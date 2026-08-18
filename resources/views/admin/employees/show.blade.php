<x-admin-layout :title="$employee->name">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            @if ($employee->profilePhotoUrl())
                <img src="{{ $employee->profilePhotoUrl() }}" alt="" class="rounded-circle" width="56" height="56" style="object-fit: cover;">
            @else
                <span class="jb-avatar-circle" style="width:56px;height:56px;font-size:1.1rem;">{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
            @endif
            <div>
                <h1 class="h4 fw-bold mb-1">{{ $employee->name }}</h1>
                <p class="text-muted small mb-0">{{ $employee->email }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            @can('employees.edit')
                <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-outline-secondary">Edit</a>
            @endcan
            <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="jb-card p-4 h-100">
                <h6 class="fw-semibold mb-3">Basic Information</h6>
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">First Name</dt>
                    <dd class="col-7">{{ $employee->first_name ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Middle Name</dt>
                    <dd class="col-7">{{ $employee->middle_name ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Last Name</dt>
                    <dd class="col-7">{{ $employee->last_name ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Mobile</dt>
                    <dd class="col-7">{{ $employee->mobile_number ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">City</dt>
                    <dd class="col-7">{{ $employee->city ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Address</dt>
                    <dd class="col-7">{{ $employee->address ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Joining Date</dt>
                    <dd class="col-7">{{ $employee->joining_date?->format('d M Y') ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="jb-card p-4 h-100">
                <h6 class="fw-semibold mb-3">Account Status</h6>
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Status</dt>
                    <dd class="col-7">
                        @if ($employee->isActive())
                            <span class="badge rounded-pill bg-success-subtle text-success-emphasis">Active</span>
                        @else
                            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">Inactive</span>
                        @endif
                    </dd>
                    <dt class="col-5 text-muted fw-normal">Role</dt>
                    <dd class="col-7">
                        @forelse ($employee->roles as $role)
                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">{{ $role->name }}</span>
                            @if (! $role->is_active)
                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">role deactivated</span>
                            @endif
                        @empty
                            <span class="text-muted">No role assigned</span>
                        @endforelse
                    </dd>
                    <dt class="col-5 text-muted fw-normal">Account created</dt>
                    <dd class="col-7">{{ $employee->created_at->format('d M Y') }}</dd>
                    <dt class="col-5 text-muted fw-normal">Email verified</dt>
                    <dd class="col-7">{{ $employee->email_verified_at ? 'Yes' : 'Not yet (pending password setup)' }}</dd>
                </dl>

                @can('managePermissions', $employee)
                    <a href="{{ route('admin.users.permissions.edit', $employee) }}" class="btn btn-sm btn-outline-secondary mt-2">
                        Manage Direct Permissions
                    </a>
                @endcan
            </div>
        </div>

        <div class="col-12">
            <div class="jb-card p-4">
                <h6 class="fw-semibold mb-3">Effective Permissions</h6>
                <p class="text-muted small">Role permissions (from active roles) + permissions granted directly to this user.</p>

                @php $effective = $employee->effectivePermissionNames(); @endphp

                @if ($effective->isEmpty())
                    <p class="text-muted small mb-0">No permissions granted yet.</p>
                @else
                    <div class="d-flex flex-wrap gap-1">
                        @foreach ($effective as $permission)
                            <span class="badge rounded-pill text-bg-light text-dark border">{{ $permission }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-4">
            <div class="jb-card p-4 h-100">
                <h6 class="fw-semibold mb-2">Assigned / Pending Tasks</h6>
                <p class="text-muted small mb-0">Task Management module is not implemented yet.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="jb-card p-4 h-100">
                <h6 class="fw-semibold mb-2">Completed Tasks</h6>
                <p class="text-muted small mb-0">Task Management module is not implemented yet.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="jb-card p-4 h-100">
                <h6 class="fw-semibold mb-2">EOD Submissions</h6>
                <p class="text-muted small mb-0">EOD module is not implemented yet.</p>
            </div>
        </div>
    </div>
</x-admin-layout>
