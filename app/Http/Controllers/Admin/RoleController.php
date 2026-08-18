<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HasPaginationPerPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    use HasPaginationPerPage;

    /**
     * List roles with permission/user counts — search by name, filter by status.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::withCount(['permissions', 'users'])
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the role creation form.
     */
    public function create(): View
    {
        $this->authorize('create', Role::class);

        $permissionsByModule = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');

        return view('admin.roles.create', compact('permissionsByModule'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
            'description' => $request->validated('description'),
            'is_active' => true,
        ]);

        $role->syncPermissions($this->permissionIds($request));

        return redirect()->route('admin.roles.index')->with('success', "Role \"{$role->name}\" created successfully.");
    }

    /**
     * Show the role edit form (details + permission assignment together).
     */
    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        $permissionsByModule = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $rolePermissionIds = $role->permissions->pluck('id')->all();

        return view('admin.roles.edit', compact('role', 'permissionsByModule', 'rolePermissionIds'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        // Protected roles (Admin) keep their name regardless of payload —
        // renaming it could break role-based checks elsewhere in the system.
        $role->update([
            'name' => $role->isProtected() ? $role->name : $request->validated('name'),
            'description' => $request->validated('description'),
        ]);

        $role->syncPermissions($this->permissionIds($request));

        return redirect()->route('admin.roles.index')->with('success', "Role \"{$role->name}\" updated successfully.");
    }

    /**
     * Toggle a role's active status. Protected roles (Admin) cannot be deactivated.
     */
    public function toggleStatus(Role $role): RedirectResponse
    {
        $this->authorize('toggleStatus', $role);

        $role->update(['is_active' => ! $role->is_active]);

        $status = $role->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.roles.index')->with('success', "Role \"{$role->name}\" {$status}.");
    }

    /**
     * Permission IDs submitted from the form arrive as strings; Spatie's
     * syncPermissions() only resolves plain integers by ID (numeric strings
     * are treated as permission names), so cast them explicitly.
     *
     * @return array<int, int>
     */
    private function permissionIds(StoreRoleRequest|UpdateRoleRequest $request): array
    {
        return array_map('intval', $request->validated('permissions', []));
    }
}
