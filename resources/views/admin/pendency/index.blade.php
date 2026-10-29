<x-admin-bootstrap-layout title="Pendency" :white-bg="true">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <span class="current">Pendency</span>
        </nav>
        <h1 class="jb-page-title">Pendency</h1>
        <p class="jb-muted mb-0" style="font-size:var(--text-sm)">What's still missing on each Lead, computed live from the Lead's own tabs — nothing here is entered or stored separately.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="jb-stat-tile h-100 p-3">
                <span class="jb-stat-label">Total Active Leads</span>
                <div class="d-flex align-items-baseline justify-content-between mt-2">
                    <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--primary-600)">{{ $totalActiveLeads }}</span>
                    <span class="jb-badge jb-badge-xs jb-badge-primary">All</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="jb-stat-tile h-100 p-3">
                <span class="jb-stat-label">Leads With Pending Work</span>
                <div class="d-flex align-items-baseline justify-content-between mt-2">
                    <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--amber-600)">{{ $leadsWithPendingWork }}</span>
                    <span class="jb-badge jb-badge-xs jb-badge-blue">In Progress</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="jb-stat-tile h-100 p-3">
                <span class="jb-stat-label">Total Pending Items</span>
                <div class="d-flex align-items-baseline justify-content-between mt-2">
                    <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--rose-600)">{{ $totalPendingItems }}</span>
                    <span class="jb-badge jb-badge-xs jb-badge-rose">Across all Leads</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="jb-stat-tile h-100 p-3">
                <span class="jb-stat-label">Completed Leads</span>
                <div class="d-flex align-items-baseline justify-content-between mt-2">
                    <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--emerald-600)">{{ $completedLeads }}</span>
                    <span class="jb-badge jb-badge-xs jb-badge-emerald">Nothing pending</span>
                </div>
            </div>
        </div>
    </div>

    <div class="jb-card p-3 mb-4">
        <form method="GET" action="{{ route('admin.pendency.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="jb-form-label">Lead</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Name or mobile number">
            </div>
            <div class="col-6 col-md-2">
                <label class="jb-form-label">City</label>
                <select name="city_id" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="jb-form-label">Assigned Employee</label>
                <select name="assigned_employee_id" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) request('assigned_employee_id') === (string) $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="jb-form-label">Module</label>
                <select name="module" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($moduleLabels as $label)
                        <option value="{{ $label }}" @selected(request('module') === $label)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="jb-form-label">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="pending" @selected(request('status') === 'pending')>Has Pending Work</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                </select>
            </div>
            <div class="col-12 col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
            @if (request()->anyFilled(['search', 'city_id', 'assigned_employee_id', 'module', 'status']))
                <div class="col-12">
                    <a href="{{ route('admin.pendency.index') }}" class="jb-file-link" style="font-size:var(--text-xs)">Clear Filters</a>
                </div>
            @endif
        </form>
    </div>

    <div class="jb-card p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:var(--text-sm)">
                <thead class="table-light">
                    <tr>
                        <th>Lead Name</th>
                        <th>Lead ID</th>
                        <th>City</th>
                        <th>Assigned Employee</th>
                        <th class="text-center">Total Pending</th>
                        <th class="text-center">Completed Items</th>
                        <th>Pending Modules</th>
                        <th>Last Updated</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summaries as $row)
                        @php $lead = $row['lead']; @endphp
                        <tr>
                            <td class="fw-semibold">{{ $lead->name }}</td>
                            <td class="text-muted">#{{ $lead->id }}</td>
                            <td>{{ $lead->city?->name ?? '—' }}</td>
                            <td>{{ $lead->assignedEmployee?->name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="jb-badge jb-badge-xs {{ $row['pending'] > 0 ? 'jb-badge-rose' : 'jb-badge-slate' }}">{{ $row['pending'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="jb-badge jb-badge-xs jb-badge-emerald">{{ $row['completed'] }}</span>
                            </td>
                            <td>
                                @if ($row['pending_modules'])
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($row['pending_modules'] as $moduleLabel)
                                            <span class="jb-badge jb-badge-xs jb-badge-blue">{{ $moduleLabel }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $lead->updated_at?->diffForHumans() }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.pendency.show', $lead) }}" class="btn btn-outline-primary btn-sm">View Pendency</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No leads match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-bootstrap-layout>
