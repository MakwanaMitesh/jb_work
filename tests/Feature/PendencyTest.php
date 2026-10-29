<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\City;
use App\Models\DocumentType;
use App\Models\Lead;
use App\Models\LeadDocument;
use App\Models\LoanProduct;
use App\Models\User;
use App\Services\PendencyService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendencyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private LoanProduct $businessLoan;
    private Bank $sbiBank;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('Admin');

        $this->businessLoan = LoanProduct::firstOrCreate(['code' => 'BL'], ['name' => 'Business Loan', 'status' => 'active']);
        $this->sbiBank = Bank::firstOrCreate(['name' => 'State Bank of India'], ['status' => 'active']);
    }

    private function userWith(array $permissions): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo($permissions);

        return $user;
    }

    private function bareLead(array $extra = []): Lead
    {
        return Lead::create(array_merge([
            'loan_product_id' => $this->businessLoan->id,
            'name' => 'Pendency Test Lead',
            'mobile_number' => '9811100000',
            'status' => 'new',
        ], $extra));
    }

    // ---- access control --------------------------------------------------

    public function test_dashboard_is_hidden_without_permission(): void
    {
        $user = $this->userWith(['leads.view']);

        $this->actingAs($user)->get(route('admin.pendency.index'))->assertForbidden();
    }

    public function test_show_is_hidden_without_permission(): void
    {
        $lead = $this->bareLead();
        $user = $this->userWith(['leads.view']);

        $this->actingAs($user)->get(route('admin.pendency.show', $lead))->assertForbidden();
    }

    public function test_dashboard_and_show_render_with_permission(): void
    {
        $lead = $this->bareLead();

        $this->actingAs($this->admin)->get(route('admin.pendency.index'))
            ->assertOk()
            ->assertSee('Total Active Leads')
            ->assertSee('Leads With Pending Work')
            ->assertSee('Total Pending Items')
            ->assertSee('Completed Leads')
            ->assertSee($lead->name);

        $this->actingAs($this->admin)->get(route('admin.pendency.show', $lead))
            ->assertOk()
            ->assertSee('Details Sheet')
            ->assertSee('Opinion Report')
            ->assertSee('CMA')
            ->assertSee('DPR')
            ->assertSee('Stock Statement')
            ->assertSee('Bank Details')
            ->assertSee('Business Details')
            ->assertSee('Loan Details');
    }

    public function test_sidebar_link_is_gated_by_permission(): void
    {
        $viewer = $this->userWith(['pendency.view']);
        $other = $this->userWith(['leads.view']);

        $this->actingAs($viewer)->get(route('admin.pendency.index'))->assertSee(route('admin.pendency.index'), false);
        $this->actingAs($other)->get(route('admin.leads.index'))->assertDontSee(route('admin.pendency.index'), false);
    }

    // ---- pendency detection ------------------------------------------------

    public function test_a_bare_lead_has_mostly_pending_modules(): void
    {
        $lead = $this->bareLead();
        $result = app(PendencyService::class)->forLead($lead);

        $byKey = collect($result['modules'])->keyBy('key');
        $this->assertSame('pending', $byKey['business_details']['subsections'][0]['status']);
        $this->assertSame('pending', $byKey['loan_details']['subsections'][0]['status']);
        $this->assertSame('complete', $byKey['details_sheet']['subsections'][0]['status']); // name + mobile are always present
        $this->assertGreaterThan(0, $result['pending']);
    }

    public function test_filling_in_fields_moves_subsections_from_pending_to_complete(): void
    {
        $lead = $this->bareLead([
            'business_name' => 'Acme Traders',
            'business_address' => 'Plot 1, MIDC',
            'required_loan_amount' => '500000',
            'bank_details' => [['bank_name' => 'HDFC', 'account_number' => '1234567890']],
        ]);

        $result = app(PendencyService::class)->forLead($lead);
        $byKey = collect($result['modules'])->keyBy('key');

        $this->assertSame('complete', collect($byKey['business_details']['subsections'])->firstWhere('key', 'business_profile')['status']);
        $this->assertSame('complete', collect($byKey['business_details']['subsections'])->firstWhere('key', 'business_address_location')['status']);
        $this->assertSame('pending', collect($byKey['business_details']['subsections'])->firstWhere('key', 'business_activity')['status']);
        $this->assertSame('complete', $byKey['loan_details']['subsections'][0]['status']);
        $this->assertSame('complete', $byKey['bank_details']['subsections'][0]['status']);
        $this->assertSame('complete', $byKey['details_sheet']['subsections'][2]['status']); // Bank Details, inside Details Sheet
    }

    public function test_inspection_sheet_module_is_not_applicable_unless_sbi_and_business_loan(): void
    {
        $homeLoan = LoanProduct::firstOrCreate(['code' => 'HL'], ['name' => 'Home Loan', 'status' => 'active']);
        $hdfc = Bank::firstOrCreate(['name' => 'HDFC Bank'], ['status' => 'active']);

        $notApplicable = $this->bareLead(['bank_id' => $hdfc->id, 'loan_product_id' => $homeLoan->id]);
        $applicable = $this->bareLead(['bank_id' => $this->sbiBank->id, 'loan_product_id' => $this->businessLoan->id]);

        $service = app(PendencyService::class);
        $moduleA = collect($service->forLead($notApplicable)['modules'])->firstWhere('key', 'bank_loan');
        $moduleB = collect($service->forLead($applicable)['modules'])->firstWhere('key', 'bank_loan');

        $this->assertFalse($moduleA['applicable']);
        $this->assertTrue($moduleB['applicable']);
        $this->assertNotEmpty($moduleB['subsections']);
    }

    public function test_documents_module_is_not_applicable_when_nothing_is_marked_required(): void
    {
        $lead = $this->bareLead();
        $module = collect(app(PendencyService::class)->forLead($lead)['modules'])->firstWhere('key', 'documents');

        $this->assertFalse($module['applicable']);
    }

    public function test_documents_module_reflects_the_document_masters_required_flag_and_uploads(): void
    {
        $type = DocumentType::create(['name' => 'PAN Card Copy', 'code' => 'PENDENCY_PAN', 'status' => 'active', 'is_required' => true, 'sort_order' => 1]);
        DocumentType::create(['name' => 'Optional Doc', 'code' => 'PENDENCY_OPT', 'status' => 'active', 'is_required' => false, 'sort_order' => 2]);
        $lead = $this->bareLead();

        $module = collect(app(PendencyService::class)->forLead($lead)['modules'])->firstWhere('key', 'documents');
        $this->assertTrue($module['applicable']);
        $this->assertCount(1, $module['subsections']); // only the required type is tracked, not the optional one
        $this->assertSame('pending', $module['subsections'][0]['status']);

        LeadDocument::create([
            'lead_id' => $lead->id, 'document_type_id' => $type->id, 'file_path' => 'x.pdf',
            'original_name' => 'x.pdf', 'mime_type' => 'application/pdf', 'file_size' => 10,
        ]);
        $lead->unsetRelation('leadDocuments');

        $module = collect(app(PendencyService::class)->forLead($lead)['modules'])->firstWhere('key', 'documents');
        $this->assertSame('complete', $module['subsections'][0]['status']);
    }

    public function test_generated_report_subsections_use_the_existing_generation_checks(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $lead = $this->bareLead();

        $module = collect(app(PendencyService::class)->forLead($lead)['modules'])->firstWhere('key', 'dpr');
        $this->assertSame('pending', collect($module['subsections'])->firstWhere('key', 'dpr_generated')['status']);

        \Illuminate\Support\Facades\Storage::disk('public')->put('dpr/' . $lead->id . '/DPR.docx', 'x');
        $module = collect(app(PendencyService::class)->forLead($lead)['modules'])->firstWhere('key', 'dpr');
        $this->assertSame('complete', collect($module['subsections'])->firstWhere('key', 'dpr_generated')['status']);
    }

    public function test_the_sbi_stock_statement_generated_flag_now_matches_the_generators_real_path(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $lead = $this->bareLead();

        $this->assertFalse($lead->hasGeneratedSbiStockStatement());
        \Illuminate\Support\Facades\Storage::disk('public')->put('sbi-stock-statement/' . $lead->id . '/SBI_Stock_Statement.xlsx', 'x');
        $this->assertTrue($lead->fresh()->hasGeneratedSbiStockStatement());
    }

    // ---- dashboard filters -------------------------------------------------

    public function test_dashboard_filters_by_city_employee_module_and_status(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $city = City::create(['name' => 'Surat', 'status' => 'active']);
        $employee = User::factory()->create(['status' => 'active']);

        $complete = $this->bareLead([
            'city_id' => $city->id, 'assigned_employee_id' => $employee->id, 'constitution_of_business' => 'Proprietorship',
            'business_name' => 'Acme', 'business_address' => 'Plot 1', 'business_activity' => 'Trading',
            'business_experience' => '5 yrs', 'gst_number' => '27AAAAA0000A1Z5', 'area_of_premises' => '1000 sqft',
            'required_loan_amount' => '100000', 'cc_amount' => '10000', 'cc_details' => 'x',
            'term_loan_amount' => '10000', 'term_loan_machinery_details' => 'x',
            'current_loans' => [['bank_name' => 'HDFC', 'loan_amount' => '1000']],
            'bank_details' => [['bank_name' => 'HDFC', 'account_number' => '1234567890']],
            'itr_details' => [['assessment_year' => '2024-25', 'itr_audited' => 'No']],
        ]);

        \App\Models\CmaProfile::create([
            'lead_id' => $complete->id, 'saved_at' => now(), 'firm_name' => 'Acme', 'proprietor_name' => 'A Owner',
            'pan' => 'AAAAA1111A', 'address' => 'Plot 1', 'business_experience' => '5 yrs', 'area_of_operation' => 'Surat',
            'manpower' => '5', 'space' => '1000', 'connectivity' => 'Highway',
        ]);
        \App\Models\DprReport::create([
            'lead_id' => $complete->id, 'saved_at' => now(), 'firm_name' => 'Acme', 'applicant_name' => 'A Owner', 'pan' => 'AAAAA1111A',
            'activity' => 'Trading', 'business_profile' => 'x', 'udyam' => 'UDYAM-1', 'current_loans' => [['bank_name' => 'HDFC']],
            'term_required' => '100000', 'cc_required' => '10000', 'cost_land' => '1000', 'cost_building' => '1000',
            'cost_machinery' => '1000', 'cost_working_capital' => '1000', 'fin_promoter_capital' => '1000', 'fin_term_loan' => '1000',
            'fin_cash_credit' => '1000', 'machinery' => [['particulars' => 'Machine']], 'collateral' => [['type' => 'Residential']],
            'financials' => ['sales' => ['p1' => '10']],
        ]);
        \App\Models\OpinionReport::create([
            'lead_id' => $complete->id, 'saved_at' => now(), 'deponent_name' => 'A Owner', 'relationship' => 'Son', 'father_name' => 'F Owner',
            'date_of_birth' => '1990-01-01', 'place_of_birth' => 'Surat', 'occupation' => 'Business', 'branch' => 'Surat Branch',
            'borrower_name' => 'Acme', 'assets_date' => '2026-01-01', 'properties' => [['place' => 'Surat']], 'outside_india' => 'None',
            'liquid_assets' => ['cash' => ['amount' => '1000']], 'gov_securities' => 'None', 'shares' => 'None',
            'life_insurance' => 'LIC', 'jewellery' => 'None', 'associate_investment' => 'None', 'intangible_assets' => 'None',
            'income_per_month' => '50000', 'borrowings_banks' => 'None', 'other_asset' => 'None',
            'family_members' => [['name' => 'Spouse']], 'deponent_date' => '2026-01-01',
        ]);
        \App\Models\StockStatement::create([
            'lead_id' => $complete->id, 'saved_at' => now(), 'items' => [['name' => 'Stock', 'opening_qty' => '10']],
            'debtor_name' => 'Debtor', 'debtor_amount' => '1000', 'sundry_creditors_year_end' => '500',
        ]);

        \Illuminate\Support\Facades\Storage::disk('public')->put('dpr/' . $complete->id . '/DPR.docx', 'x');
        \Illuminate\Support\Facades\Storage::disk('public')->put('cma/' . $complete->id . '/CMA.xlsx', 'x');

        $pending = $this->bareLead(['name' => 'Still Pending Lead']);

        $byCity = $this->actingAs($this->admin)->get(route('admin.pendency.index', ['city_id' => $city->id]))->assertOk();
        $byCity->assertSee($complete->name)->assertDontSee($pending->name);

        $byEmployee = $this->actingAs($this->admin)->get(route('admin.pendency.index', ['assigned_employee_id' => $employee->id]))->assertOk();
        $byEmployee->assertSee($complete->name)->assertDontSee($pending->name);

        $completedOnly = $this->actingAs($this->admin)->get(route('admin.pendency.index', ['status' => 'completed']))->assertOk();
        $completedOnly->assertSee($complete->name)->assertDontSee($pending->name);

        $pendingOnly = $this->actingAs($this->admin)->get(route('admin.pendency.index', ['status' => 'pending']))->assertOk();
        $pendingOnly->assertSee($pending->name)->assertDontSee($complete->name);

        $byModule = $this->actingAs($this->admin)->get(route('admin.pendency.index', ['module' => 'Business Details']))->assertOk();
        $byModule->assertSee($pending->name)->assertDontSee($complete->name);
    }

    public function test_clicking_a_pending_item_links_to_the_leads_edit_screen_and_its_tab(): void
    {
        $lead = $this->bareLead();

        $this->actingAs($this->admin)->get(route('admin.pendency.show', $lead))
            ->assertOk()
            ->assertSee(route('admin.leads.edit', $lead) . '#business-tab', false);
    }
}
