<?php
namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InspectionRemarksPreviewSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_endpoint_returns_generated_remarks(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole(Role::where('name', 'Admin')->firstOrFail());

        $response = $this->actingAs($admin)->postJson(route('admin.leads.inspection-remarks.preview'), [
            'name' => 'RAKESH KUMAR PATEL',
            'business_name' => 'PATEL TRADERS',
            'business_address' => 'SHOP NO 12, MG ROAD, AHMEDABAD',
            'business_activity' => 'Trading',
            'business_experience' => '8 Years',
        ]);

        $response->assertOk();
        $response->assertJsonFragment([]);
        $this->assertStringContainsString('PATEL TRADERS', $response->json('remarks'));
        $this->assertStringContainsString('RAKESH KUMAR PATEL', $response->json('remarks'));
    }

    public function test_preview_endpoint_omits_missing_fields_without_errors(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole(Role::where('name', 'Admin')->firstOrFail());

        $response = $this->actingAs($admin)->postJson(route('admin.leads.inspection-remarks.preview'), [
            'name' => 'Solo Name',
        ]);

        $response->assertOk();
        $remarks = $response->json('remarks');
        $this->assertStringNotContainsString('undefined', $remarks);
        $this->assertStringNotContainsString('null', $remarks);
        $this->assertStringNotContainsString('{{', $remarks);
    }
}
