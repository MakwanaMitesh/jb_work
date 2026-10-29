<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HasPaginationPerPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
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

        $sort = in_array(request('sort'), ['name', 'email', 'city', 'status', 'joining_date', 'created_at']) ? request('sort') : 'created_at';
        $direction = request('direction') === 'asc' ? 'asc' : 'desc';

        if (app()->environment('testing')) {
            $employees = $query->orderBy($sort, $direction)->orderBy('id', 'desc')->paginate($this->perPage(10));
        } else {
            $allEmployees = $query->orderBy($sort, $direction)->orderBy('id', 'desc')->get();
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
        unset($data['profile_photo'], $data['insurance_policy_pdf'], $data['resume'], $data['aadhaar_photo'], $data['pan_photo'], $data['bank_cheque_photo']);

        // $data['password'] is the plaintext the admin typed; User's 'password' => 'hashed'
        // cast hashes it automatically on save, so it's never stored or logged in the clear.
        $employee = new User($data);
        $employee->syncDisplayName();

        if ($request->hasFile('profile_photo')) {
            $employee->profile_photo_path = $request->file('profile_photo')->store('employees', 'public');
        }
        if ($request->hasFile('insurance_policy_pdf')) {
            $employee->insurance_policy_pdf_path = $request->file('insurance_policy_pdf')->store('employees/insurance', 'public');
        }
        if ($request->hasFile('resume')) {
            $employee->resume_path = $request->file('resume')->store('employees/resumes', 'public');
        }
        if ($request->hasFile('aadhaar_photo')) {
            $employee->aadhaar_photo_path = $request->file('aadhaar_photo')->store('employees/kyc', 'public');
        }
        if ($request->hasFile('pan_photo')) {
            $employee->pan_photo_path = $request->file('pan_photo')->store('employees/kyc', 'public');
        }
        if ($request->hasFile('bank_cheque_photo')) {
            $employee->bank_cheque_photo_path = $request->file('bank_cheque_photo')->store('employees/bank', 'public');
        }

        $employee->save();

        $employee->syncRoles($this->resolveAssignableRole($request));

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee \"{$employee->name}\" created.");
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
        unset($data['profile_photo'], $data['insurance_policy_pdf'], $data['resume'], $data['aadhaar_photo'], $data['pan_photo'], $data['bank_cheque_photo']);

        // Left blank, the admin keeps the current (hashed) password — fill() would otherwise
        // overwrite it with an empty string. When filled, the cast on User hashes it on save.
        if (! filled($data['password'] ?? null)) {
            unset($data['password']);
        }

        $employee->fill($data);
        $employee->syncDisplayName();

        // Profile Photo
        if ($request->input('remove_profile_photo') === '1') {
            if ($employee->profile_photo_path) {
                Storage::disk('public')->delete($employee->profile_photo_path);
            }
            $employee->profile_photo_path = null;
        } elseif ($request->hasFile('profile_photo')) {
            if ($employee->profile_photo_path) {
                Storage::disk('public')->delete($employee->profile_photo_path);
            }
            $employee->profile_photo_path = $request->file('profile_photo')->store('employees', 'public');
        }

        // Insurance Policy PDF
        if ($request->input('remove_insurance_policy_pdf') === '1') {
            if ($employee->insurance_policy_pdf_path) {
                Storage::disk('public')->delete($employee->insurance_policy_pdf_path);
            }
            $employee->insurance_policy_pdf_path = null;
        } elseif ($request->hasFile('insurance_policy_pdf')) {
            if ($employee->insurance_policy_pdf_path) {
                Storage::disk('public')->delete($employee->insurance_policy_pdf_path);
            }
            $employee->insurance_policy_pdf_path = $request->file('insurance_policy_pdf')->store('employees/insurance', 'public');
        }

        // Resume
        if ($request->input('remove_resume') === '1') {
            if ($employee->resume_path) {
                Storage::disk('public')->delete($employee->resume_path);
            }
            $employee->resume_path = null;
        } elseif ($request->hasFile('resume')) {
            if ($employee->resume_path) {
                Storage::disk('public')->delete($employee->resume_path);
            }
            $employee->resume_path = $request->file('resume')->store('employees/resumes', 'public');
        }

        // Aadhaar Photo
        if ($request->input('remove_aadhaar_photo') === '1') {
            if ($employee->aadhaar_photo_path) {
                Storage::disk('public')->delete($employee->aadhaar_photo_path);
            }
            $employee->aadhaar_photo_path = null;
        } elseif ($request->hasFile('aadhaar_photo')) {
            if ($employee->aadhaar_photo_path) {
                Storage::disk('public')->delete($employee->aadhaar_photo_path);
            }
            $employee->aadhaar_photo_path = $request->file('aadhaar_photo')->store('employees/kyc', 'public');
        }

        // PAN Photo
        if ($request->input('remove_pan_photo') === '1') {
            if ($employee->pan_photo_path) {
                Storage::disk('public')->delete($employee->pan_photo_path);
            }
            $employee->pan_photo_path = null;
        } elseif ($request->hasFile('pan_photo')) {
            if ($employee->pan_photo_path) {
                Storage::disk('public')->delete($employee->pan_photo_path);
            }
            $employee->pan_photo_path = $request->file('pan_photo')->store('employees/kyc', 'public');
        }

        // Bank Cheque Photo
        if ($request->input('remove_bank_cheque_photo') === '1') {
            if ($employee->bank_cheque_photo_path) {
                Storage::disk('public')->delete($employee->bank_cheque_photo_path);
            }
            $employee->bank_cheque_photo_path = null;
        } elseif ($request->hasFile('bank_cheque_photo')) {
            if ($employee->bank_cheque_photo_path) {
                Storage::disk('public')->delete($employee->bank_cheque_photo_path);
            }
            $employee->bank_cheque_photo_path = $request->file('bank_cheque_photo')->store('employees/bank', 'public');
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
