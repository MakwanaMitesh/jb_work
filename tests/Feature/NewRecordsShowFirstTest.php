<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AssessmentYear;
use App\Models\Bank;
use App\Models\City;
use App\Models\ContactEnquiry;
use App\Models\CustomerConstitution;
use App\Models\DocumentType;
use App\Models\Lead;
use App\Models\LoanProduct;
use App\Models\RoughLead;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NewRecordsShowFirstTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('Admin');
    }

    public function test_new_leads_show_first(): void
    {
        $lead1 = Lead::create([
            'name' => 'First Older Lead',
            'mobile_number' => '9800000001',
            'status' => Lead::STATUS_NEW,
            'created_at' => now()->subHours(2),
        ]);

        $lead2 = Lead::create([
            'name' => 'Second Newer Lead',
            'mobile_number' => '9800000002',
            'status' => Lead::STATUS_NEW,
            'created_at' => now()->subHour(1),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.leads.index'));
        $response->assertOk();

        $leads = $response->viewData('leads');
        $this->assertEquals($lead2->id, $leads->first()->id);
    }

    public function test_new_employees_show_first(): void
    {
        $city = City::create(['name' => 'Mumbai', 'status' => 'active']);

        $emp1 = User::create([
            'name' => 'Aaron Older Employee',
            'email' => 'aaron@test.com',
            'password' => bcrypt('password'),
            'city_id' => $city->id,
            'status' => 'active',
            'created_at' => now()->subHours(2),
        ]);
        $emp1->assignRole('Employee');

        $emp2 = User::create([
            'name' => 'Zara Newer Employee',
            'email' => 'zara@test.com',
            'password' => bcrypt('password'),
            'city_id' => $city->id,
            'status' => 'active',
            'created_at' => now()->subHour(1),
        ]);
        $emp2->assignRole('Employee');

        $response = $this->actingAs($this->admin)->get(route('admin.employees.index'));
        $response->assertOk();

        $employees = $response->viewData('employees');
        $this->assertEquals($emp2->id, $employees->first()->id);
    }

    public function test_new_agents_show_first(): void
    {
        $city = City::create(['name' => 'Pune', 'status' => 'active']);

        $agent1 = Agent::create([
            'first_name' => 'Adam',
            'last_name' => 'Older',
            'email' => 'adam@test.com',
            'mobile_number' => '9100000001',
            'city_id' => $city->id,
            'status' => 'active',
            'created_at' => now()->subHours(2),
        ]);

        $agent2 = Agent::create([
            'first_name' => 'Zack',
            'last_name' => 'Newer',
            'email' => 'zack@test.com',
            'mobile_number' => '9100000002',
            'city_id' => $city->id,
            'status' => 'active',
            'created_at' => now()->subHour(1),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.agents.index'));
        $response->assertOk();

        $agents = $response->viewData('agents');
        $this->assertEquals($agent2->id, $agents->first()->id);
    }

    public function test_new_cities_show_first(): void
    {
        $city1 = City::create(['name' => 'Ahmedabad Older', 'status' => 'active', 'created_at' => now()->subHours(2)]);
        $city2 = City::create(['name' => 'Zirakpur Newer', 'status' => 'active', 'created_at' => now()->subHour(1)]);

        $response = $this->actingAs($this->admin)->get(route('admin.cities.index'));
        $response->assertOk();

        $cities = $response->viewData('cities');
        $this->assertEquals($city2->id, $cities->first()->id);
    }

    public function test_new_banks_show_first(): void
    {
        $bank1 = Bank::create(['name' => 'Axis Bank Older', 'status' => 'active', 'sort_order' => 1, 'created_at' => now()->subHours(2)]);
        $bank2 = Bank::create(['name' => 'Yes Bank Newer', 'status' => 'active', 'sort_order' => 2, 'created_at' => now()->subHour(1)]);

        $response = $this->actingAs($this->admin)->get(route('admin.banks.index'));
        $response->assertOk();

        $banks = $response->viewData('banks');
        $this->assertEquals($bank2->id, $banks->first()->id);
    }

    public function test_new_loan_products_show_first(): void
    {
        $prod1 = LoanProduct::create(['code' => 'ALP', 'name' => 'Auto Loan Older', 'status' => 'active', 'sort_order' => 1, 'created_at' => now()->subHours(2)]);
        $prod2 = LoanProduct::create(['code' => 'ZLP', 'name' => 'Zero Down Loan Newer', 'status' => 'active', 'sort_order' => 2, 'created_at' => now()->subHour(1)]);

        $response = $this->actingAs($this->admin)->get(route('admin.loan-products.index'));
        $response->assertOk();

        $products = $response->viewData('products');
        $this->assertEquals($prod2->id, $products->first()->id);
    }

    public function test_new_constitutions_show_first(): void
    {
        $c1 = CustomerConstitution::create(['code' => 'ACC', 'name' => 'Association Older', 'status' => 'active', 'sort_order' => 1, 'created_at' => now()->subHours(2)]);
        $c2 = CustomerConstitution::create(['code' => 'ZCC', 'name' => 'Zonal Body Newer', 'status' => 'active', 'sort_order' => 2, 'created_at' => now()->subHour(1)]);

        $response = $this->actingAs($this->admin)->get(route('admin.constitutions.index'));
        $response->assertOk();

        $constitutions = $response->viewData('constitutions');
        $this->assertEquals($c2->id, $constitutions->first()->id);
    }

    public function test_new_document_types_with_default_order_show_first(): void
    {
        $dt1 = DocumentType::create(['code' => 'ADT', 'name' => 'Older Doc', 'status' => 'active', 'sort_order' => 10]);
        $dt2 = DocumentType::create(['code' => 'ZDT', 'name' => 'Newer Doc', 'status' => 'active', 'sort_order' => 0]);

        $response = $this->actingAs($this->admin)->get(route('admin.document-types.index'));
        $response->assertOk();

        $types = $response->viewData('documentTypes');
        $this->assertEquals($dt2->id, $types->first()->id);
    }

    public function test_new_assessment_years_show_first(): void
    {
        $ay1 = AssessmentYear::create(['code' => 'AY1', 'name' => '2024-25 Older', 'status' => 'active', 'sort_order' => 1, 'created_at' => now()->subHours(2)]);
        $ay2 = AssessmentYear::create(['code' => 'AY2', 'name' => '2027-28 Newer', 'status' => 'active', 'sort_order' => 2, 'created_at' => now()->subHour(1)]);

        $response = $this->actingAs($this->admin)->get(route('admin.assessment-years.index'));
        $response->assertOk();

        $years = $response->viewData('assessmentYears');
        $this->assertEquals($ay2->id, $years->first()->id);
    }

    public function test_new_roles_show_first(): void
    {
        $r1 = Role::create(['name' => 'Alpha Role Older', 'guard_name' => 'web']);
        // Sleep or step timestamp forward
        $r2 = Role::create(['name' => 'Omega Role Newer', 'guard_name' => 'web', 'created_at' => now()->addMinutes(5)]);

        $response = $this->actingAs($this->admin)->get(route('admin.roles.index'));
        $response->assertOk();

        $roles = $response->viewData('roles');
        $this->assertEquals($r2->id, $roles->first()->id);
    }

    public function test_new_rough_leads_show_first(): void
    {
        $rl1 = RoughLead::create([
            'full_name' => 'Older Rough Lead',
            'mobile_number' => '9700000001',
            'status' => 'new',
            'created_at' => now()->subHours(2),
        ]);

        $rl2 = RoughLead::create([
            'full_name' => 'Newer Rough Lead',
            'mobile_number' => '9700000002',
            'status' => 'new',
            'created_at' => now()->subHour(1),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.rough-leads.index'));
        $response->assertOk();

        $roughLeads = $response->viewData('roughLeads');
        $this->assertEquals($rl2->id, $roughLeads->first()->id);
    }

    public function test_new_contact_enquiries_show_first(): void
    {
        $ce1 = ContactEnquiry::create([
            'full_name' => 'Older Enquiry',
            'mobile' => '9600000001',
            'email' => 'old@test.com',
            'status' => ContactEnquiry::STATUS_NEW,
            'created_at' => now()->subHours(2),
        ]);

        $ce2 = ContactEnquiry::create([
            'full_name' => 'Newer Enquiry',
            'mobile' => '9600000002',
            'email' => 'new@test.com',
            'status' => ContactEnquiry::STATUS_NEW,
            'created_at' => now()->subHour(1),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.contact-enquiries.index'));
        $response->assertOk();

        $enquiries = $response->viewData('enquiries');
        $this->assertEquals($ce2->id, $enquiries->first()->id);
    }

    public function test_new_pendency_leads_show_first(): void
    {
        $lead1 = Lead::create([
            'name' => 'Older Pendency Lead',
            'mobile_number' => '9500000001',
            'status' => Lead::STATUS_NEW,
            'created_at' => now()->subHours(2),
        ]);

        $lead2 = Lead::create([
            'name' => 'Newer Pendency Lead',
            'mobile_number' => '9500000002',
            'status' => Lead::STATUS_NEW,
            'created_at' => now()->subHour(1),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.pendency.index'));
        $response->assertOk();

        $summaries = $response->viewData('summaries');
        $this->assertEquals($lead2->id, $summaries[0]['lead']->id);
    }

    public function test_new_assigned_leads_show_first_in_employee_dashboard(): void
    {
        $employee = User::factory()->create(['status' => 'active']);
        $employee->assignRole('Employee');
        $employee->givePermissionTo(['dashboard.view', 'leads.view', 'pendency.view']);

        $lead1 = Lead::create([
            'name' => 'Older Assigned Lead',
            'mobile_number' => '9400000001',
            'status' => Lead::STATUS_NEW,
            'assigned_employee_id' => $employee->id,
            'created_at' => now()->subHours(2),
        ]);

        $lead2 = Lead::create([
            'name' => 'Newer Assigned Lead',
            'mobile_number' => '9400000002',
            'status' => Lead::STATUS_NEW,
            'assigned_employee_id' => $employee->id,
            'created_at' => now()->subHour(1),
        ]);

        $response = $this->actingAs($employee)->get(route('dashboard'));
        $response->assertOk();

        $tableLeads = $response->viewData('tableLeads');
        $this->assertEquals($lead2->id, $tableLeads->first()->id);
    }
}
