<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\RoughLead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoughLeadManagementTest extends TestCase
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

    public function test_admin_can_view_rough_leads_list(): void
    {
        RoughLead::factory()->create(['full_name' => 'Ramesh Patel']);

        $response = $this->actingAs($this->admin)->get(route('admin.rough-leads.index'));

        $response->assertStatus(200);
        $response->assertSee('Ramesh Patel');
    }

    public function test_admin_can_create_rough_lead(): void
    {
        $payload = [
            'full_name' => 'Suresh Shah',
            'mobile_number' => '+919876543210',
            'alternate_mobile_number' => null,
            'email' => 'suresh@example.com',
            'city_id' => null,
            'loan_requirement' => 'Business Loan',
            'description' => 'Interested in a CC limit.',
            'source' => 'Walk-in',
            'assigned_employee_id' => null,
            'status' => 'new',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.rough-leads.store'), $payload);

        $response->assertRedirect(route('admin.rough-leads.index'));
        $this->assertDatabaseHas('rough_leads', [
            'full_name' => 'Suresh Shah',
            'mobile_number' => '+919876543210',
            'status' => 'new',
        ]);
    }

    public function test_rough_lead_creation_requires_full_name_and_mobile_number(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.rough-leads.store'), [
            'status' => 'new',
        ]);

        $response->assertSessionHasErrors(['full_name', 'mobile_number']);
    }

    public function test_admin_can_view_rough_lead_details(): void
    {
        $roughLead = RoughLead::factory()->create(['full_name' => 'Dipak Mehta']);

        $response = $this->actingAs($this->admin)->get(route('admin.rough-leads.show', $roughLead));

        $response->assertStatus(200);
        $response->assertSee('Dipak Mehta');
    }

    public function test_admin_can_update_rough_lead(): void
    {
        $roughLead = RoughLead::factory()->create(['full_name' => 'Old Name', 'status' => 'new']);

        $payload = [
            'full_name' => 'New Name',
            'mobile_number' => $roughLead->mobile_number,
            'status' => 'contacted',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.rough-leads.update', $roughLead), $payload);

        $response->assertRedirect(route('admin.rough-leads.index'));
        $roughLead->refresh();
        $this->assertSame('New Name', $roughLead->full_name);
        $this->assertSame('contacted', $roughLead->status);
    }

    public function test_admin_can_delete_rough_lead(): void
    {
        $roughLead = RoughLead::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.rough-leads.destroy', $roughLead));

        $response->assertRedirect(route('admin.rough-leads.index'));
        $this->assertSoftDeleted('rough_leads', ['id' => $roughLead->id]);
    }

    public function test_user_without_permission_cannot_manage_rough_leads(): void
    {
        $employee = User::factory()->create(['status' => 'active']);
        $employee->assignRole('Employee');

        $roughLead = RoughLead::factory()->create();

        $this->actingAs($employee)->get(route('admin.rough-leads.index'))->assertStatus(403);
        $this->actingAs($employee)->get(route('admin.rough-leads.create'))->assertStatus(403);
        $this->actingAs($employee)->post(route('admin.rough-leads.store'), [])->assertStatus(403);
        $this->actingAs($employee)->get(route('admin.rough-leads.show', $roughLead))->assertStatus(403);
        $this->actingAs($employee)->get(route('admin.rough-leads.edit', $roughLead))->assertStatus(403);
        $this->actingAs($employee)->put(route('admin.rough-leads.update', $roughLead), [])->assertStatus(403);
        $this->actingAs($employee)->delete(route('admin.rough-leads.destroy', $roughLead))->assertStatus(403);
        $this->actingAs($employee)->post(route('admin.rough-leads.convert', $roughLead))->assertStatus(403);
    }

    public function test_user_with_only_convert_permission_can_convert(): void
    {
        $employee = User::factory()->create(['status' => 'active']);
        $employee->assignRole('Employee');
        $employee->givePermissionTo('rough_leads.convert');

        $roughLead = RoughLead::factory()->create();

        $response = $this->actingAs($employee)->post(route('admin.rough-leads.convert', $roughLead));

        $response->assertRedirect(route('admin.rough-leads.show', $roughLead));
        $this->assertNotNull($roughLead->fresh()->converted_lead_id);
    }

    public function test_admin_can_convert_rough_lead_to_main_lead(): void
    {
        $roughLead = RoughLead::factory()->create([
            'full_name' => 'Kiran Joshi',
            'mobile_number' => '+919998887777',
            'email' => 'kiran@example.com',
            'loan_requirement' => 'Term Loan',
            'description' => 'Wants to expand factory.',
            'status' => 'qualified',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.rough-leads.convert', $roughLead));

        $roughLead->refresh();
        $response->assertRedirect(route('admin.rough-leads.show', $roughLead));
        $this->assertTrue($roughLead->isConverted());
        $this->assertSame('converted', $roughLead->status);
        $this->assertNotNull($roughLead->converted_at);

        $lead = Lead::findOrFail($roughLead->converted_lead_id);
        $this->assertSame('Kiran Joshi', $lead->name);
        $this->assertSame('+919998887777', $lead->mobile_number);
        $this->assertSame('kiran@example.com', $lead->email);

        // The Rough Lead record itself is never duplicated — only one row
        // for it should ever exist.
        $this->assertSame(1, RoughLead::where('full_name', 'Kiran Joshi')->count());
    }

    public function test_admin_can_change_rough_lead_status_from_the_listing_screen(): void
    {
        $roughLead = RoughLead::factory()->create(['status' => 'new']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.rough-leads.status', $roughLead), ['status' => 'contacted']);

        $response->assertRedirect(route('admin.rough-leads.index'));
        $this->assertSame('contacted', $roughLead->fresh()->status);
    }

    public function test_status_cannot_be_manually_set_to_converted(): void
    {
        $roughLead = RoughLead::factory()->create(['status' => 'new']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.rough-leads.status', $roughLead), ['status' => 'converted']);

        $response->assertSessionHasErrors('status');
        $this->assertSame('new', $roughLead->fresh()->status);
    }

    public function test_status_cannot_be_changed_after_conversion(): void
    {
        $roughLead = RoughLead::factory()->create();
        $this->actingAs($this->admin)->post(route('admin.rough-leads.convert', $roughLead));
        $roughLead->refresh();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.rough-leads.status', $roughLead), ['status' => 'closed']);

        $response->assertRedirect(route('admin.rough-leads.index'));
        $this->assertSame('converted', $roughLead->fresh()->status);
    }

    public function test_user_without_change_status_permission_cannot_update_status(): void
    {
        $employee = User::factory()->create(['status' => 'active']);
        $employee->assignRole('Employee');
        $employee->givePermissionTo('rough_leads.view');

        $roughLead = RoughLead::factory()->create(['status' => 'new']);

        $this->actingAs($employee)
            ->post(route('admin.rough-leads.status', $roughLead), ['status' => 'contacted'])
            ->assertStatus(403);
    }

    public function test_converting_an_already_converted_rough_lead_does_not_create_a_duplicate_lead(): void
    {
        $roughLead = RoughLead::factory()->create();

        $this->actingAs($this->admin)->post(route('admin.rough-leads.convert', $roughLead));
        $roughLead->refresh();
        $firstLeadId = $roughLead->converted_lead_id;

        $response = $this->actingAs($this->admin)->post(route('admin.rough-leads.convert', $roughLead));

        $response->assertRedirect(route('admin.rough-leads.show', $roughLead));
        $roughLead->refresh();
        $this->assertSame($firstLeadId, $roughLead->converted_lead_id);
        $this->assertSame(1, Lead::count());
    }
}
