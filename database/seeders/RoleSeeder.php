<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Initial roles: Admin, Employee, Agent.
     * Admin receives every permission. Employee and Agent otherwise start with
     * none — the Admin assigns further permissions to them via Role Management.
     *
     * Employee is the exception: it always gets dashboard.view, since that's
     * the page every logged-in user lands on. Without it, a freshly created
     * Employee would be locked out with a 403 immediately after logging in,
     * before an Admin ever gets a chance to grant them anything else.
     *
     * Note: Agent is not a login-enabled role at this stage (no auth flow
     * exists for it), it is only kept ready for future use.
     */
    public function run(): void
    {
        $admin = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['description' => 'Full system access', 'is_active' => true],
        );

        $employee = Role::firstOrCreate(
            ['name' => 'Employee', 'guard_name' => 'web'],
            ['description' => 'Staff member with permissions assigned by Admin', 'is_active' => true],
        );

        Role::firstOrCreate(
            ['name' => 'Agent', 'guard_name' => 'web'],
            ['description' => 'External agent — no login access yet', 'is_active' => true],
        );

        $admin->syncPermissions(Permission::all());

        if ($dashboardView = Permission::where('name', 'dashboard.view')->first()) {
            $employee->givePermissionTo($dashboardView);
        }
    }
}
