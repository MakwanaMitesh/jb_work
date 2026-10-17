<?php

namespace Tests\Feature;

use App\Models\ContactEnquiry;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactEnquiryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('Admin');
    }

    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Chartered Accountant & Financial Advisory', false);
        $response->assertSee('Bank Loan Syndication');
        $response->assertSee('CMA Report Preparation');
        $response->assertSee('Why Choose Our Financial Consultancy');
        $response->assertSee('Request a Consultation');
    }

    public function test_landing_page_contains_all_required_sections_and_fields(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        // Nav Links
        $response->assertSee('#home');
        $response->assertSee('#about');
        $response->assertSee('#services');
        $response->assertSee('#why-us');
        $response->assertSee('#contact');

        // Form Fields
        $response->assertSee('name="full_name"', false);
        $response->assertSee('name="mobile"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="city"', false);
        $response->assertSee('name="loan_requirement"', false);
        $response->assertSee('name="message"', false);
    }

    public function test_visitor_can_submit_contact_enquiry(): void
    {
        $payload = [
            'full_name' => 'Rajesh Sharma',
            'mobile' => '9876543210',
            'email' => 'rajesh@example.com',
            'city' => 'Surat',
            'loan_requirement' => 'Business Loan ₹50 Lakhs',
            'message' => 'Looking for working capital loan assistance for textile business.',
        ];

        $response = $this->post(route('contact-enquiries.store'), $payload);

        $response->assertRedirect('/#contact');
        $response->assertSessionHas('contact_success');

        $this->assertDatabaseHas('contact_enquiries', [
            'full_name' => 'Rajesh Sharma',
            'mobile' => '9876543210',
            'email' => 'rajesh@example.com',
            'city' => 'Surat',
            'loan_requirement' => 'Business Loan ₹50 Lakhs',
            'status' => ContactEnquiry::STATUS_NEW,
        ]);
    }

    public function test_visitor_submission_validates_required_fields(): void
    {
        $response = $this->post(route('contact-enquiries.store'), [
            'full_name' => '',
            'mobile' => '',
        ]);

        $response->assertSessionHasErrors(['full_name', 'mobile']);
        $this->assertEquals(0, ContactEnquiry::count());
    }

    public function test_visitor_submission_with_json_returns_json_response(): void
    {
        $payload = [
            'full_name' => 'Anil Verma',
            'mobile' => '9123456780',
            'email' => 'anil@example.com',
            'city' => 'Ahmedabad',
            'loan_requirement' => 'CMA Preparation',
        ];

        $response = $this->postJson(route('contact-enquiries.store'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'enquiry' => [
                'full_name' => 'Anil Verma',
                'mobile' => '9123456780',
                'status' => ContactEnquiry::STATUS_NEW,
            ],
        ]);
    }

    public function test_admin_can_view_contact_enquiries_list(): void
    {
        ContactEnquiry::factory()->create([
            'full_name' => 'Nitin Shah',
            'mobile' => '9898989898',
            'city' => 'Vadodara',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.contact-enquiries.index'));

        $response->assertOk();
        $response->assertSee('Nitin Shah');
        $response->assertSee('9898989898');
        $response->assertSee('Vadodara');
    }

    public function test_admin_can_search_contact_enquiries(): void
    {
        ContactEnquiry::factory()->create(['full_name' => 'Ketan Mehta', 'mobile' => '9111111111']);
        ContactEnquiry::factory()->create(['full_name' => 'Bhavin Patel', 'mobile' => '9222222222']);

        $response = $this->actingAs($this->admin)->get(route('admin.contact-enquiries.index', ['search' => 'Ketan']));

        $response->assertOk();
        $response->assertSee('Ketan Mehta');
        $response->assertDontSee('Bhavin Patel');
    }

    public function test_admin_can_filter_contact_enquiries_by_status(): void
    {
        ContactEnquiry::factory()->create(['full_name' => 'Lead One', 'status' => ContactEnquiry::STATUS_NEW]);
        ContactEnquiry::factory()->create(['full_name' => 'Lead Two', 'status' => ContactEnquiry::STATUS_CONVERTED]);

        $response = $this->actingAs($this->admin)->get(route('admin.contact-enquiries.index', ['status' => ContactEnquiry::STATUS_CONVERTED]));

        $response->assertOk();
        $response->assertSee('Lead Two');
        $response->assertDontSee('Lead One');
    }

    public function test_admin_can_view_contact_enquiry_details(): void
    {
        $enquiry = ContactEnquiry::factory()->create([
            'full_name' => 'Vikram Singhania',
            'mobile' => '9870000000',
            'email' => 'vikram@example.com',
            'city' => 'Rajkot',
            'loan_requirement' => 'DPR Preparation for New Plant',
            'message' => 'Need complete project report for bank financing of ₹5 Crore.',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.contact-enquiries.show', $enquiry));

        $response->assertOk();
        $response->assertSee('Vikram Singhania');
        $response->assertSee('9870000000');
        $response->assertSee('vikram@example.com');
        $response->assertSee('Rajkot');
        $response->assertSee('DPR Preparation for New Plant');
        $response->assertSee('Need complete project report for bank financing of ₹5 Crore.');
    }

    public function test_admin_can_update_contact_enquiry_status(): void
    {
        $enquiry = ContactEnquiry::factory()->create(['status' => ContactEnquiry::STATUS_NEW]);

        $response = $this->actingAs($this->admin)->post(route('admin.contact-enquiries.status', $enquiry), [
            'status' => ContactEnquiry::STATUS_CONTACTED,
        ]);

        $response->assertRedirect();
        $this->assertEquals(ContactEnquiry::STATUS_CONTACTED, $enquiry->fresh()->status);
    }

    public function test_admin_can_delete_contact_enquiry(): void
    {
        $enquiry = ContactEnquiry::factory()->create(['full_name' => 'To Delete']);

        $response = $this->actingAs($this->admin)->delete(route('admin.contact-enquiries.destroy', $enquiry));

        $response->assertRedirect(route('admin.contact-enquiries.index'));
        $this->assertDatabaseMissing('contact_enquiries', ['id' => $enquiry->id]);
    }

    public function test_unauthenticated_user_cannot_access_admin_contact_enquiries(): void
    {
        $response = $this->get(route('admin.contact-enquiries.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_view_contact_enquiries(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        // Plain user has no permissions

        $response = $this->actingAs($user)->get(route('admin.contact-enquiries.index'));
        $response->assertForbidden();
    }

    public function test_user_without_permission_cannot_update_contact_enquiry_status(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $enquiry = ContactEnquiry::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.contact-enquiries.status', $enquiry), [
            'status' => ContactEnquiry::STATUS_CONTACTED,
        ]);
        $response->assertForbidden();
    }

    public function test_user_without_permission_cannot_delete_contact_enquiry(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $enquiry = ContactEnquiry::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.contact-enquiries.destroy', $enquiry));
        $response->assertForbidden();
    }

    public function test_user_with_specific_permission_can_perform_allowed_actions(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $permView = Permission::where('name', 'contact_enquiry.view')->firstOrFail();
        $user->givePermissionTo($permView);

        $enquiry = ContactEnquiry::factory()->create(['full_name' => 'Accessible Lead']);

        // View allowed
        $response = $this->actingAs($user)->get(route('admin.contact-enquiries.index'));
        $response->assertOk();
        $response->assertSee('Accessible Lead');

        // Status update forbidden
        $responseUpdate = $this->actingAs($user)->post(route('admin.contact-enquiries.status', $enquiry), [
            'status' => ContactEnquiry::STATUS_CONTACTED,
        ]);
        $responseUpdate->assertForbidden();

        // Delete forbidden
        $responseDelete = $this->actingAs($user)->delete(route('admin.contact-enquiries.destroy', $enquiry));
        $responseDelete->assertForbidden();
    }
}
