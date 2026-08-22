<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\City;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private City $city;
    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $this->city = City::create(['name' => 'Austin', 'status' => 'active']);
        $this->agent = Agent::create([
            'first_name' => 'Bob',
            'last_name' => 'Agent',
            'email' => 'bob.agent@example.com',
            'mobile_number' => '+919876543210',
            'city_id' => $this->city->id,
            'status' => 'active',
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Lead Customer',
            'email' => 'customer@example.com',
            'mobile_number' => '+919876543210',
            'alternate_mobile_number' => '+918765432109',
            'agent_id' => $this->agent->id,
            'city_id' => $this->city->id,
            'source' => 'Website',
            'status' => 'new',
            'notes' => 'Some sample requirements.',
        ], $overrides);
    }

    public function test_admin_can_create_a_lead(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), $this->validPayload());

        $response->assertRedirect(route('admin.leads.index'));

        $lead = Lead::where('email', 'customer@example.com')->first();
        $this->assertNotNull($lead);
        $this->assertSame('Lead Customer', $lead->name);
        $this->assertSame('Website', $lead->source);
        $this->assertSame('new', $lead->status);
        $this->assertSame($this->agent->id, $lead->agent_id);
    }

    public function test_required_fields_are_validated(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), []);

        $response->assertSessionHasErrors(['name', 'mobile_number', 'status']);
    }

    public function test_user_without_create_permission_cannot_create_lead(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Employee'); // no leads.create permission by default

        $response = $this->actingAs($user)
            ->post(route('admin.leads.store'), $this->validPayload());

        $response->assertStatus(403);
    }

    public function test_admin_can_update_a_lead(): void
    {
        $lead = Lead::create($this->validPayload());

        $response = $this->actingAs($this->admin)
            ->put(route('admin.leads.update', $lead), $this->validPayload([
                'name' => 'Updated Customer',
                'status' => 'contacted',
            ]));

        $response->assertRedirect(route('admin.leads.index'));
        $this->assertSame('Updated Customer', $lead->fresh()->name);
        $this->assertSame('contacted', $lead->fresh()->status);
    }

    public function test_admin_can_delete_a_lead_and_it_is_soft_deleted(): void
    {
        $lead = Lead::create($this->validPayload());

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.leads.destroy', $lead));

        $response->assertRedirect(route('admin.leads.index'));
        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
    }

    public function test_filters_and_search_work(): void
    {
        $cityDallas = City::create(['name' => 'Dallas', 'status' => 'active']);
        
        $lead1 = Lead::create($this->validPayload(['name' => 'Alice Search', 'status' => 'new']));
        $lead2 = Lead::create($this->validPayload(['name' => 'Bob Dallas', 'city_id' => $cityDallas->id, 'status' => 'converted']));

        // Test search
        $response = $this->actingAs($this->admin)
            ->get(route('admin.leads.index', ['search' => 'Alice']));
        $leads = $response->viewData('leads');
        $this->assertTrue($leads->contains('name', 'Alice Search'));
        $this->assertFalse($leads->contains('name', 'Bob Dallas'));

        // Test status filter
        $response = $this->actingAs($this->admin)
            ->get(route('admin.leads.index', ['status' => 'converted']));
        $leads = $response->viewData('leads');
        $this->assertTrue($leads->contains('name', 'Bob Dallas'));
        $this->assertFalse($leads->contains('name', 'Alice Search'));

        // Test city filter
        $response = $this->actingAs($this->admin)
            ->get(route('admin.leads.index', ['city_id' => $cityDallas->id]));
        $leads = $response->viewData('leads');
        $this->assertTrue($leads->contains('name', 'Bob Dallas'));
        $this->assertFalse($leads->contains('name', 'Alice Search'));
    }
    public function test_admin_can_store_and_update_detailed_lead_fields(): void
    {
        $detailedPayload = $this->validPayload([
            'date_of_birth' => '1990-05-15',
            'gender' => 'Male',
            'address' => "123 Main St\nSuite 100",
            'aadhar_card' => '1234-5678-9012',
            'pan_card' => 'ABCDE1234F',
            'udyam_registration' => 'UDYAM-TX-00-1234567',
            'education' => 'Bachelor of Engineering',
            'mother_name' => 'Jane Doe',
            'itr_id' => 'itr_user_123',
            'itr_password' => 'secretPass!',
            'itr_audited' => 'Yes',
            'itr_ay_2026_27' => '1',
            'itr_ay_2025_26' => '1',
            'itr_ay_2024_25' => '0',
            'bank_details' => [
                [
                    'bank_name' => 'First National Bank',
                    'account_number' => '9876543210',
                    'account_type' => 'Savings',
                    'ifsc_code' => 'FNBK0001234',
                ],
            ],
            'business_name' => 'Tech Solutions LLC',
            'constitution_of_business' => 'Proprietorship',
            'introduction' => 'Providing IT services',
            'business_address' => '456 Business Rd',
            'gst_applicable' => 'Yes',
            'gst_number' => '22AAAAA1111A1Z1',
            'gst_id' => 'gst_user',
            'gst_password' => 'gstPass123',
            'firm_name' => 'Tech Solutions',
            'business_activity' => 'Services',
            'business_experience' => '3 Years',
            'no_of_manpower' => '10',
            'business_location' => 'Downtown',
            'area_of_premises' => '1000 sq ft',
            'connectivity' => 'High-speed Fiber',
            'required_loan_amount' => '500,000',
            'cc_amount' => '200,000',
            'cc_details' => 'Stock-in-trade',
            'term_loan_amount' => '300,000',
            'term_loan_machinery_details' => 'Servers and workstations',
            'current_loans' => [
                [
                    'bank_name' => 'Second Union Bank',
                    'loan_type' => 'Equipment Loan',
                    'loan_amount' => '150,000',
                    'disburse_date' => '2025-01-10',
                    'emi' => '5,000',
                    'outstanding_amount' => '80,000',
                    'tenure' => '36 Months',
                ],
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), $detailedPayload);

        $response->assertRedirect(route('admin.leads.index'));

        $lead = Lead::where('email', 'customer@example.com')->first();
        $this->assertNotNull($lead);
        $this->assertSame('Male', $lead->gender);
        $this->assertSame('Jane Doe', $lead->mother_name);
        $this->assertTrue($lead->itr_ay_2026_27);
        $this->assertFalse($lead->itr_ay_2024_25);
        $this->assertIsArray($lead->bank_details);
        $this->assertSame('First National Bank', $lead->bank_details[0]['bank_name']);
        $this->assertSame('Tech Solutions LLC', $lead->business_name);
        $this->assertIsArray($lead->current_loans);
        $this->assertSame('Equipment Loan', $lead->current_loans[0]['loan_type']);
    }
}
