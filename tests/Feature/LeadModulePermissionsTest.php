<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LoanProduct;
use App\Models\Role;
use App\Models\User;
use App\Services\PendencyService;
use App\Support\LeadModuleAccess;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadModulePermissionsTest extends TestCase
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

    private function userWith(array $permissions): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo($permissions);

        return $user;
    }

    private function roleWith(string $name, array $permissions): Role
    {
        $role = Role::create(['name' => $name, 'guard_name' => 'web', 'is_active' => true]);
        $role->givePermissionTo($permissions);

        return $role;
    }

    private function lead(array $extra = []): Lead
    {
        return Lead::create(array_merge([
            'loan_product_id' => $this->product->id, 'name' => 'RBAC Test Lead', 'mobile_number' => '9800000000', 'status' => 'new',
        ], $extra));
    }

    // ---- the seeded permissions themselves ---------------------------------

    public function test_the_new_lead_tab_modules_and_their_subsections_are_seeded(): void
    {
        foreach (['details_sheet.view', 'details_sheet.update', 'bank_loan.view', 'bank_loan.update',
            'bank_details.view', 'bank_details.update', 'business_details.view', 'business_details.update',
            'loan_details.view', 'loan_details.update'] as $name) {
            $this->assertDatabaseHas('permissions', ['name' => $name]);
        }

        foreach (PermissionSeeder::LEAD_MODULE_SUBSECTIONS as $module => $subsections) {
            foreach (array_keys($subsections) as $key) {
                $this->assertDatabaseHas('permissions', ['name' => "{$module}.{$key}.view"]);
            }
        }
    }

    public function test_admin_role_has_every_new_permission(): void
    {
        $admin = Role::where('name', 'Admin')->first();
        $this->assertTrue($admin->hasPermissionTo('business_details.update'));
        $this->assertTrue($admin->hasPermissionTo('dpr.financials.view'));
        $this->assertTrue($admin->hasPermissionTo('or.deponent_details.view'));
    }

    // ---- tab visibility: "Loan Data Entry" example from the spec ----------

    public function test_a_role_granted_only_three_modules_sees_only_those_three_tabs(): void
    {
        $this->roleWith('Loan Data Entry', [
            'leads.view', 'leads.create', 'leads.edit',
            'details_sheet.view', 'details_sheet.update',
            'bank_loan.view', 'bank_loan.update',
            'application_form.view', 'application_form.create', 'application_form.update',
        ]);
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('Loan Data Entry');

        $html = $this->actingAs($user)->get(route('admin.leads.create'))->assertOk()->getContent();

        $this->assertStringContainsString('id="kyc-tab"', $html);
        $this->assertStringContainsString('id="bank-loan-tab"', $html);
        $this->assertStringContainsString('id="application-form-tab"', $html);

        foreach (['documents-tab', 'stock-statement-tab', 'opinion-report-tab', 'cma-tab', 'dpr-tab', 'bank-tab', 'business-tab', 'loan-tab'] as $hiddenTab) {
            $this->assertStringNotContainsString('id="' . $hiddenTab . '"', $html, "{$hiddenTab} should not be in the page at all");
        }
    }

    public function test_a_role_with_no_module_permissions_sees_no_lead_tabs_at_all(): void
    {
        $user = $this->userWith(['leads.view', 'leads.edit']);
        $lead = $this->lead();

        $html = $this->actingAs($user)->get(route('admin.leads.edit', $lead))->assertOk()->getContent();

        foreach (['kyc-tab', 'bank-loan-tab', 'application-form-tab', 'documents-tab', 'stock-statement-tab',
            'opinion-report-tab', 'cma-tab', 'dpr-tab', 'bank-tab', 'business-tab', 'loan-tab'] as $tab) {
            $this->assertStringNotContainsString('id="' . $tab . '"', $html);
        }
    }

    public function test_admin_still_sees_every_tab(): void
    {
        $lead = $this->lead();
        $html = $this->actingAs($this->admin)->get(route('admin.leads.edit', $lead))->assertOk()->getContent();

        foreach (['kyc-tab', 'bank-loan-tab', 'application-form-tab', 'documents-tab', 'stock-statement-tab',
            'opinion-report-tab', 'cma-tab', 'dpr-tab', 'bank-tab', 'business-tab', 'loan-tab'] as $tab) {
            $this->assertStringContainsString('id="' . $tab . '"', $html);
        }
    }

    // ---- backend enforcement: hidden tabs can't be written to by a crafted request ------------

    public function test_a_user_without_business_details_update_cannot_set_business_fields_even_by_crafting_the_request(): void
    {
        $user = $this->userWith(['leads.view', 'leads.create']);

        $this->actingAs($user)->post(route('admin.leads.store'), [
            'name' => 'Crafted', 'mobile_number' => '9811111111', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Should Not Be Saved',
        ])->assertRedirect(route('admin.leads.index'));

        $lead = Lead::where('name', 'Crafted')->first();
        $this->assertNotNull($lead);
        $this->assertNull($lead->business_name);
    }

    public function test_a_user_with_business_details_update_can_set_business_fields(): void
    {
        $user = $this->userWith(['leads.view', 'leads.create', 'business_details.view', 'business_details.update']);

        $this->actingAs($user)->post(route('admin.leads.store'), [
            'name' => 'Allowed', 'mobile_number' => '9822222222', 'loan_product_id' => $this->product->id, 'status' => 'new',
            'business_name' => 'Should Be Saved',
        ])->assertRedirect(route('admin.leads.index'));

        $this->assertSame('Should Be Saved', Lead::where('name', 'Allowed')->first()->business_name);
    }

    public function test_updating_a_lead_without_loan_details_update_leaves_its_existing_loan_fields_untouched(): void
    {
        $lead = $this->lead(['required_loan_amount' => '500000']);
        $user = $this->userWith(['leads.view', 'leads.edit']);

        $this->actingAs($user)->put(route('admin.leads.update', $lead), [
            'name' => $lead->name, 'mobile_number' => $lead->mobile_number, 'loan_product_id' => $this->product->id, 'status' => 'new',
            'required_loan_amount' => '999999999',
        ])->assertRedirect();

        $this->assertSame('500000', $lead->fresh()->required_loan_amount);
    }

    public function test_the_details_sheet_itr_and_bank_loan_fields_are_likewise_protected(): void
    {
        $lead = $this->lead(['pan_card' => 'ORIGINAL1A']);
        $user = $this->userWith(['leads.view', 'leads.edit']);

        $this->actingAs($user)->put(route('admin.leads.update', $lead), [
            'name' => $lead->name, 'mobile_number' => $lead->mobile_number, 'loan_product_id' => $this->product->id, 'status' => 'new',
            'pan_card' => 'HACKED0001A',
            'bank_loan_details' => ['branch_name' => 'Should Not Save'],
            'bank_details' => [['bank_name' => 'Should Not Save', 'account_number' => '000']],
        ])->assertRedirect();

        $fresh = $lead->fresh();
        $this->assertSame('ORIGINAL1A', $fresh->pan_card);
        $this->assertEmpty($fresh->bank_loan_details ?? []);
        $this->assertEmpty($fresh->bank_details ?? []);
    }

    public function test_the_six_independently_gated_modules_already_enforce_their_own_permission(): void
    {
        // dpr/cma/or/sbi_stock_statement/application_form/documents were already protected before
        // this change (each via its own Saver::permissionFor() or an authorize() call) — confirms
        // that remains true rather than silently regressing.
        $lead = $this->lead();
        $user = $this->userWith(['leads.view', 'leads.edit']);

        $this->actingAs($user)->put(route('admin.leads.update', $lead), [
            'name' => $lead->name, 'mobile_number' => $lead->mobile_number, 'loan_product_id' => $this->product->id, 'status' => 'new',
            'dpr' => ['firm_name' => 'Should Not Save'],
        ])->assertRedirect();

        $this->assertDatabaseMissing('dpr_reports', ['firm_name' => 'Should Not Save']);
    }

    // ---- LeadModuleAccess helper --------------------------------------------

    public function test_lead_module_access_filters_exactly_the_five_new_modules_fields(): void
    {
        $user = $this->userWith(['leads.view']); // none of the five module permissions

        $filtered = LeadModuleAccess::filterFieldsForUser($user, [
            'name' => 'Kept', 'status' => 'new', 'business_name' => 'Dropped', 'required_loan_amount' => 'Dropped',
            'bank_details' => 'Dropped', 'bank_loan_details' => 'Dropped', 'pan_card' => 'Dropped',
        ]);

        $this->assertSame('Kept', $filtered['name']);
        $this->assertSame('new', $filtered['status']);
        foreach (['business_name', 'required_loan_amount', 'bank_details', 'bank_loan_details', 'pan_card'] as $dropped) {
            $this->assertArrayNotHasKey($dropped, $filtered);
        }
    }

    // ---- Pendency respects the same permissions ------------------------------

    public function test_pendency_hides_modules_the_viewing_user_cannot_see(): void
    {
        $lead = $this->lead();
        $user = $this->userWith(['pendency.view', 'leads.view', 'dpr.view', 'dpr.basic.view']);

        $result = app(PendencyService::class)->forLead($lead, $user);
        $keys = array_column($result['modules'], 'key');

        $this->assertContains('dpr', $keys);
        $this->assertNotContains('cma', $keys);
        $this->assertNotContains('business_details', $keys);

        $dpr = collect($result['modules'])->firstWhere('key', 'dpr');
        $this->assertCount(1, $dpr['subsections']);
        $this->assertSame('basic', $dpr['subsections'][0]['key']);
    }

    public function test_pendency_show_page_only_offers_complete_now_links_for_visible_modules(): void
    {
        $lead = $this->lead();
        $user = $this->userWith(['pendency.view', 'dpr.view', 'dpr.basic.view']);

        $html = $this->actingAs($user)->get(route('admin.pendency.show', $lead))->assertOk()->getContent();

        $this->assertStringContainsString('DPR', $html);
        $this->assertStringNotContainsString('>CMA<', $html);
        $this->assertStringNotContainsString('Business Details', $html);
    }

    public function test_pendency_without_a_user_filter_sees_every_module_as_before(): void
    {
        $lead = $this->lead();
        $result = app(PendencyService::class)->forLead($lead);

        $this->assertGreaterThanOrEqual(10, count($result['modules']));
    }
}
