<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\City;
use App\Models\Lead;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;
    private User $unauthorizedUser;
    private City $city;
    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        // Admin User (has all permissions)
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        // Employee User
        $this->employee = User::factory()->create();
        $this->employee->assignRole('Employee');

        // Unauthorized User (Remove roles just to be sure)
        $this->unauthorizedUser = User::factory()->create();

        $this->city = City::create(['name' => 'Austin', 'status' => 'active']);
        $this->agent = Agent::create([
            'first_name' => 'Bob',
            'last_name' => 'Agent',
            'email' => 'bob.agent@example.com',
            'mobile_number' => '+919876543210',
            'city_id' => $this->city->id,
            'status' => 'active',
        ]);

        $this->product = \App\Models\LoanProduct::create([
            'name' => 'Home Loan',
            'code' => 'HL',
            'status' => 'active',
        ]);

        $this->constitution = \App\Models\CustomerConstitution::create([
            'name' => 'Individual',
            'code' => 'INDIVIDUAL',
            'status' => 'active',
        ]);

        \App\Models\LoanProductConstitution::create([
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
            'status' => 'active',
        ]);
    }

    private function validPayload(): array
    {
        return [
            'name' => 'John Lead',
            'email' => 'john.lead@example.com',
            'mobile_number' => '+919876543210',
            'agent_id' => $this->agent->id,
            'loan_product_id' => $this->product->id,
            'constitution_id' => $this->constitution->id,
            'city_id' => $this->city->id,
            'source' => 'Ad',
            'status' => 'new',
        ];
    }

    public function test_complete_lead_workflow_flow(): void
    {
        // 1. Create Lead
        $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), $this->validPayload());

        $lead = Lead::where('email', 'john.lead@example.com')->first();
        $this->assertNotNull($lead);
        $this->assertSame('new', $lead->status);
        
        // Verify Lead Created activity
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'created',
            'description' => 'Lead created',
        ]);

        // 2. Assign Lead to Employee
        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.assign', $lead), [
                'employee_id' => $this->employee->id,
                'notes' => 'Assigning first representative',
            ]);

        $response->assertRedirect(route('admin.leads.show', $lead));
        
        // 3. Verify assignment
        $lead = $lead->fresh();
        $this->assertEquals($this->employee->id, $lead->assigned_employee_id);
        $this->assertEquals($this->admin->id, $lead->assigned_by);
        $this->assertNotNull($lead->assigned_at);

        // Verify assignment history preserved
        $this->assertDatabaseHas('lead_assignments', [
            'lead_id' => $lead->id,
            'employee_id' => $this->employee->id,
            'assigned_by' => $this->admin->id,
            'notes' => 'Assigning first representative',
        ]);

        // Verify Activity Log for assignment
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'assigned',
            'description' => "Lead assigned to {$this->employee->name}",
        ]);

        // 4. Create Visit
        $visitDate = now()->addDays(2)->format('Y-m-d H:i:s');
        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.visits.store', $lead), [
                'employee_id' => $this->employee->id,
                'visit_date' => $visitDate,
                'purpose' => 'Address verification and documents collect',
                'status' => 'scheduled',
                'notes' => 'Please ask for bank statement.',
            ]);

        $response->assertRedirect(route('admin.leads.show', $lead));

        // Verify Lead status automatically changed to visit_pending
        $lead = $lead->fresh();
        $this->assertSame('visit_pending', $lead->status);
        
        // Verify Visit history
        $visit = Visit::where('lead_id', $lead->id)->first();
        $this->assertNotNull($visit);
        $this->assertSame('scheduled', $visit->status);
        
        // Verify Visit created activity is logged
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'visit_created',
        ]);

        // 5. Complete Visit
        $response = $this->actingAs($this->admin)
            ->put(route('admin.leads.visits.update', [$lead, $visit]), [
                'employee_id' => $this->employee->id,
                'visit_date' => $visitDate,
                'purpose' => 'Address verification and documents collect',
                'status' => 'completed',
                'outcome' => 'Customer met. Docs collected.',
                'next_action' => 'Submit file to team.',
                'notes' => 'Met in person.',
            ]);

        $response->assertRedirect(route('admin.leads.show', $lead));

        // Verify status automatically updated to visit_completed
        $lead = $lead->fresh();
        $this->assertSame('visit_completed', $lead->status);

        // Verify visit status is completed
        $this->assertSame('completed', $visit->fresh()->status);

        // Verify visit completed activity logged
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'visit_completed',
        ]);

        // 6. Change Lead status manually to documentation_pending
        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.status', $lead), [
                'status' => 'documentation_pending',
            ]);

        $response->assertRedirect(route('admin.leads.show', $lead));
        $this->assertSame('documentation_pending', $lead->fresh()->status);

        // Verify status_changed activity logged
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'status_changed',
            'description' => "Status changed from 'Visit_completed' to 'Documentation_pending'",
        ]);

        // 7. Reassign Lead
        $anotherEmployee = User::factory()->create();
        $anotherEmployee->assignRole('Employee');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leads.assign', $lead), [
                'employee_id' => $anotherEmployee->id,
                'notes' => 'Changing representative due to load',
            ]);

        // 8. Verify assignment history is preserved
        $this->assertDatabaseHas('lead_assignments', [
            'lead_id' => $lead->id,
            'employee_id' => $this->employee->id,
            'notes' => 'Assigning first representative',
        ]);
        $this->assertDatabaseHas('lead_assignments', [
            'lead_id' => $lead->id,
            'employee_id' => $anotherEmployee->id,
            'notes' => 'Changing representative due to load',
        ]);

        // Verify Activity Timeline has Reassigned activity
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'reassigned',
            'description' => "Lead reassigned to {$anotherEmployee->name}",
        ]);
    }

    public function test_unauthorized_user_access(): void
    {
        $lead = Lead::create($this->validPayload());

        // Test Assign action restricted
        $response = $this->actingAs($this->unauthorizedUser)
            ->post(route('admin.leads.assign', $lead), [
                'employee_id' => $this->employee->id,
            ]);
        $response->assertStatus(403);

        // Test Create Visit action restricted
        $response = $this->actingAs($this->unauthorizedUser)
            ->post(route('admin.leads.visits.store', $lead), [
                'employee_id' => $this->employee->id,
                'visit_date' => now()->format('Y-m-d H:i:s'),
                'purpose' => 'Some purpose',
                'status' => 'scheduled',
            ]);
        $response->assertStatus(403);
    }
}
