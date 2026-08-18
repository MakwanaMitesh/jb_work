<x-admin-layout :title="$employee->name">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            @if ($employee->profilePhotoUrl())
                <img src="{{ $employee->profilePhotoUrl() }}" alt="" class="w-14 h-14 rounded-full object-cover shrink-0">
            @else
                <span class="w-14 h-14 rounded-full bg-gradient-to-tr from-primary-500 to-orange-600 flex items-center justify-center text-white text-lg font-bold uppercase shrink-0">
                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                </span>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-slate-950 dark:text-white leading-tight">{{ $employee->name }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $employee->email }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @can('employees.edit')
                <a href="{{ route('admin.employees.edit', $employee) }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold rounded-lg shadow-sm transition no-underline">
                    Edit
                </a>
            @endcan
            <a href="{{ route('admin.employees.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm no-underline">
                Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Basic Information -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Basic Information</h3>
            <dl class="grid grid-cols-3 gap-y-3 text-sm">
                <dt class="text-slate-500 dark:text-slate-400 font-medium">First Name</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $employee->first_name ?: '—' }}</dd>
                <dt class="text-slate-500 dark:text-slate-400 font-medium">Middle Name</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $employee->middle_name ?: '—' }}</dd>
                <dt class="text-slate-500 dark:text-slate-400 font-medium">Last Name</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $employee->last_name ?: '—' }}</dd>
                <dt class="text-slate-500 dark:text-slate-400 font-medium">Mobile</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $employee->mobile_number ?: '—' }}</dd>
                <dt class="text-slate-500 dark:text-slate-400 font-medium">City</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $employee->city ?: '—' }}</dd>
                <dt class="text-slate-500 dark:text-slate-400 font-medium">Address</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $employee->address ?: '—' }}</dd>
                <dt class="text-slate-500 dark:text-slate-400 font-medium">Joining Date</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $employee->joining_date?->format('d M Y') ?? '—' }}</dd>
            </dl>
        </div>

        <!-- Account Status -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Account Status</h3>
            <dl class="grid grid-cols-3 gap-y-3 text-sm mb-4">
                <dt class="text-slate-500 dark:text-slate-400 font-medium">Status</dt>
                <dd class="col-span-2">
                    @if ($employee->isActive())
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-500 border border-emerald-200/30 dark:border-emerald-900/30">Active</span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">Inactive</span>
                    @endif
                </dd>
                <dt class="text-slate-500 dark:text-slate-400 font-medium">Role</dt>
                <dd class="col-span-2">
                    @forelse ($employee->roles as $role)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-50 dark:bg-primary-950/20 text-primary-700 dark:text-primary-500 border border-primary-200/50 dark:border-primary-900/30">{{ $role->name }}</span>
                        @if (! $role->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 dark:bg-red-950/20 text-red-700 dark:text-red-500 border border-red-200/50 dark:border-red-900/30 mt-1">role deactivated</span>
                        @endif
                    @empty
                        <span class="text-slate-400 font-semibold">No role assigned</span>
                    @endforelse
                </dd>
                <dt class="text-slate-500 dark:text-slate-400 font-medium">Account created</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $employee->created_at->format('d M Y') }}</dd>
                <dt class="text-slate-500 dark:text-slate-400 font-medium">Email verified</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $employee->email_verified_at ? 'Yes' : 'Not yet (pending password setup)' }}</dd>
            </dl>

            @can('managePermissions', $employee)
                <a href="{{ route('admin.users.permissions.edit', $employee) }}" class="inline-flex items-center justify-center px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm no-underline">
                    Manage Direct Permissions
                </a>
            @endcan
        </div>
    </div>

    <!-- Effective Permissions -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Effective Permissions</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Role permissions (from active roles) + permissions granted directly to this user.</p>

        @php $effective = $employee->effectivePermissionNames(); @endphp

        @if ($effective->isEmpty())
            <p class="text-sm text-slate-400">No permissions granted yet.</p>
        @else
            <div class="flex flex-wrap gap-1.5">
                @foreach ($effective as $permission)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">{{ $permission }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2">Assigned / Pending Tasks</h3>
            <p class="text-xs text-slate-400 mt-1">Task Management module is not implemented yet.</p>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2">Completed Tasks</h3>
            <p class="text-xs text-slate-400 mt-1">Task Management module is not implemented yet.</p>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2">EOD Submissions</h3>
            <p class="text-xs text-slate-400 mt-1">EOD module is not implemented yet.</p>
        </div>
    </div>
</x-admin-layout>
