<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $this->city = \App\Models\City::create(['name' => 'Austin', 'status' => 'active']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john.smith@example.com',
            'mobile_number' => '1234567890',
            'city_id' => $this->city->id,
            'address' => '500 Congress Ave',
            'status' => 'active',
        ], $overrides);
    }

    public function test_admin_can_create_an_agent(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.agents.store'), $this->validPayload());

        $response->assertRedirect(route('admin.agents.index'));

        $agent = Agent::where('email', 'john.smith@example.com')->first();
        $this->assertNotNull($agent);
        $this->assertSame('John Smith', $agent->name);
        $this->assertTrue($agent->isActive());
    }

    public function test_duplicate_email_is_rejected(): void
    {
        Agent::create($this->validPayload(['email' => 'john.smith@example.com']));

        $response = $this->actingAs($this->admin)
            ->post(route('admin.agents.store'), $this->validPayload());

        $response->assertSessionHasErrors('email');
    }

    public function test_required_fields_are_validated(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.agents.store'), []);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'status']);
    }

    public function test_user_without_create_permission_cannot_create_agent(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Employee'); // has no agent permissions by default

        $response = $this->actingAs($user)
            ->post(route('admin.agents.store'), $this->validPayload());

        $response->assertStatus(403);
    }

    public function test_admin_can_update_an_agent(): void
    {
        $agent = Agent::create($this->validPayload());

        $response = $this->actingAs($this->admin)
            ->put(route('admin.agents.update', $agent), $this->validPayload([
                'first_name' => 'Johnny',
                'last_name' => 'S.',
            ]));

        $response->assertRedirect(route('admin.agents.index'));
        $this->assertSame('Johnny S.', $agent->fresh()->name);
    }

    public function test_admin_can_deactivate_and_reactivate_an_agent(): void
    {
        $agent = Agent::create($this->validPayload(['status' => 'active']));

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.agents.toggle-status', $agent));

        $response->assertRedirect();
        $this->assertSame('inactive', $agent->fresh()->status);

        $response2 = $this->actingAs($this->admin)
            ->patch(route('admin.agents.toggle-status', $agent));

        $response2->assertRedirect();
        $this->assertSame('active', $agent->fresh()->status);
    }

    public function test_admin_can_delete_an_agent_and_it_is_soft_deleted(): void
    {
        $agent = Agent::create($this->validPayload());

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.agents.destroy', $agent));

        $response->assertRedirect(route('admin.agents.index'));
        $this->assertSoftDeleted('agents', ['id' => $agent->id]);
    }

    public function test_search_filters_agents_by_name_or_email(): void
    {
        Agent::create($this->validPayload(['first_name' => 'Alice', 'email' => 'alice@example.com']));
        Agent::create($this->validPayload(['first_name' => 'Bob', 'email' => 'bob@example.com']));

        $response = $this->actingAs($this->admin)
            ->get(route('admin.agents.index', ['search' => 'Alice']));

        $response->assertViewHas('agents');
        $agents = $response->viewData('agents');
        $this->assertCount(1, $agents);
        $this->assertSame('Alice', $agents->first()->first_name);
    }

    public function test_filter_by_status(): void
    {
        Agent::create($this->validPayload(['first_name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']));
        Agent::create($this->validPayload(['first_name' => 'Bob', 'email' => 'bob@example.com', 'status' => 'inactive']));

        $response = $this->actingAs($this->admin)
            ->get(route('admin.agents.index', ['status' => 'inactive']));

        $agents = $response->viewData('agents');
        $this->assertCount(1, $agents);
        $this->assertSame('Bob', $agents->first()->first_name);
    }

    public function test_filter_by_city(): void
    {
        $cityAustin = $this->city;
        $cityDallas = \App\Models\City::create(['name' => 'Dallas', 'status' => 'active']);

        Agent::create($this->validPayload(['first_name' => 'Alice', 'email' => 'alice@example.com', 'city_id' => $cityAustin->id]));
        Agent::create($this->validPayload(['first_name' => 'Bob', 'email' => 'bob@example.com', 'city_id' => $cityDallas->id]));

        $response = $this->actingAs($this->admin)
            ->get(route('admin.agents.index', ['city_id' => $cityDallas->id]));

        $agents = $response->viewData('agents');
        $this->assertCount(1, $agents);
        $this->assertSame('Bob', $agents->first()->first_name);
    }
}
