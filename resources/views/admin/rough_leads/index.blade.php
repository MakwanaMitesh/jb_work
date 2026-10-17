<x-admin-bootstrap-layout title="Rough Leads">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.rough-leads.index') }}">Rough Leads</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">List</span>
        </nav>

        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="jb-page-title">Rough Leads</h1>
                <p class="m-0 mt-1" style="font-size:var(--text-sm);color:var(--slate-500)">Quick initial enquiries — convert genuine ones into Main Leads.</p>
            </div>
            @can('rough_leads.create')
                <a href="{{ route('admin.rough-leads.create') }}" class="btn btn-primary">
                    Add Rough Lead
                </a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="jb-alert jb-alert-success fw-medium mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="jb-alert jb-alert-danger fw-medium mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="jb-card overflow-hidden">
        <div class="jb-card-header d-flex flex-column flex-sm-row gap-3 justify-content-between align-items-center">
            <form action="{{ route('admin.rough-leads.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-3 w-100 w-sm-auto">
                <div class="jb-search">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--slate-400);flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or mobile...">
                </div>
                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto">
                    <option value="">All Statuses</option>
                    @foreach (\App\Models\RoughLead::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('admin.rough-leads.index') }}" class="jb-file-link-muted fw-medium">Clear</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="jb-table jb-table-compact">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Mobile Number</th>
                        <th>City</th>
                        <th>Assigned Employee</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roughLeads as $roughLead)
                        <tr>
                            <td class="jb-cell-strong">{{ $roughLead->full_name }}</td>
                            <td>{{ $roughLead->mobile_number }}</td>
                            <td>{{ $roughLead->city?->name ?? '—' }}</td>
                            <td>{{ $roughLead->assignedEmployee?->name ?? '—' }}</td>
                            <td>
                                @can('rough_leads.change_status')
                                    @if (! $roughLead->isConverted())
                                        <form method="POST" action="{{ route('admin.rough-leads.status', $roughLead) }}">
                                            @csrf
                                            <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto">
                                                @foreach (\App\Models\RoughLead::STATUSES as $value => $label)
                                                    @continue($value === 'converted')
                                                    <option value="{{ $value }}" @selected($roughLead->status === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @endif
                                @endcan

                                @if ($roughLead->isConverted() || ! auth()->user()->can('rough_leads.change_status'))
                                    @php
                                        $badge = match($roughLead->status) {
                                            'new' => 'jb-badge-blue',
                                            'contacted' => 'jb-badge-amber',
                                            'interested' => 'jb-badge-indigo',
                                            'follow_up' => 'jb-badge-purple',
                                            'qualified' => 'jb-badge-teal',
                                            'not_interested' => 'jb-badge-rose',
                                            'converted' => 'jb-badge-emerald',
                                            'closed' => 'jb-badge-slate',
                                            default => 'jb-badge-slate',
                                        };
                                    @endphp
                                    <span class="jb-badge {{ $badge }}">
                                        {{ \App\Models\RoughLead::STATUSES[$roughLead->status] ?? ucfirst($roughLead->status) }}
                                    </span>
                                @endif
                            </td>
                            <td>{{ $roughLead->created_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="jb-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" /></svg>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end text-start text-nowrap" style="min-width:13rem">
                                        <li>
                                            <a href="{{ route('admin.rough-leads.show', $roughLead) }}" class="dropdown-item">
                                                <span>View details</span>
                                            </a>
                                        </li>
                                        @can('rough_leads.edit')
                                            <li>
                                                <a href="{{ route('admin.rough-leads.edit', $roughLead) }}" class="dropdown-item">
                                                    <span>Edit</span>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('rough_leads.convert')
                                            @if (! $roughLead->isConverted())
                                                <li>
                                                    <form method="POST" action="{{ route('admin.rough-leads.convert', $roughLead) }}" onsubmit="return confirm('Convert this Rough Lead into a Main Lead? This cannot be undone.');">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <span>Convert to Main Lead</span>
                                                        </button>
                                                    </form>
                                                </li>
                                            @elseif ($roughLead->converted_lead_id)
                                                <li>
                                                    <a href="{{ route('admin.leads.show', $roughLead->converted_lead_id) }}" class="dropdown-item">
                                                        <span>View Main Lead</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endcan
                                        @can('rough_leads.delete')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.rough-leads.destroy', $roughLead) }}" onsubmit="return confirm('Are you sure you want to delete this rough lead?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item dropdown-item-danger">
                                                        <span>Delete</span>
                                                    </button>
                                                </form>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5" style="color:var(--slate-400)">No rough leads found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roughLeads->hasPages())
            <div class="p-3 border-top">
                {{ $roughLeads->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</x-admin-bootstrap-layout>
