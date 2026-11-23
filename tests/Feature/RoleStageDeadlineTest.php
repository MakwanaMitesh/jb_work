<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LoanProduct;
use App\Models\Role;
use App\Models\User;
use App\Support\LeadStage;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RoleStageDeadlineTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private LoanProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('Admin');

        $this->product = LoanProduct::firstOrCreate(['code' => 'BL'], ['name' => 'Business Loan', 'status' => 'active']);
    }

    private function roleWithSla(string $name, ?int $slaDays): Role
    {
        return Role::create(['name' => $name, 'guard_name' => 'web', 'is_active' => true, 'sla_days' => $slaDays]);
    }

    private function userWithRole(string $roleName, array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'], ['is_active' => true]);
        $role->givePermissionTo($permissions);

        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole($roleName);

        return $user;
    }

    private function lead(array $extra = []): Lead
    {
        return Lead::create(array_merge([
            'loan_product_id' => $this->product->id, 'name' => 'Deadline Test Lead', 'mobile_number' => '9800000000', 'status' => 'new',
        ], $extra));
    }

    public function test_creating_a_role_can_set_its_sla_days_via_the_admin_form(): void
    {
        $this->actingAs($this->admin)->post(route('admin.roles.store'), [
            'name' => 'Detail Entry', 'description' => '', 'sla_days' => 2, 'permissions' => [],
        ])->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseHas('roles', ['name' => 'Detail Entry', 'sla_days' => 2]);
    }

    public function test_a_lead_created_while_the_first_stages_role_has_an_sla_gets_a_deadline(): void
    {
        $this->roleWithSla(LeadStage::first()['role'], 2);

        $lead = $this->lead();

        $this->assertNotNull($lead->deadline);
        $this->assertTrue($lead->deadline->between(now()->addDays(2)->subMinute(), now()->addDays(2)->addMinute()));
    }

    public function test_a_lead_created_while_the_first_stages_role_has_no_sla_gets_no_deadline(): void
    {
        $this->roleWithSla(LeadStage::first()['role'], null);

        $lead = $this->lead();

        $this->assertNull($lead->deadline);
    }

    public function test_submit_stage_recomputes_the_deadline_for_the_next_stages_role(): void
    {
        $firstStage = LeadStage::first();
        $nextStage = LeadStage::next($firstStage['key']);

        $this->roleWithSla($firstStage['role'], 2);
        $this->roleWithSla($nextStage['role'], 5);

        $user = $this->userWithRole($firstStage['role'], ['leads.view', 'leads.view_own']);
        $lead = $this->lead();

        // Satisfy PendencyService's stage-1 completion check (Details Sheet subsections).
        $lead->update([
            'bank_details' => [['bank_name' => 'SBI', 'account_number' => '12345', 'account_type' => 'Savings', 'ifsc_code' => 'SBIN0000001']],
            'itr_details' => [['assessment_year' => '2024-25', 'itr_audited' => 'Yes']],
        ]);

        $this->actingAs($user)->post(route('admin.leads.stage.submit', $lead))->assertSessionHas('success');

        $fresh = $lead->fresh();
        $this->assertSame($nextStage['key'], $fresh->current_stage);
        $this->assertNotNull($fresh->deadline);
        $this->assertTrue($fresh->deadline->between(now()->addDays(5)->subMinute(), now()->addDays(5)->addMinute()));
    }

    public function test_the_lead_show_page_shows_overdue_when_past_the_deadline(): void
    {
        $this->roleWithSla(LeadStage::first()['role'], 2);
        $lead = $this->lead();
        $lead->update(['deadline' => Carbon::now()->subDay()]);

        $html = $this->actingAs($this->admin)->get(route('admin.leads.show', $lead))->assertOk()->getContent();

        $this->assertStringContainsString('Overdue', $html);
    }

    public function test_the_lead_show_page_shows_a_due_date_before_the_deadline(): void
    {
        $this->roleWithSla(LeadStage::first()['role'], 2);
        $lead = $this->lead();

        $html = $this->actingAs($this->admin)->get(route('admin.leads.show', $lead))->assertOk()->getContent();

        $this->assertStringContainsString('Due ' . $lead->fresh()->deadline->format('d M Y'), $html);
    }
}
