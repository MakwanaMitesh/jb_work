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
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardLeadWidgetsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employeeA;
    private User $employeeB;
    private City $surat;
    private City $ahmedabad;
    private City $vadodara;
    private Bank $bank;
    private LoanProduct $businessLoan;
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
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'status' => 'active',
        ]);
        $this->admin->assignRole('Admin');

        $this->employeeA = User::factory()->create([
            'name' => 'Employee Alice',
            'email' => 'alice@example.com',
            'status' => 'active',
        ]);
        $this->employeeA->assignRole('Employee');
        $this->employeeA->givePermissionTo(['dashboard.view', 'leads.view']);

        $this->employeeB = User::factory()->create([
            'name' => 'Employee Bob',
            'email' => 'bob@example.com',
            'status' => 'active',
        ]);
        $this->employeeB->assignRole('Employee');
        $this->employeeB->givePermissionTo(['dashboard.view', 'leads.view']);

        $this->surat = City::create(['name' => 'Surat', 'status' => 'active']);
        $this->ahmedabad = City::create(['name' => 'Ahmedabad', 'status' => 'active']);
        $this->vadodara = City::create(['name' => 'Vadodara', 'status' => 'active']);

        $this->bank = Bank::firstOrCreate(['name' => 'State Bank of India'], ['status' => 'active']);
        $this->businessLoan = LoanProduct::firstOrCreate(['code' => 'BL'], ['name' => 'Business Loan', 'status' => 'active']);
        $this->constitution = CustomerConstitution::firstOrCreate(['code' => 'PROP'], ['name' => 'Proprietorship', 'status' => 'active']);
        $this->agent = Agent::create([
            'first_name' => 'Direct',
            'last_name' => 'Agent',
            'email' => 'direct@example.com',
            'mobile_number' => '+919876543210',
            'city_id' => $this->surat->id,
            'status' => 'active',
        ]);
    }

    private function createLead(array $attributes = []): Lead
    {
        return Lead::create(array_merge([
            'name' => 'Sample Lead',
            'mobile_number' => '+919876500000',
            'email' => 'lead@example.com',
            'city_id' => $this->surat->id,
            'bank_id' => $this->bank->id,
            'loan_product_id' => $this->businessLoan->id,
            'constitution_id' => $this->constitution->id,
            'agent_id' => $this->agent->id,
            'status' => 'new',
            'required_loan_amount' => 1000000,
        ], $attributes));
    }

    public function test_empty_data_states_work_correctly(): void
    {
        // Admin with no leads
        $adminResponse = $this->actingAs($this->admin)->get(route('dashboard'));
        $adminResponse->assertOk();
        $adminResponse->assertSee('Total Leads');
        $adminResponse->assertSee('No lead status data available.');
        $adminResponse->assertSee('No city lead data available.');
        $adminResponse->assertSee('No leads available by city.');
        $adminResponse->assertSee('No leads found.');

        // Employee with no assigned leads
        $empResponse = $this->actingAs($this->employeeA)->get(route('dashboard'));
        $empResponse->assertOk();
        $empResponse->assertSee('Total Assigned Leads');
        $empResponse->assertSee('No assigned leads yet.');
        $empResponse->assertSee('No assigned lead locations.');
        $empResponse->assertSee('No leads available by city.');
        $empResponse->assertSee('No upcoming follow-ups scheduled.');
        $empResponse->assertSee('No recent activities logged.');
    }

    public function test_admin_sees_correct_total_lead_count(): void
    {
        $this->createLead(['name' => 'Lead A', 'assigned_employee_id' => $this->employeeA->id]);
        $this->createLead(['name' => 'Lead B', 'assigned_employee_id' => $this->employeeB->id]);
        $this->createLead(['name' => 'Lead C', 'assigned_employee_id' => null]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertViewHas('leadStats', function ($stats) {
            return $stats['total_leads'] === 3;
        });
    }

    public function test_admin_sees_correct_city_wise_counts(): void
    {
        $this->createLead(['name' => 'Surat 1', 'city_id' => $this->surat->id]);
        $this->createLead(['name' => 'Surat 2', 'city_id' => $this->surat->id]);
        $this->createLead(['name' => 'Ahmedabad 1', 'city_id' => $this->ahmedabad->id]);
        $this->createLead(['name' => 'Vadodara 1', 'city_id' => $this->vadodara->id]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertViewHas('leadsByCity', function ($cities) {
            return $cities->firstWhere('name', 'Surat')?->total === 2
                && $cities->firstWhere('name', 'Ahmedabad')?->total === 1
                && $cities->firstWhere('name', 'Vadodara')?->total === 1;
        });
    }

    public function test_admin_sees_correct_status_wise_counts(): void
    {
        $this->createLead(['name' => 'New 1', 'status' => 'new']);
        $this->createLead(['name' => 'New 2', 'status' => 'new']);
        $this->createLead(['name' => 'Contacted 1', 'status' => 'contacted']);
        $this->createLead(['name' => 'Converted 1', 'status' => 'converted']);
        $this->createLead(['name' => 'Rejected 1', 'status' => 'rejected']);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertViewHas('leadsByStatus', function ($statuses) {
            return $statuses->firstWhere('status', 'new')?->total === 2
                && $statuses->firstWhere('status', 'contacted')?->total === 1
                && $statuses->firstWhere('status', 'converted')?->total === 1
                && $statuses->firstWhere('status', 'rejected')?->total === 1;
        });
    }

    public function test_employee_sees_only_their_assigned_lead_count(): void
    {
        // 2 for Alice
        $this->createLead(['name' => 'Alice 1', 'assigned_employee_id' => $this->employeeA->id]);
        $this->createLead(['name' => 'Alice 2', 'assigned_employee_id' => $this->employeeA->id]);

        // 4 for Bob
        $this->createLead(['name' => 'Bob 1', 'assigned_employee_id' => $this->employeeB->id]);
        $this->createLead(['name' => 'Bob 2', 'assigned_employee_id' => $this->employeeB->id]);
        $this->createLead(['name' => 'Bob 3', 'assigned_employee_id' => $this->employeeB->id]);
        $this->createLead(['name' => 'Bob 4', 'assigned_employee_id' => $this->employeeB->id]);

        // Alice's view
        $responseA = $this->actingAs($this->employeeA)->get(route('dashboard'));
        $responseA->assertOk();
        $responseA->assertViewHas('leadStats', function ($stats) {
            return $stats['total_assigned_leads'] === 2;
        });

        // Bob's view
        $responseB = $this->actingAs($this->employeeB)->get(route('dashboard'));
        $responseB->assertOk();
        $responseB->assertViewHas('leadStats', function ($stats) {
            return $stats['total_assigned_leads'] === 4;
        });
    }

    public function test_employee_status_counts_include_only_their_assigned_leads(): void
    {
        // Alice: 1 new, 1 converted
        $this->createLead(['name' => 'Alice New', 'status' => 'new', 'assigned_employee_id' => $this->employeeA->id]);
        $this->createLead(['name' => 'Alice Converted', 'status' => 'converted', 'assigned_employee_id' => $this->employeeA->id]);

        // Bob: 5 new, 2 rejected
        $this->createLead(['name' => 'Bob New 1', 'status' => 'new', 'assigned_employee_id' => $this->employeeB->id]);
        $this->createLead(['name' => 'Bob New 2', 'status' => 'new', 'assigned_employee_id' => $this->employeeB->id]);
        $this->createLead(['name' => 'Bob Rejected', 'status' => 'rejected', 'assigned_employee_id' => $this->employeeB->id]);

        $responseA = $this->actingAs($this->employeeA)->get(route('dashboard'));
        $responseA->assertOk();
        $responseA->assertViewHas('leadsByStatus', function ($statuses) {
            return $statuses->count() === 2
                && $statuses->firstWhere('status', 'new')?->total === 1
                && $statuses->firstWhere('status', 'converted')?->total === 1
                && $statuses->firstWhere('status', 'rejected') === null;
        });
    }

    public function test_employee_cannot_access_another_employees_dashboard_data(): void
    {
        // Alice Lead in Surat
        $aliceLead = $this->createLead([
            'name' => 'Secret Enterprise of Alice',
            'city_id' => $this->surat->id,
            'assigned_employee_id' => $this->employeeA->id,
        ]);

        // Bob Lead in Vadodara
        $bobLead = $this->createLead([
            'name' => 'Confidential Corp of Bob',
            'city_id' => $this->vadodara->id,
            'assigned_employee_id' => $this->employeeB->id,
        ]);

        // Bob visits Alice's lead? (Bob should NOT see Alice's data on dashboard)
        $bobResponse = $this->actingAs($this->employeeB)->get(route('dashboard'));
        $bobResponse->assertOk();
        $bobResponse->assertSee('Confidential Corp of Bob');
        $bobResponse->assertDontSee('Secret Enterprise of Alice');

        // Alice visits dashboard (Alice should NOT see Bob's data)
        $aliceResponse = $this->actingAs($this->employeeA)->get(route('dashboard'));
        $aliceResponse->assertOk();
        $aliceResponse->assertSee('Secret Enterprise of Alice');
        $aliceResponse->assertDontSee('Confidential Corp of Bob');
    }

    public function test_city_card_exact_counts_and_status_breakdown(): void
    {
        // Surat: 3 New + 2 In Progress = Total 5
        $this->createLead(['name' => 'Surat New 1', 'city_id' => $this->surat->id, 'status' => 'new']);
        $this->createLead(['name' => 'Surat New 2', 'city_id' => $this->surat->id, 'status' => 'new']);
        $this->createLead(['name' => 'Surat New 3', 'city_id' => $this->surat->id, 'status' => 'new']);
        $this->createLead(['name' => 'Surat InProg 1', 'city_id' => $this->surat->id, 'status' => 'in_progress']);
        $this->createLead(['name' => 'Surat InProg 2', 'city_id' => $this->surat->id, 'status' => 'in_progress']);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();

        $response->assertViewHas('cityLeadStatusCards', function ($cards) {
            $surat = $cards->firstWhere('city_name', 'Surat');
            return $surat !== null
                && $surat['total_leads'] === 5
                && ($surat['status_counts']['new'] ?? 0) === 3
                && ($surat['status_counts']['in_progress'] ?? 0) === 2
                && count($surat['status_counts']) === 2;
        });

        // Check HTML renders city name and total count
        $response->assertSee('Surat');
        $response->assertSee('Total: 5');
        $response->assertSee('New');
        $response->assertSee('In Progress');
    }

    public function test_dynamically_shows_all_cities_without_limit(): void
    {
        // Create 20 distinct cities with leads
        for ($i = 1; $i <= 20; $i++) {
            $city = City::create(['name' => sprintf('City %02d', $i), 'status' => 'active']);
            $this->createLead(['name' => "Lead in City {$i}", 'city_id' => $city->id, 'status' => 'new']);
        }

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();

        $response->assertViewHas('cityLeadStatusCards', function ($cards) {
            return $cards->count() === 20;
        });

        for ($i = 1; $i <= 20; $i++) {
            $response->assertSee(sprintf('City %02d', $i));
        }
    }

    public function test_lead_listing_filtering_by_city_name_or_id_and_status(): void
    {
        $suratNew = $this->createLead(['name' => 'Surat New Lead', 'city_id' => $this->surat->id, 'status' => 'new']);
        $suratInProg = $this->createLead(['name' => 'Surat In Progress Lead', 'city_id' => $this->surat->id, 'status' => 'in_progress']);
        $ahmedabadNew = $this->createLead(['name' => 'Ahmedabad New Lead', 'city_id' => $this->ahmedabad->id, 'status' => 'new']);

        // 1. Filter by City ID (Surat)
        $cityIdResponse = $this->actingAs($this->admin)->get(route('admin.leads.index', ['city_id' => $this->surat->id]));
        $cityIdResponse->assertOk();
        $cityIdResponse->assertSee('Surat New Lead');
        $cityIdResponse->assertSee('Surat In Progress Lead');
        $cityIdResponse->assertDontSee('Ahmedabad New Lead');

        // 2. Filter by City Name (Surat)
        $cityNameResponse = $this->actingAs($this->admin)->get(route('admin.leads.index', ['city' => 'Surat']));
        $cityNameResponse->assertOk();
        $cityNameResponse->assertSee('Surat New Lead');
        $cityNameResponse->assertSee('Surat In Progress Lead');
        $cityNameResponse->assertDontSee('Ahmedabad New Lead');

        // 3. Filter by City ID + Status
        $cityStatusResponse = $this->actingAs($this->admin)->get(route('admin.leads.index', [
            'city_id' => $this->surat->id,
            'status' => 'new',
        ]));
        $cityStatusResponse->assertOk();
        $cityStatusResponse->assertSee('Surat New Lead');
        $cityStatusResponse->assertDontSee('Surat In Progress Lead');
        $cityStatusResponse->assertDontSee('Ahmedabad New Lead');

        // 4. Filter by City Name + Status
        $cityNameStatusResponse = $this->actingAs($this->admin)->get(route('admin.leads.index', [
            'city' => 'Surat',
            'status' => 'in_progress',
        ]));
        $cityNameStatusResponse->assertOk();
        $cityNameStatusResponse->assertSee('Surat In Progress Lead');
        $cityNameStatusResponse->assertDontSee('Surat New Lead');
        $cityNameStatusResponse->assertDontSee('Ahmedabad New Lead');
    }

    public function test_employee_city_status_breakdown_strictly_isolated(): void
    {
        // Alice: Surat (3 New, 2 In Progress) = 5
        for ($i = 0; $i < 3; $i++) {
            $this->createLead(['name' => "Alice Surat New {$i}", 'city_id' => $this->surat->id, 'status' => 'new', 'assigned_employee_id' => $this->employeeA->id]);
        }
        for ($i = 0; $i < 2; $i++) {
            $this->createLead(['name' => "Alice Surat InProg {$i}", 'city_id' => $this->surat->id, 'status' => 'in_progress', 'assigned_employee_id' => $this->employeeA->id]);
        }

        // Bob: Surat (10 Approved)
        for ($i = 0; $i < 10; $i++) {
            $this->createLead(['name' => "Bob Surat Appr {$i}", 'city_id' => $this->surat->id, 'status' => 'approved', 'assigned_employee_id' => $this->employeeB->id]);
        }

        $responseA = $this->actingAs($this->employeeA)->get(route('dashboard'));
        $responseA->assertOk();
        $responseA->assertViewHas('cityLeadStatusCards', function ($cards) {
            $surat = $cards->firstWhere('city_name', 'Surat');
            return $surat !== null
                && $surat['total_leads'] === 5
                && ($surat['status_counts']['new'] ?? 0) === 3
                && ($surat['status_counts']['in_progress'] ?? 0) === 2
                && ($surat['status_counts']['approved'] ?? 0) === 0;
        });

        $responseB = $this->actingAs($this->employeeB)->get(route('dashboard'));
        $responseB->assertOk();
        $responseB->assertViewHas('cityLeadStatusCards', function ($cards) {
            $surat = $cards->firstWhere('city_name', 'Surat');
            return $surat !== null
                && $surat['total_leads'] === 10
                && ($surat['status_counts']['approved'] ?? 0) === 10
                && ($surat['status_counts']['new'] ?? 0) === 0;
        });
    }

    public function test_leads_by_city_widget_displays_cities_and_total_counts(): void
    {
        // Surat: 2 New, 1 Approved => Total 3
        $this->createLead(['name' => 'Surat Lead 1', 'city_id' => $this->surat->id, 'status' => 'new']);
        $this->createLead(['name' => 'Surat Lead 2', 'city_id' => $this->surat->id, 'status' => 'new']);
        $this->createLead(['name' => 'Surat Lead 3', 'city_id' => $this->surat->id, 'status' => 'approved']);

        // Ahmedabad: 1 In Progress => Total 1
        $this->createLead(['name' => 'Ahmd Lead 1', 'city_id' => $this->ahmedabad->id, 'status' => 'in_progress']);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();

        $response->assertViewHas('leadsByCity', function ($leadsByCity) {
            $suratItem = $leadsByCity->firstWhere('name', 'Surat');
            $ahmdItem = $leadsByCity->firstWhere('name', 'Ahmedabad');

            return $suratItem !== null
                && $suratItem->total === 3
                && $ahmdItem !== null
                && $ahmdItem->total === 1;
        });

        // Verify HTML markup in Leads by City widget contains city names and badges
        $response->assertSee('Surat');
        $response->assertSee('Ahmedabad');
        $response->assertSee('3');
        $response->assertSee('1');
    }
}

