<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Only Admin may view/manage the list of users and their direct permissions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function managePermissions(User $user, User $target): bool
    {
        return $user->hasRole('Admin');
    }
}
