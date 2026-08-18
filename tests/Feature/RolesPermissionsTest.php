<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    public function test_admin_role_has_every_permission(): void
    {
        $admin = Role::where('name', 'Admin')->firstOrFail();

        $this->assertSame(Permission::count(), $admin->permissions()->count());
    }

    public function test_initial_roles_exist(): void
    {
        $this->assertSame(['Admin', 'Agent', 'Employee'], Role::orderBy('name')->pluck('name')->all());
    }

    public function test_employee_starts_with_no_permissions(): void
    {
        $employee = Role::where('name', 'Employee')->firstOrFail();

        $this->assertSame(0, $employee->permissions()->count());
    }

    public function test_admin_user_can_access_role_management(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin)->get(route('admin.roles.index'))->assertOk();
    }

    public function test_non_admin_user_cannot_access_role_management(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $this->actingAs($employee)->get(route('admin.roles.index'))->assertForbidden();
    }

    public function test_user_without_permission_is_denied_dashboard_access(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $this->actingAs($employee)->get('/dashboard')->assertForbidden();
    }

    public function test_role_permission_grants_dashboard_access(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        Role::where('name', 'Employee')->firstOrFail()
            ->givePermissionTo('dashboard.view');

        $this->actingAs($employee->fresh())->get('/dashboard')->assertOk();
    }

    public function test_user_specific_permission_grants_access_independent_of_role(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $employee->givePermissionTo('employees.view');

        $this->assertTrue($employee->fresh()->can('employees.view'));
        $this->assertFalse($employee->fresh()->can('employees.create'));
    }

    public function test_effective_access_combines_role_and_user_permissions(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        Role::where('name', 'Employee')->firstOrFail()->givePermissionTo('dashboard.view');
        $employee->givePermissionTo('employees.view');

        $names = $employee->fresh()->effectivePermissionNames();

        $this->assertTrue($names->contains('dashboard.view'));
        $this->assertTrue($names->contains('employees.view'));
    }

    public function test_deactivating_a_role_revokes_its_role_granted_permissions(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $role = Role::where('name', 'Employee')->firstOrFail();
        $role->givePermissionTo('dashboard.view');

        $this->assertTrue($employee->fresh()->can('dashboard.view'));

        $role->update(['is_active' => false]);

        $this->assertFalse($employee->fresh()->can('dashboard.view'));
    }

    public function test_deactivating_a_role_does_not_revoke_direct_user_permissions(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $role = Role::where('name', 'Employee')->firstOrFail();
        $role->givePermissionTo('dashboard.view');
        $employee->givePermissionTo('employees.view');

        $role->update(['is_active' => false]);

        $this->assertFalse($employee->fresh()->can('dashboard.view'));
        $this->assertTrue($employee->fresh()->can('employees.view'));
    }

    public function test_admin_role_cannot_be_deactivated(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $adminRole = Role::where('name', 'Admin')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.roles.toggle-status', $adminRole))
            ->assertForbidden();

        $this->assertTrue($adminRole->fresh()->is_active);
    }

    public function test_admin_can_deactivate_a_non_protected_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $employeeRole = Role::where('name', 'Employee')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.roles.toggle-status', $employeeRole))
            ->assertRedirect(route('admin.roles.index'));

        $this->assertFalse($employeeRole->fresh()->is_active);
    }

    public function test_admin_can_assign_permissions_to_a_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $role = Role::where('name', 'Employee')->firstOrFail();
        $permissionIds = Permission::whereIn('name', ['dashboard.view', 'tasks.view'])->pluck('id')->all();

        $this->actingAs($admin)->put(route('admin.roles.update', $role), [
            'name' => 'Employee',
            'description' => 'Updated',
            'permissions' => $permissionIds,
        ])->assertRedirect(route('admin.roles.index'));

        $this->assertSame(2, $role->fresh()->permissions()->count());
    }

    public function test_admin_can_set_user_specific_permissions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $employee = User::factory()->create();
        $employee->assignRole('Employee');

        $permissionId = Permission::where('name', 'documents.upload')->firstOrFail()->id;

        $this->actingAs($admin)->put(route('admin.users.permissions.update', $employee), [
            'permissions' => [$permissionId],
        ])->assertRedirect(route('admin.users.index'));

        $this->assertTrue($employee->fresh()->can('documents.upload'));
    }

    public function test_guest_cannot_access_role_or_user_permission_management(): void
    {
        $this->get(route('admin.roles.index'))->assertRedirect(route('login'));
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    public function test_agent_role_has_no_associated_login_users_by_default(): void
    {
        $agent = Role::where('name', 'Agent')->firstOrFail();

        $this->assertSame(0, $agent->users()->count());
    }
}
