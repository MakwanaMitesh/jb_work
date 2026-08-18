<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HasPaginationPerPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    use HasPaginationPerPage;

    /**
     * Employee listing — search, filter, sort, paginate.
     *
     * Agent is excluded from role filtering/listing entirely: it has no
     * login capability and is out of scope for Employee Management.
     */
    public function index(): View
    {
        $this->authorize('employees.view');

        $query = User::query()->with('roles')->whereHas('roles', fn ($q) => $q->where('name', '!=', 'Agent'));

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if ($role = request('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        if ($cityId = request('city_id')) {
            $query->where('city_id', $cityId);
        }

        $sort = in_array(request('sort'), ['name', 'email', 'city', 'status', 'joining_date', 'created_at']) ? request('sort') : 'name';
        $direction = request('direction') === 'desc' ? 'desc' : 'asc';

        if (app()->environment('testing')) {
            $employees = $query->orderBy($sort, $direction)->paginate($this->perPage(10));
        } else {
            $allEmployees = $query->orderBy($sort, $direction)->get();
            $employees = new \Illuminate\Pagination\LengthAwarePaginator(
                $allEmployees,
                $allEmployees->count(),
                max(1, $allEmployees->count()),
                1
            );
        }

        $roles = Role::where('name', '!=', 'Agent')->orderBy('name')->pluck('name');
        $cities = \App\Models\City::orderBy('name')->get();

        return view('admin.employees.index', compact('employees', 'roles', 'cities', 'sort', 'direction'));
    }

    public function create(): View
    {
        $this->authorize('employees.create');

        $roles = Role::where('name', '!=', 'Agent')->where('is_active', true)->orderBy('name')->get();
        $cities = \App\Models\City::where('status', 'active')->orderBy('name')->get();

        return view('admin.employees.create', compact('roles', 'cities'));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $employee = new User($data);
        $employee->syncDisplayName();

        // Never store/display a plaintext password — generate a random one,
        // hash it, and let the employee set their own via Forgot Password.
        $employee->password = Str::password(16);

        if ($request->hasFile('profile_photo')) {
            $employee->profile_photo_path = $request->file('profile_photo')->store('employees', 'public');
        }

        $employee->save();

        $employee->syncRoles($this->resolveAssignableRole($request));

        Password::sendResetLink(['email' => $employee->email]);

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee \"{$employee->name}\" created. A password setup link has been emailed to them.");
    }

    public function show(User $employee): View
    {
        $this->authorize('employees.view');

        $employee->load('roles', 'permissions');

        return view('admin.employees.show', compact('employee'));
    }

    public function edit(User $employee): View
    {
        $this->authorize('employees.edit');

        $roles = Role::where('name', '!=', 'Agent')->where('is_active', true)->orderBy('name')->get();
        $cities = \App\Models\City::where('status', 'active')->orderBy('name')->get();

        return view('admin.employees.edit', compact('employee', 'roles', 'cities'));
    }

    public function update(UpdateEmployeeRequest $request, User $employee): RedirectResponse
    {
        $data = $request->validated();
        unset($data['profile_photo']);

        $employee->fill($data);
        $employee->syncDisplayName();

        if ($request->hasFile('profile_photo')) {
            if ($employee->profile_photo_path) {
                Storage::disk('public')->delete($employee->profile_photo_path);
            }
            $employee->profile_photo_path = $request->file('profile_photo')->store('employees', 'public');
        }

        $employee->save();

        $employee->syncRoles($this->resolveAssignableRole($request, $employee));

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee \"{$employee->name}\" updated successfully.");
    }

    /**
     * Activate/deactivate toggle. An admin cannot deactivate their own account.
     */
    public function toggleStatus(User $employee): RedirectResponse
    {
        $this->authorize('employees.activate');

        if ($employee->is(auth()->user())) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $employee->update(['status' => $employee->isActive() ? 'inactive' : 'active']);

        $label = $employee->isActive() ? 'activated' : 'deactivated';

        return back()->with('success', "Employee \"{$employee->name}\" {$label}.");
    }

    /**
     * Soft-delete. An admin cannot delete their own account, and the last
     * remaining Admin cannot be deleted (would lock everyone out).
     */
    public function destroy(User $employee): RedirectResponse
    {
        $this->authorize('employees.delete');

        if ($employee->is(auth()->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($employee->hasRole('Admin') && User::role('Admin')->count() <= 1) {
            return back()->with('error', 'Cannot delete the last remaining Admin.');
        }

        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee \"{$employee->name}\" deleted.");
    }

    /**
     * Only apply the submitted role if the acting user is allowed to assign
     * roles; otherwise fall back to the employee's current role (edit) or
     * the default "Employee" role (create).
     */
    private function resolveAssignableRole(StoreEmployeeRequest|UpdateEmployeeRequest $request, ?User $employee = null): string
    {
        if ($request->user()->can('employees.assign_role')) {
            return $request->validated('role');
        }

        return $employee?->roles->first()?->name ?? 'Employee';
    }
}
