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
use Tests\TestCase;

class LeadStageWorkflowTest extends TestCase
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
            'loan_product_id' => $this->product->id, 'name' => 'Stage Test Lead', 'mobile_number' => '9800000000', 'status' => 'new',
        ], $extra));
    }

    public function test_a_freshly_created_lead_starts_at_the_first_stage_unassigned(): void
    {
        $lead = $this->lead();

        $this->assertSame(LeadStage::first()['key'], $lead->current_stage);
        $this->assertNull($lead->assigned_employee_id);
    }

    public function test_an_unassigned_lead_is_visible_to_the_current_stage_roles_pool_but_not_other_roles(): void
    {
        $detailEntryUser = $this->userWithRole('Detail Entry', ['leads.view', 'leads.view_own', 'details_sheet.view', 'bank_loan.view']);
        $bankProcessingUser = $this->userWithRole('Bank Processing', ['leads.view', 'leads.view_own']);

        $lead = $this->lead(['name' => 'Pool Lead']);

        $this->actingAs($detailEntryUser)->get(route('admin.leads.show', $lead))->assertOk();
        $this->actingAs($bankProcessingUser)->get(route('admin.leads.show', $lead))->assertForbidden();

        $html = $this->actingAs($detailEntryUser)->get(route('admin.leads.index'))->assertOk()->getContent();
        $this->assertStringContainsString('Pool Lead', $html);

        $html2 = $this->actingAs($bankProcessingUser)->get(route('admin.leads.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString('Pool Lead', $html2);
    }

    public function test_once_admin_assigns_it_to_one_employee_their_role_mates_lose_access(): void
    {
        $detailUser1 = $this->userWithRole('Detail Entry', ['leads.view', 'leads.view_own']);
        $detailUser2 = User::factory()->create(['status' => 'active']);
        $detailUser2->assignRole('Detail Entry');

        $lead = $this->lead();

        $this->admin->givePermissionTo(['leads.assign']);
        $this->actingAs($this->admin)->post(route('admin.leads.assign', $lead), [
            'employee_id' => $detailUser1->id,
            'notes' => 'Assigning to Detail Entry',
        ])->assertRedirect();

        $this->actingAs($detailUser1)->get(route('admin.leads.show', $lead))->assertOk();
        $this->actingAs($detailUser2)->get(route('admin.leads.show', $lead))->assertForbidden();
    }

    public function test_a_crafted_request_cannot_write_fields_outside_the_current_stage_even_with_permission(): void
    {
        $user = $this->userWithRole('Detail Entry', [
            'leads.view', 'leads.view_own', 'leads.edit',
            'details_sheet.view', 'details_sheet.update',
            // Has the permission for a module that belongs to a LATER stage — stage gating must
            // still block it, independent of module permission.
            'business_details.view', 'business_details.update',
        ]);

        $lead = $this->lead();

        $this->actingAs($user)->put(route('admin.leads.update', $lead), [
            'name' => $lead->name, 'mobile_number' => $lead->mobile_number, 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Should Not Save',
        ])->assertRedirect();

        $this->assertNull($lead->fresh()->business_name);
    }

    public function test_submit_stage_is_blocked_while_the_current_stages_fields_are_incomplete(): void
    {
        $user = $this->userWithRole('Detail Entry', ['leads.view', 'leads.view_own']);
        $lead = $this->lead();

        $this->actingAs($user)->post(route('admin.leads.stage.submit', $lead))
            ->assertRedirect(route('admin.leads.show', $lead))
            ->assertSessionHas('error');

        $this->assertSame(LeadStage::first()['key'], $lead->fresh()->current_stage);
    }

    public function test_submit_stage_succeeds_once_complete_and_forwards_to_the_next_stage_role_pool(): void
    {
        $user = $this->userWithRole('Detail Entry', ['leads.view', 'leads.view_own']);
        $lead = $this->lead();

        // Fill in the Details Sheet subsections PendencyService checks for stage 1.
        $lead->update([
            'bank_details' => [['bank_name' => 'SBI', 'account_number' => '12345', 'account_type' => 'Savings', 'ifsc_code' => 'SBIN0000001']],
            'itr_details' => [['assessment_year' => '2024-25', 'itr_audited' => 'Yes']],
        ]);

        $this->actingAs($user)->post(route('admin.leads.stage.submit', $lead))
            ->assertRedirect(route('admin.leads.show', $lead))
            ->assertSessionHas('success');

        $fresh = $lead->fresh();
        $this->assertSame(LeadStage::next(LeadStage::first()['key'])['key'], $fresh->current_stage);
        $this->assertNull($fresh->assigned_employee_id);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'stage_forwarded']);

        $bankProcessingUser = $this->userWithRole('Bank Processing', ['leads.view', 'leads.view_own']);
        $this->actingAs($bankProcessingUser)->get(route('admin.leads.show', $fresh))->assertOk();
        $this->actingAs($user)->get(route('admin.leads.show', $fresh))->assertForbidden();
    }

    public function test_admin_is_unaffected_by_stage_restrictions(): void
    {
        $lead = $this->lead();

        $this->actingAs($this->admin)->get(route('admin.leads.show', $lead))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.leads.edit', $lead))->assertOk();

        $this->actingAs($this->admin)->put(route('admin.leads.update', $lead), [
            'name' => $lead->name, 'mobile_number' => $lead->mobile_number, 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Admin Can Save',
        ])->assertRedirect();

        $this->assertSame('Admin Can Save', $lead->fresh()->business_name);
    }

    public function test_a_user_with_neither_view_own_nor_view_all_keeps_the_old_unrestricted_behaviour(): void
    {
        // Pre-existing behaviour for roles that never opted into the stage workflow.
        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo(['leads.view', 'leads.edit', 'business_details.view', 'business_details.update']);

        $lead = $this->lead();

        $this->actingAs($user)->get(route('admin.leads.show', $lead))->assertOk();

        $this->actingAs($user)->put(route('admin.leads.update', $lead), [
            'name' => $lead->name, 'mobile_number' => $lead->mobile_number, 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Unrestricted Still Works',
        ])->assertRedirect();

        $this->assertSame('Unrestricted Still Works', $lead->fresh()->business_name);
    }
}
