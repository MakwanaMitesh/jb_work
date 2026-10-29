<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LoanProduct;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadViewScopePermissionsTest extends TestCase
{
    use RefreshDatabase;

    private LoanProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->product = LoanProduct::firstOrCreate(['code' => 'BL'], ['name' => 'Business Loan', 'status' => 'active']);
    }

    private function userWith(array $permissions): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo($permissions);

        return $user;
    }

    private function lead(array $extra = []): Lead
    {
        return Lead::create(array_merge([
            'loan_product_id' => $this->product->id, 'name' => 'Scope Test Lead', 'mobile_number' => '9800000000', 'status' => 'new',
        ], $extra));
    }

    public function test_leads_view_own_scopes_the_index_to_leads_assigned_to_that_employee(): void
    {
        $employee = $this->userWith(['leads.view', 'leads.view_own']);
        $other = User::factory()->create(['status' => 'active']);

        $own = $this->lead(['name' => 'Mine', 'assigned_employee_id' => $employee->id]);
        $notMine = $this->lead(['name' => 'Not Mine', 'assigned_employee_id' => $other->id]);

        $html = $this->actingAs($employee)->get(route('admin.leads.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Mine', $html);
        $this->assertStringNotContainsString('Not Mine', $html);
    }

    public function test_leads_view_all_sees_every_lead_regardless_of_assignment(): void
    {
        $employee = $this->userWith(['leads.view', 'leads.view_all']);
        $other = User::factory()->create(['status' => 'active']);

        $this->lead(['name' => 'Mine', 'assigned_employee_id' => $employee->id]);
        $this->lead(['name' => 'Not Mine', 'assigned_employee_id' => $other->id]);

        $html = $this->actingAs($employee)->get(route('admin.leads.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Mine', $html);
        $this->assertStringContainsString('Not Mine', $html);
    }

    public function test_leads_view_own_blocks_opening_a_lead_assigned_to_someone_else(): void
    {
        $employee = $this->userWith(['leads.view', 'leads.view_own']);
        $other = User::factory()->create(['status' => 'active']);

        $notMine = $this->lead(['assigned_employee_id' => $other->id]);

        $this->actingAs($employee)->get(route('admin.leads.show', $notMine))->assertForbidden();
    }

    public function test_leads_view_own_allows_opening_a_lead_assigned_to_that_employee(): void
    {
        $employee = $this->userWith(['leads.view', 'leads.view_own']);
        $mine = $this->lead(['assigned_employee_id' => $employee->id]);

        $this->actingAs($employee)->get(route('admin.leads.show', $mine))->assertOk();
    }

    public function test_leads_view_without_either_scope_permission_keeps_seeing_the_full_list(): void
    {
        $employee = $this->userWith(['leads.view']);
        $other = User::factory()->create(['status' => 'active']);

        $this->lead(['name' => 'Mine', 'assigned_employee_id' => $employee->id]);
        $this->lead(['name' => 'Not Mine', 'assigned_employee_id' => $other->id]);

        $html = $this->actingAs($employee)->get(route('admin.leads.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Mine', $html);
        $this->assertStringContainsString('Not Mine', $html);
    }

    // ---- Pendency respects the same leads.view_own / leads.view_all scope ----

    public function test_pendency_index_with_leads_view_own_only_lists_the_employees_own_leads(): void
    {
        $employee = $this->userWith(['pendency.view', 'leads.view_own']);
        $other = User::factory()->create(['status' => 'active']);

        $this->lead(['name' => 'Mine', 'assigned_employee_id' => $employee->id]);
        $this->lead(['name' => 'Not Mine', 'assigned_employee_id' => $other->id]);

        $html = $this->actingAs($employee)->get(route('admin.pendency.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Mine', $html);
        $this->assertStringNotContainsString('Not Mine', $html);
    }

    public function test_pendency_index_with_leads_view_all_lists_every_lead(): void
    {
        $employee = $this->userWith(['pendency.view', 'leads.view_all']);
        $other = User::factory()->create(['status' => 'active']);

        $this->lead(['name' => 'Mine', 'assigned_employee_id' => $employee->id]);
        $this->lead(['name' => 'Not Mine', 'assigned_employee_id' => $other->id]);

        $html = $this->actingAs($employee)->get(route('admin.pendency.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Mine', $html);
        $this->assertStringContainsString('Not Mine', $html);
    }

    public function test_pendency_show_with_leads_view_own_blocks_a_lead_assigned_to_someone_else(): void
    {
        $employee = $this->userWith(['pendency.view', 'leads.view_own']);
        $other = User::factory()->create(['status' => 'active']);
        $notMine = $this->lead(['assigned_employee_id' => $other->id]);

        $this->actingAs($employee)->get(route('admin.pendency.show', $notMine))->assertForbidden();
    }

    public function test_pendency_show_with_leads_view_own_allows_the_employees_own_lead(): void
    {
        $employee = $this->userWith(['pendency.view', 'leads.view_own']);
        $mine = $this->lead(['assigned_employee_id' => $employee->id]);

        $this->actingAs($employee)->get(route('admin.pendency.show', $mine))->assertOk();
    }
}
