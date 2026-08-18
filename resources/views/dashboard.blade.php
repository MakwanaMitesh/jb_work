<x-admin-layout title="Dashboard">
    <div class="mb-4">
        <h1 class="h4 fw-bold mb-1">Dashboard</h1>
        <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name }} 👋</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="jb-card p-3 h-100">
                <span class="jb-stat-icon jb-stat-icon--indigo mb-3">
                    {!! '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><path d="M16.5 9.5a2.6 2.6 0 1 0 0-5.2 2.6 2.6 0 0 0 0 5.2Z"/><path d="M15 14c2.8.4 5 2.7 5 6"/></svg>' !!}
                </span>
                <div class="text-muted small">Total Employees</div>
                <div class="h4 fw-bold mb-0">{{ $stats['total_employees'] }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="jb-card p-3 h-100">
                <span class="jb-stat-icon jb-stat-icon--green mb-3">
                    {!! '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>' !!}
                </span>
                <div class="text-muted small">Active Employees</div>
                <div class="h4 fw-bold mb-0">{{ $stats['active_employees'] }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="jb-card p-3 h-100">
                <span class="jb-stat-icon jb-stat-icon--amber mb-3">
                    {!! '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>' !!}
                </span>
                <div class="text-muted small">Inactive Employees</div>
                <div class="h4 fw-bold mb-0">{{ $stats['inactive_employees'] }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="jb-card p-3 h-100">
                <span class="jb-stat-icon jb-stat-icon--purple mb-3">
                    {!! '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 4.8 3.4 7.9 8 9 4.6-1.1 8-4.2 8-9V6l-8-3Z"/></svg>' !!}
                </span>
                <div class="text-muted small">Roles</div>
                <div class="h4 fw-bold mb-0">{{ $stats['total_roles'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="jb-card p-4 h-100">
                <h6 class="fw-semibold mb-3">Employees per Role</h6>
                @if ($roleBreakdown->isEmpty())
                    <p class="text-muted small mb-0">No roles yet.</p>
                @else
                    @foreach ($roleBreakdown as $role)
                        @php $percent = $stats['total_employees'] > 0 ? round(($role->users_count / $stats['total_employees']) * 100) : 0; @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-medium">{{ $role->name }}</span>
                                <span class="text-muted">{{ $role->users_count }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%; background-color: var(--jb-primary);"></div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="col-lg-5">
            <div class="jb-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-semibold mb-0">Recently Added Employees</h6>
                    @can('employees.view')
                        <a href="{{ route('admin.employees.index') }}" class="jb-link small">View All</a>
                    @endcan
                </div>

                @if ($recentEmployees->isEmpty())
                    <p class="text-muted small mb-0">No employees yet.</p>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach ($recentEmployees as $employee)
                            <div class="d-flex align-items-center gap-2">
                                @if ($employee->profilePhotoUrl())
                                    <img src="{{ $employee->profilePhotoUrl() }}" alt="" class="rounded-circle" width="32" height="32" style="object-fit: cover;">
                                @else
                                    <span class="jb-logo-mark" style="width:32px;height:32px;font-size:.75rem;">{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
                                @endif
                                <div class="flex-grow-1">
                                    <div class="small fw-semibold">{{ $employee->name }}</div>
                                    <div class="text-muted" style="font-size:.75rem;">{{ $employee->roles->first()->name ?? '—' }}</div>
                                </div>
                                @if ($employee->isActive())
                                    <span class="badge rounded-pill bg-success-subtle text-success-emphasis">Active</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">Inactive</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
