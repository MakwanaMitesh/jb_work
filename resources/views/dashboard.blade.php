<x-admin-bootstrap-layout title="Dashboard">
    <div class="mb-4">
        <h1 class="jb-page-title">Dashboard</h1>
        <p class="mt-1 mb-0" style="font-size:var(--text-sm);color:var(--slate-500)">Welcome back, {{ auth()->user()->name }} 👋</p>
    </div>

    @if (isset($expiredInsurances) && $expiredInsurances->isNotEmpty())
        <div class="jb-alert jb-alert-warning mb-4 d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="jb-icon-tile" style="background:var(--amber-100);color:var(--amber-800)">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <div>
                    <h4 class="fw-bold mb-0" style="font-size:var(--text-sm);color:var(--amber-900)">Insurance Policies Expired</h4>
                    <p class="mt-1 mb-0" style="font-size:var(--text-xs);color:var(--amber-700)">{{ $expiredInsurances->count() }} active employee(s) have expired insurance policies.</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('insuranceExpiryModal').classList.remove('jb-hidden')" class="btn btn-sm" style="background:var(--amber-600);color:#fff">
                View Employees
            </button>
        </div>

        <div id="insuranceExpiryModal" class="jb-modal jb-hidden">
            <div class="jb-modal-backdrop" onclick="document.getElementById('insuranceExpiryModal').classList.add('jb-hidden')"></div>

            <div class="jb-modal-panel text-start" style="max-width:32rem;text-align:left">
                <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                    <h3 class="fw-bold m-0" style="font-size:var(--text-base);color:var(--slate-900)">Expired Insurance Policies</h3>
                    <button type="button" onclick="document.getElementById('insuranceExpiryModal').classList.add('jb-hidden')" class="jb-modal-close" style="position:static">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="d-flex flex-column gap-3" style="max-height:24rem;overflow-y:auto">
                    @foreach ($expiredInsurances as $emp)
                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="border:1px solid var(--slate-100);background:color-mix(in oklab, var(--slate-50) 50%, transparent)">
                            <div>
                                <h4 class="fw-bold mb-0" style="font-size:var(--text-sm);color:var(--slate-900)">{{ $emp->name }}</h4>
                                <p class="mt-1 mb-0" style="font-size:var(--text-xs);color:var(--slate-500)">Expired: {{ $emp->insurance_end_date->format('d M Y') }}</p>
                            </div>
                            <a href="{{ route('admin.employees.edit', $emp) }}" class="jb-btn-soft btn btn-sm">
                                Renew / Edit
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="jb-card p-4 d-flex align-items-center gap-3">
                <span class="jb-icon-tile" style="background:var(--indigo-50);color:var(--indigo-600)">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
                <div>
                    <span class="jb-stat-label">Total Employees</span>
                    <span class="d-block fw-bold mt-1" style="font-size:var(--text-2xl);color:var(--slate-900)">{{ $stats['total_employees'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="jb-card p-4 d-flex align-items-center gap-3">
                <span class="jb-icon-tile" style="background:var(--emerald-50);color:var(--emerald-600)">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <span class="jb-stat-label">Active Employees</span>
                    <span class="d-block fw-bold mt-1" style="font-size:var(--text-2xl);color:var(--slate-900)">{{ $stats['active_employees'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="jb-card p-4 d-flex align-items-center gap-3">
                <span class="jb-icon-tile" style="background:var(--rose-50);color:var(--rose-600)">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <span class="jb-stat-label">Inactive Employees</span>
                    <span class="d-block fw-bold mt-1" style="font-size:var(--text-2xl);color:var(--slate-900)">{{ $stats['inactive_employees'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="jb-card p-4 d-flex align-items-center gap-3">
                <span class="jb-icon-tile" style="background:var(--primary-50);color:var(--primary-600)">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </span>
                <div>
                    <span class="jb-stat-label">Roles</span>
                    <span class="d-block fw-bold mt-1" style="font-size:var(--text-2xl);color:var(--slate-900)">{{ $stats['total_roles'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="jb-card p-4">
                <h3 class="jb-section-title mb-3">Employees per Role</h3>
                @if ($roleBreakdown->isEmpty())
                    <p class="m-0" style="font-size:var(--text-sm);color:var(--slate-400)">No roles yet.</p>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach ($roleBreakdown as $role)
                            @php $percent = $stats['total_employees'] > 0 ? round(($role->users_count / $stats['total_employees']) * 100) : 0; @endphp
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:var(--text-sm)">
                                    <span class="fw-semibold" style="color:var(--slate-700)">{{ $role->name }}</span>
                                    <span class="jb-badge jb-badge-sm jb-badge-slate">{{ $role->users_count }}</span>
                                </div>
                                <div class="jb-progress-track">
                                    <div class="jb-progress-fill" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="jb-card p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="jb-section-title m-0">Recently Added</h3>
                    @can('employees.view')
                        <a href="{{ route('admin.employees.index') }}" class="jb-file-link">View All</a>
                    @endcan
                </div>

                @if ($recentEmployees->isEmpty())
                    <p class="m-0" style="font-size:var(--text-sm);color:var(--slate-400)">No employees yet.</p>
                @else
                    <div class="d-flex flex-column gap-2">
                        @foreach ($recentEmployees as $employee)
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-3" style="border:1px solid transparent">
                                <div class="d-flex align-items-center gap-3">
                                    @if ($employee->profilePhotoUrl())
                                        <img src="{{ $employee->profilePhotoUrl() }}" alt="" class="rounded-circle object-fit-cover flex-shrink-0" width="32" height="32">
                                    @else
                                        <span class="jb-avatar" style="width:2rem;height:2rem">{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
                                    @endif
                                    <div>
                                        <span class="d-block fw-bold" style="font-size:var(--text-xs);color:var(--slate-900)">{{ $employee->name }}</span>
                                        <span class="d-block mt-1" style="font-size:.625rem;color:var(--slate-400)">{{ $employee->roles->first()->name ?? '—' }}</span>
                                    </div>
                                </div>
                                <div>
                                    @if ($employee->isActive())
                                        <span class="jb-badge jb-badge-xs jb-badge-emerald">Active</span>
                                    @else
                                        <span class="jb-badge jb-badge-xs jb-badge-slate">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-bootstrap-layout>
