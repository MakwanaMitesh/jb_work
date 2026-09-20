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

class PilotRenderCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_leads_index_renders_with_bootstrap_layout(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $city = City::create(['name' => 'Austin', 'status' => 'active']);
        $agent = Agent::create([
            'first_name' => 'Bob',
            'last_name' => 'Agent',
            'email' => 'bob.agent@example.com',
            'mobile_number' => '+919876543210',
            'city_id' => $city->id,
            'status' => 'active',
        ]);

        Lead::create([
            'name' => 'Render Probe Lead',
            'mobile_number' => '+919999999999',
            'city_id' => $city->id,
            'agent_id' => $agent->id,
            'status' => 'new',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.leads.index'));

        $response->assertOk();

        $html = $response->getContent();

        // Bootstrap is loaded, Tailwind build is not
        $this->assertStringContainsString('bootstrap@5.3.8/dist/css/bootstrap.min.css', $html);
        $this->assertStringContainsString('css/jb-tokens.css', $html);
        $this->assertStringContainsString('css/bootstrap-theme.css', $html);

        // Tokens must load before the theme that consumes them.
        $this->assertLessThan(
            strpos($html, 'css/bootstrap-theme.css'),
            strpos($html, 'css/jb-tokens.css'),
            'jb-tokens.css must be linked before bootstrap-theme.css'
        );

        // Bootstrap-native components are in use
        $this->assertStringContainsString('data-bs-toggle="dropdown"', $html);

        // Theme classes render
        $this->assertStringContainsString('jb-table', $html);
        $this->assertStringContainsString('jb-badge', $html);
        $this->assertStringContainsString('jb-sidebar', $html);

        // Real data still renders
        $this->assertStringContainsString('Render Probe Lead', $html);
        $this->assertStringContainsString('+919999999999', $html);

        // No leftover Tailwind utility soup on the migrated page
        $this->assertStringNotContainsString('dark:bg-slate-900', $html);
    }
}
