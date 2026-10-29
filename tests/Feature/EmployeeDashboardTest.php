<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Bank;
use App\Models\City;
use App\Models\CustomerConstitution;
use App\Models\DocumentType;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LoanProduct;
use App\Models\User;
use App\Services\PendencyService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EmployeeDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employeeA;
    private User $employeeB;
    private City $mumbai;
    private City $pune;
    private Bank $sbiBank;
    private LoanProduct $businessLoan;
    private LoanProduct $homeLoan;
    private CustomerConstitution $constitution;
    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'admin@test.com',
            'status' => 'active',
        ]);
        $this->admin->assignRole('Admin');

        $this->employeeA = User::factory()->create([
            'name' => 'Alice Employee',
            'email' => 'alice@test.com',
            'status' => 'active',
        ]);
        $this->employeeA->assignRole('Employee');
        $this->employeeA->givePermissionTo([
            'dashboard.view', 'leads.view', 'leads.edit', 'pendency.view',
            'details_sheet.view', 'details_sheet.basic_details.view', 'details_sheet.itr_details.view', 'details_sheet.bank_details.view',
            'documents.view',
            'dpr.view', 'dpr.basic.view',
            'cma.view', 'cma.business_applicant_profile.view',
            'bank_details.view', 'bank_details.bank_account_details.view',
            'business_details.view', 'business_details.business_profile.view',
            'loan_details.view', 'loan_details.requested_loan_amount.view',
        ]);

        $this->employeeB = User::factory()->create([
            'name' => 'Bob Employee',
            'email' => 'bob@test.com',
            'status' => 'active',
        ]);
        $this->employeeB->assignRole('Employee');
        $this->employeeB->givePermissionTo(['dashboard.view', 'leads.view', 'pendency.view']);

        $this->mumbai = City::create(['name' => 'Mumbai', 'status' => 'active']);
        $this->pune = City::create(['name' => 'Pune', 'status' => 'active']);

        $this->sbiBank = Bank::firstOrCreate(['name' => 'State Bank of India'], ['status' => 'active']);
        $this->businessLoan = LoanProduct::firstOrCreate(['code' => 'BL'], ['name' => 'Business Loan', 'status' => 'active']);
        $this->homeLoan = LoanProduct::firstOrCreate(['code' => 'HL'], ['name' => 'Home Loan', 'status' => 'active']);
        $this->constitution = CustomerConstitution::firstOrCreate(['code' => 'PROP'], ['name' => 'Proprietorship', 'status' => 'active']);

        $this->agent = Agent::create([
            'first_name' => 'Test',
            'last_name' => 'Agent',
            'email' => 'agent@test.com',
            'mobile_number' => '+919999900000',
            'city_id' => $this->mumbai->id,
            'status' => 'active',
        ]);
    }

    private function createLeadFor(User $employee, array $attributes = []): Lead
    {
        return Lead::create(array_merge([
            'name' => 'Test Lead ' . uniqid(),
            'mobile_number' => '+919876543210',
            'email' => 'lead' . uniqid() . '@example.com',
            'city_id' => $this->mumbai->id,
            'bank_id' => $this->sbiBank->id,
            'loan_product_id' => $this->businessLoan->id,
            'constitution_id' => $this->constitution->id,
            'agent_id' => $this->agent->id,
            'status' => Lead::STATUS_NEW,
            'assigned_employee_id' => $employee->id,
            'assigned_by' => $this->admin->id,
            'assigned_at' => now(),
            'required_loan_amount' => 500000,
        ], $attributes));
    }

    public function test_employee_sees_correct_dashboard_summary_cards(): void
    {
        // Alice has:
        // 1 New lead (active)
        // 1 In Progress lead (active)
        // 1 Completed lead
        // 1 Lost lead (closed)
        $this->createLeadFor($this->employeeA, ['name' => 'Alice Lead 1', 'status' => Lead::STATUS_NEW]);
        $this->createLeadFor($this->employeeA, ['name' => 'Alice Lead 2', 'status' => Lead::STATUS_IN_PROGRESS]);
        $this->createLeadFor($this->employeeA, ['name' => 'Alice Lead 3', 'status' => Lead::STATUS_COMPLETED]);
        $this->createLeadFor($this->employeeA, ['name' => 'Alice Lead 4', 'status' => Lead::STATUS_LOST]);

        // Bob has 2 leads (must not count for Alice)
        $this->createLeadFor($this->employeeB, ['name' => 'Bob Lead 1', 'status' => Lead::STATUS_NEW]);
        $this->createLeadFor($this->employeeB, ['name' => 'Bob Lead 2', 'status' => Lead::STATUS_COMPLETED]);

        $response = $this->actingAs($this->employeeA)->get(route('dashboard'));
        $response->assertOk();

        $response->assertViewHas('leadStats', function ($stats) {
            return $stats['total_assigned_leads'] === 4
                && $stats['active_leads'] === 2
                && $stats['completed_leads'] === 1
                && $stats['closed_leads'] === 1
                && $stats['pending_work_count'] > 0;
        });

        // Summary cards should be rendered and clickable
        $response->assertSee('Total Assigned Leads');
        $response->assertSee('Active Leads');
        $response->assertSee('Pending Work');
        $response->assertSee('Overdue Leads');
        $response->assertSee('Due Today');
        $response->assertSee('Completed Leads');
    }

    public function test_my_assigned_leads_table_renders_all_required_columns_and_links(): void
    {
        $lead = $this->createLeadFor($this->employeeA, [
            'name' => 'Rajesh Sharma Enterprise',
            'city_id' => $this->mumbai->id,
            'loan_product_id' => $this->businessLoan->id,
            'status' => Lead::STATUS_NEW,
        ]);

        $response = $this->actingAs($this->employeeA)->get(route('dashboard'));
        $response->assertOk();

        // Lead Name
        $response->assertSee('Rajesh Sharma Enterprise');
        // Lead ID
        $response->assertSee('#LD-' . str_pad($lead->id, 5, '0', STR_PAD_LEFT));
        // City
        $response->assertSee('Mumbai');
        // Loan Product
        $response->assertSee('Business Loan');
        // Action: View Lead button with link
        $response->assertSee(route('admin.leads.show', $lead));
        $response->assertSee('View Lead');
    }

    public function test_my_assigned_leads_table_search_by_name_and_id(): void
    {
        $lead1 = $this->createLeadFor($this->employeeA, ['name' => 'Unique Alpha Corp']);
        $lead2 = $this->createLeadFor($this->employeeA, ['name' => 'Unique Beta Ltd']);

        // Search by name
        $resName = $this->actingAs($this->employeeA)->get(route('dashboard', ['search' => 'Alpha']));
        $resName->assertOk();
        $resName->assertViewHas('tableLeads', function ($tableLeads) {
            return $tableLeads->contains('name', 'Unique Alpha Corp')
                && !$tableLeads->contains('name', 'Unique Beta Ltd');
        });

        // Search by formatted ID "#LD-0000X"
        $formattedId = '#LD-' . str_pad($lead2->id, 5, '0', STR_PAD_LEFT);
        $resId = $this->actingAs($this->employeeA)->get(route('dashboard', ['search' => $formattedId]));
        $resId->assertOk();
        $resId->assertViewHas('tableLeads', function ($tableLeads) {
            return $tableLeads->contains('name', 'Unique Beta Ltd')
                && !$tableLeads->contains('name', 'Unique Alpha Corp');
        });

        // Search by raw ID
        $resRawId = $this->actingAs($this->employeeA)->get(route('dashboard', ['search' => (string)$lead1->id]));
        $resRawId->assertOk();
        $resRawId->assertViewHas('tableLeads', function ($tableLeads) {
            return $tableLeads->contains('name', 'Unique Alpha Corp')
                && !$tableLeads->contains('name', 'Unique Beta Ltd');
        });
    }

    public function test_my_assigned_leads_table_filters_by_status_city_and_loan_product(): void
    {
        $leadMumbai = $this->createLeadFor($this->employeeA, [
            'name' => 'Mumbai BL Lead',
            'city_id' => $this->mumbai->id,
            'loan_product_id' => $this->businessLoan->id,
            'status' => Lead::STATUS_NEW,
        ]);

        $leadPune = $this->createLeadFor($this->employeeA, [
            'name' => 'Pune HL Lead',
            'city_id' => $this->pune->id,
            'loan_product_id' => $this->homeLoan->id,
            'status' => Lead::STATUS_IN_PROGRESS,
        ]);

        // Filter by City: Pune
        $resCity = $this->actingAs($this->employeeA)->get(route('dashboard', ['city_id' => $this->pune->id]));
        $resCity->assertOk();
        $resCity->assertViewHas('tableLeads', function ($tableLeads) {
            return $tableLeads->contains('name', 'Pune HL Lead')
                && !$tableLeads->contains('name', 'Mumbai BL Lead');
        });

        // Filter by Loan Product: Business Loan
        $resProd = $this->actingAs($this->employeeA)->get(route('dashboard', ['loan_product_id' => $this->businessLoan->id]));
        $resProd->assertOk();
        $resProd->assertViewHas('tableLeads', function ($tableLeads) {
            return $tableLeads->contains('name', 'Mumbai BL Lead')
                && !$tableLeads->contains('name', 'Pune HL Lead');
        });

        // Filter by Status: In Progress
        $resStatus = $this->actingAs($this->employeeA)->get(route('dashboard', ['status' => Lead::STATUS_IN_PROGRESS]));
        $resStatus->assertOk();
        $resStatus->assertViewHas('tableLeads', function ($tableLeads) {
            return $tableLeads->contains('name', 'Pune HL Lead')
                && !$tableLeads->contains('name', 'Mumbai BL Lead');
        });

        // Filter by Quick Filter: Active
        $resActive = $this->actingAs($this->employeeA)->get(route('dashboard', ['filter' => 'active']));
        $resActive->assertOk();
        $resActive->assertViewHas('tableLeads', function ($tableLeads) {
            return $tableLeads->contains('name', 'Mumbai BL Lead')
                && $tableLeads->contains('name', 'Pune HL Lead');
        });
    }

    public function test_my_assigned_leads_table_pagination_works_for_large_datasets(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            $this->createLeadFor($this->employeeA, ['name' => sprintf('Paginated Lead %02d', $i)]);
        }

        // Page 1 should contain 10 leads
        $resP1 = $this->actingAs($this->employeeA)->get(route('dashboard', ['page' => 1]));
        $resP1->assertOk();
        $resP1->assertViewHas('tableLeads', function ($paginator) {
            return $paginator->total() === 15 && $paginator->count() === 10;
        });

        // Page 2 should contain 5 leads
        $resP2 = $this->actingAs($this->employeeA)->get(route('dashboard', ['page' => 2]));
        $resP2->assertOk();
        $resP2->assertViewHas('tableLeads', function ($paginator) {
            return $paginator->total() === 15 && $paginator->count() === 5;
        });
    }

    public function test_my_pending_work_widget_groups_by_lead_and_module_with_section_anchors(): void
    {
        $lead = $this->createLeadFor($this->employeeA, ['name' => 'Pendency Sample Lead']);

        $response = $this->actingAs($this->employeeA)->get(route('dashboard'));
        $response->assertOk();

        // Check widget title and lead name in pending work
        $response->assertSee('My Pending Work');
        $response->assertSee('Pendency Sample Lead');

        // Check modules from PendencyService are rendered (e.g. Details Sheet, Documents)
        $response->assertSee('Details Sheet');

        // Check anchor link exists (e.g. #kyc-tab)
        $response->assertSee('#kyc-tab');

        $response->assertViewHas('myPendingWorkByLead', function ($work) use ($lead) {
            $item = collect($work)->firstWhere('lead.id', $lead->id);
            return $item !== null && $item['total_pending'] > 0 && !empty($item['modules']);
        });
    }

    public function test_upcoming_deadlines_reports_gap_when_no_deadline_field_exists(): void
    {
        $this->createLeadFor($this->employeeA, ['name' => 'Lead Without Deadline']);

        $response = $this->actingAs($this->employeeA)->get(route('dashboard'));
        $response->assertOk();

        $response->assertSee('Upcoming Deadlines');
        // Notice indicating the deadline gap
        $response->assertSee('Deadline Field Gap Notice');
        $response->assertSee('No overdue or upcoming deadlines.');
    }

    public function test_recent_activity_stream_displays_assigned_lead_activities(): void
    {
        $lead = $this->createLeadFor($this->employeeA, ['name' => 'Active Customer Lead']);

        // Log specific activity
        $lead->logActivity($this->employeeA->id, 'document_uploaded', 'Aadhar Card Document Uploaded');

        $response = $this->actingAs($this->employeeA)->get(route('dashboard'));
        $response->assertOk();

        $response->assertSee('Recent Activity');
        $response->assertSee('Aadhar Card Document Uploaded');
        $response->assertSee('Active Customer Lead');
    }

    public function test_workload_and_performance_widgets_display_accurately(): void
    {
        $this->createLeadFor($this->employeeA, ['name' => 'Lead Status 1', 'status' => Lead::STATUS_NEW]);
        $this->createLeadFor($this->employeeA, ['name' => 'Lead Status 2', 'status' => Lead::STATUS_IN_PROGRESS]);
        $this->createLeadFor($this->employeeA, ['name' => 'Lead Status 3', 'status' => Lead::STATUS_COMPLETED]);

        $response = $this->actingAs($this->employeeA)->get(route('dashboard'));
        $response->assertOk();

        $response->assertSee('Assigned Leads by Status');
        $response->assertSee('Pending Work by Module');
        $response->assertSee('Completed This Month');
        $response->assertSee('Historical On-Time vs Overdue:');

        $response->assertViewHas('pendingByModule', function ($modules) {
            return is_array($modules) && count($modules) > 0;
        });
    }

    public function test_strict_security_and_tenant_isolation_enforced(): void
    {
        $aliceLead = $this->createLeadFor($this->employeeA, ['name' => 'Confidential Alice Client']);
        $bobLead = $this->createLeadFor($this->employeeB, ['name' => 'Confidential Bob Client']);

        // Alice sees only Alice
        $resAlice = $this->actingAs($this->employeeA)->get(route('dashboard'));
        $resAlice->assertOk();
        $resAlice->assertSee('Confidential Alice Client');
        $resAlice->assertDontSee('Confidential Bob Client');

        // Alice attempts to pass Bob's employee ID in the query to inspect Bob's dashboard
        $resAliceSneaky = $this->actingAs($this->employeeA)->get(route('dashboard', ['assigned_employee_id' => $this->employeeB->id]));
        $resAliceSneaky->assertOk();
        $resAliceSneaky->assertSee('Confidential Alice Client');
        $resAliceSneaky->assertDontSee('Confidential Bob Client');

        // Bob sees only Bob
        $resBob = $this->actingAs($this->employeeB)->get(route('dashboard'));
        $resBob->assertOk();
        $resBob->assertSee('Confidential Bob Client');
        $resBob->assertDontSee('Confidential Alice Client');
    }

    public function test_admin_dashboard_functionality_remains_intact(): void
    {
        $this->createLeadFor($this->employeeA, ['name' => 'Lead For Admin View']);

        $resAdmin = $this->actingAs($this->admin)->get(route('dashboard'));
        $resAdmin->assertOk();
        $resAdmin->assertSee('Total Leads');
        $resAdmin->assertSee('Administrator');
        $resAdmin->assertSee('Lead For Admin View');
    }
}
