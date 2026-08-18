<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
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

        $this->city = \App\Models\City::create(['name' => 'Mumbai', 'status' => 'active']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Jane',
            'middle_name' => '',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'mobile_number' => '9876543210',
            'city_id' => $this->city->id,
            'address' => '123 Street',
            'joining_date' => '2026-01-01',
            'role' => 'Employee',
            'status' => 'active',
        ], $overrides);
    }

    public function test_admin_can_create_an_employee_which_also_creates_a_login_account(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.employees.store'), $this->validPayload());

        $response->assertRedirect(route('admin.employees.index'));

        $employee = User::where('email', 'jane.doe@example.com')->first();
        $this->assertNotNull($employee);
        $this->assertSame('Jane Doe', $employee->name);
        $this->assertTrue($employee->hasRole('Employee'));
        $this->assertTrue($employee->isActive());

        // A password reset link is sent instead of ever exposing a plaintext password.
        Notification::assertSentTo($employee, ResetPassword::class);
    }

    public function test_employee_creation_never_stores_a_guessable_or_visible_password(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('admin.employees.store'), $this->validPayload());

        $employee = User::where('email', 'jane.doe@example.com')->first();

        // The stored value is a bcrypt hash, never plaintext.
        $this->assertStringStartsWith('$2y$', $employee->password);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'jane.doe@example.com']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.employees.store'), $this->validPayload());

        $response->assertSessionHasErrors('email');
    }

    public function test_required_fields_are_validated(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.employees.store'), []);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'role', 'status']);
    }

    public function test_user_without_create_permission_cannot_create_employee(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $response = $this->actingAs($employee)
            ->post(route('admin.employees.store'), $this->validPayload());

        $response->assertForbidden();
        $this->assertNull(User::where('email', 'jane.doe@example.com')->first());
    }

    public function test_guest_is_redirected_from_employee_routes(): void
    {
        $this->get(route('admin.employees.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_update_an_employee(): void
    {
        Notification::fake();

        $employee = User::factory()->create(['first_name' => 'Old', 'last_name' => 'Name']);
        $employee->assignRole('Employee');

        $response = $this->actingAs($this->admin)->put(route('admin.employees.update', $employee), $this->validPayload([
            'email' => $employee->email,
            'first_name' => 'New',
        ]));

        $response->assertRedirect(route('admin.employees.index'));
        $this->assertSame('New', $employee->fresh()->first_name);
        $this->assertSame('New Doe', $employee->fresh()->name);
    }

    public function test_user_without_assign_role_permission_cannot_change_an_employees_role(): void
    {
        // Grant a limited user only employees.edit, not employees.assign_role.
        $limited = User::factory()->create();
        $limited->assignRole('Employee');
        $limited->givePermissionTo('employees.edit');

        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $this->actingAs($limited)->put(route('admin.employees.update', $employee), $this->validPayload([
            'email' => $employee->email,
            'role' => 'Admin', // attempted privilege escalation
        ]));

        $this->assertTrue($employee->fresh()->hasRole('Employee'));
        $this->assertFalse($employee->fresh()->hasRole('Admin'));
    }

    public function test_admin_with_assign_role_permission_can_change_an_employees_role(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $this->actingAs($this->admin)->put(route('admin.employees.update', $employee), $this->validPayload([
            'email' => $employee->email,
            'role' => 'Admin',
        ]));

        $this->assertTrue($employee->fresh()->hasRole('Admin'));
        $this->assertFalse($employee->fresh()->hasRole('Employee'));
    }

    public function test_admin_can_deactivate_and_reactivate_an_employee(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $this->actingAs($this->admin)->patch(route('admin.employees.toggle-status', $employee))
            ->assertRedirect();
        $this->assertFalse($employee->fresh()->isActive());

        $this->actingAs($this->admin)->patch(route('admin.employees.toggle-status', $employee))
            ->assertRedirect();
        $this->assertTrue($employee->fresh()->isActive());
    }

    public function test_admin_cannot_deactivate_their_own_account(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.employees.toggle-status', $this->admin))
            ->assertRedirect();

        $this->assertTrue($this->admin->fresh()->isActive());
    }

    public function test_admin_can_delete_an_employee_and_it_is_soft_deleted(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $this->actingAs($this->admin)->delete(route('admin.employees.destroy', $employee))
            ->assertRedirect(route('admin.employees.index'));

        $this->assertSoftDeleted($employee);
        $this->assertFalse(User::whereKey($employee->id)->exists());
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $this->actingAs($this->admin)->delete(route('admin.employees.destroy', $this->admin));

        $this->assertNotSoftDeleted($this->admin);
    }

    public function test_last_remaining_admin_cannot_be_deleted(): void
    {
        // $this->admin is the only Admin. A different actor (non-Admin, but
        // granted employees.delete) attempts to delete them — must be blocked.
        $actor = User::factory()->create();
        $actor->assignRole('Employee');
        $actor->givePermissionTo('employees.delete');

        $this->actingAs($actor)->delete(route('admin.employees.destroy', $this->admin));

        $this->assertNotSoftDeleted($this->admin);
    }

    public function test_a_non_last_admin_can_be_deleted(): void
    {
        $secondAdmin = User::factory()->create();
        $secondAdmin->assignRole('Admin');

        // Two admins exist now — deleting one should succeed.
        $this->actingAs($this->admin)->delete(route('admin.employees.destroy', $secondAdmin))
            ->assertRedirect(route('admin.employees.index'));

        $this->assertSoftDeleted($secondAdmin);
    }

    public function test_employee_can_log_in_after_setting_password_via_forgot_password(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('admin.employees.store'), $this->validPayload());
        $employee = User::where('email', 'jane.doe@example.com')->first();

        // Log out the acting Admin — the reset-password endpoint is a
        // guest-only route and would otherwise redirect an authenticated
        // session away instead of processing the reset.
        $this->post(route('logout'));

        Notification::assertSentTo($employee, ResetPassword::class, function ($notification) use ($employee) {
            $token = $notification->token;

            $response = $this->post(route('password.store'), [
                'token' => $token,
                'email' => $employee->email,
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ]);

            $response->assertRedirect(route('login'));

            return true;
        });

        $this->post('/login', [
            'email' => 'jane.doe@example.com',
            'password' => 'new-secure-password',
        ]);

        $this->assertAuthenticated();
    }

    public function test_search_filters_employees_by_name_or_email(): void
    {
        User::factory()->create(['name' => 'Findable Person', 'email' => 'findable@example.com'])->assignRole('Employee');
        User::factory()->create(['name' => 'Other Person', 'email' => 'other@example.com'])->assignRole('Employee');

        $response = $this->actingAs($this->admin)->get(route('admin.employees.index', ['search' => 'Findable']));

        $response->assertOk();
        $response->assertSee('Findable Person');
        $response->assertDontSee('Other Person');
    }

    public function test_filter_by_status(): void
    {
        $active = User::factory()->create(['name' => 'Active Person', 'status' => 'active']);
        $active->assignRole('Employee');
        $inactive = User::factory()->create(['name' => 'Inactive Person', 'status' => 'inactive']);
        $inactive->assignRole('Employee');

        $response = $this->actingAs($this->admin)->get(route('admin.employees.index', ['status' => 'inactive']));

        $response->assertSee('Inactive Person');
        $response->assertDontSee('Active Person');
    }

    public function test_filter_by_role(): void
    {
        $adminUser = User::factory()->create(['name' => 'Another Admin']);
        $adminUser->assignRole('Admin');
        $employeeUser = User::factory()->create(['name' => 'Plain Employee']);
        $employeeUser->assignRole('Employee');

        $response = $this->actingAs($this->admin)->get(route('admin.employees.index', ['role' => 'Employee']));

        $response->assertSee('Plain Employee');
        $response->assertDontSee('Another Admin');
    }

    public function test_filter_by_city(): void
    {
        $cityMumbai = $this->city;
        $cityDelhi = \App\Models\City::create(['name' => 'Delhi', 'status' => 'active']);

        $mumbai = User::factory()->create(['name' => 'Mumbai Person', 'city_id' => $cityMumbai->id]);
        $mumbai->assignRole('Employee');
        $delhi = User::factory()->create(['name' => 'Delhi Person', 'city_id' => $cityDelhi->id]);
        $delhi->assignRole('Employee');

        $response = $this->actingAs($this->admin)->get(route('admin.employees.index', ['city_id' => $cityMumbai->id]));

        $response->assertSee('Mumbai Person');
        $response->assertDontSee('Delhi Person');
    }

    public function test_pagination_limits_results_per_page(): void
    {
        User::factory()->count(20)->create()->each(fn ($u) => $u->assignRole('Employee'));

        $response = $this->actingAs($this->admin)->get(route('admin.employees.index'));

        $response->assertOk();
        $response->assertViewHas('employees', fn ($paginator) => $paginator->count() === 10 && $paginator->total() === 21);
    }

    public function test_employee_detail_page_shows_effective_permissions(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');
        $employee->givePermissionTo('documents.upload');

        $response = $this->actingAs($this->admin)->get(route('admin.employees.show', $employee));

        $response->assertOk();
        $response->assertSee('documents.upload');
    }

    public function test_agent_role_is_excluded_from_employee_listing_and_role_filter(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.employees.index'));

        $response->assertOk();
        $response->assertDontSee('>Agent<', false);
    }
}
