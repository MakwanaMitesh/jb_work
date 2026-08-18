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
     * Admin receives every permission. Employee and Agent start with none —
     * the Admin assigns permissions to them afterwards via Role Management.
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

        Role::firstOrCreate(
            ['name' => 'Employee', 'guard_name' => 'web'],
            ['description' => 'Staff member with permissions assigned by Admin', 'is_active' => true],
        );

        Role::firstOrCreate(
            ['name' => 'Agent', 'guard_name' => 'web'],
            ['description' => 'External agent — no login access yet', 'is_active' => true],
        );

        $admin->syncPermissions(Permission::all());
    }
}
