<?php

namespace Tests\Feature;

use App\Models\AssessmentYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentYearTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['status' => 'active']);
        $role = \Spatie\Permission\Models\Role::create(['name' => 'Admin']);
        $this->admin->assignRole($role);
    }

    public function test_admin_can_create_assessment_year(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.assessment-years.store'), [
            'name' => 'A.Y. 2027-28',
            'code' => '2027-28',
            'sort_order' => 10,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.assessment-years.index'));
        $this->assertDatabaseHas('assessment_years', [
            'name' => 'A.Y. 2027-28',
            'code' => '2027-28',
        ]);
    }

    public function test_admin_can_update_assessment_year(): void
    {
        $ay = AssessmentYear::create([
            'name' => 'A.Y. 2028-29',
            'code' => '2028-29',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.assessment-years.update', $ay), [
            'name' => 'A.Y. 2028-29 Updated',
            'code' => '2028-29-U',
            'sort_order' => 2,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.assessment-years.index'));
        $this->assertDatabaseHas('assessment_years', [
            'id' => $ay->id,
            'name' => 'A.Y. 2028-29 Updated',
        ]);
    }

    public function test_admin_can_toggle_assessment_year_status(): void
    {
        $ay = AssessmentYear::create([
            'name' => 'A.Y. 2029-30',
            'code' => '2029-30',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.assessment-years.toggle-status', $ay));

        $response->assertRedirect();
        $this->assertSame('inactive', $ay->fresh()->status);
    }
}
