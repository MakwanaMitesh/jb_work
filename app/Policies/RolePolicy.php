<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    /**
     * Role & Permission management is an Admin-only capability.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasRole('Admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Protected roles (Admin) can never be deactivated — doing so could
     * lock every administrator out of the system.
     */
    public function toggleStatus(User $user, Role $role): bool
    {
        return $user->hasRole('Admin') && ! $role->isProtected();
    }

    public function assignPermissions(User $user, Role $role): bool
    {
        return $user->hasRole('Admin');
    }
}
