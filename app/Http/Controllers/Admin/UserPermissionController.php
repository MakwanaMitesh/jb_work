<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HasPaginationPerPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserPermissionsRequest;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserPermissionController extends Controller
{
    use HasPaginationPerPage;

    /**
     * List login-enabled users with their assigned role, for permission management.
     *
     * Note: this is a minimal listing for permission assignment only — full
     * Employee Management (profile fields, onboarding, etc.) is a separate,
     * later module.
     */
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('roles');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = request('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        $users = $query->orderBy('name')->paginate($this->perPage())->withQueryString();
        $roles = \App\Models\Role::orderBy('name')->pluck('name');

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the direct-permission assignment form for a single user.
     */
    public function edit(User $user): View
    {
        $this->authorize('managePermissions', $user);

        $permissionsByModule = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $userPermissionIds = $user->permissions->pluck('id')->all();

        return view('admin.users.edit', compact('user', 'permissionsByModule', 'userPermissionIds'));
    }

    public function update(UpdateUserPermissionsRequest $request, User $user): RedirectResponse
    {
        // Numeric strings from the form must be cast to int — Spatie's
        // syncPermissions() only resolves plain integers by ID.
        $permissionIds = array_map('intval', $request->validated('permissions', []));

        $user->syncPermissions($permissionIds);

        return redirect()->route('admin.users.index')->with('success', "Permissions updated for {$user->name}.");
    }
}
