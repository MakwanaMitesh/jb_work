<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\User;
use App\Models\Agent;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityManagementTest extends TestCase
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
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Austin',
            'status' => 'active',
        ], $overrides);
    }

    public function test_admin_can_create_a_city(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.cities.store'), $this->validPayload());

        $response->assertRedirect(route('admin.cities.index'));

        $this->assertDatabaseHas('cities', [
            'name' => 'Austin',
            'status' => 'active',
        ]);
    }

    public function test_duplicate_name_is_rejected(): void
    {
        City::create($this->validPayload(['name' => 'Austin']));

        $response = $this->actingAs($this->admin)
            ->post(route('admin.cities.store'), $this->validPayload(['name' => 'Austin']));

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_a_city(): void
    {
        $city = City::create($this->validPayload(['name' => 'Austin']));

        $response = $this->actingAs($this->admin)
            ->put(route('admin.cities.update', $city), $this->validPayload([
                'name' => 'Dallas',
            ]));

        $response->assertRedirect(route('admin.cities.index'));
        $this->assertSame('Dallas', $city->fresh()->name);
    }

    public function test_admin_can_delete_an_unassigned_city(): void
    {
        $city = City::create($this->validPayload());

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.cities.destroy', $city));

        $response->assertRedirect(route('admin.cities.index'));
        $this->assertDatabaseMissing('cities', ['id' => $city->id]);
    }

    public function test_admin_cannot_delete_assigned_city(): void
    {
        $city = City::create($this->validPayload());
        
        // Assign to employee
        $employee = User::factory()->create(['city_id' => $city->id]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.cities.destroy', $city));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('cities', ['id' => $city->id]);
    }
}
