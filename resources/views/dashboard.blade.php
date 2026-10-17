<x-admin-bootstrap-layout title="Dashboard">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="jb-page-title">Dashboard</h1>
            <p class="mt-1 mb-0" style="font-size:var(--text-sm);color:var(--slate-500)">
                Welcome back, {{ auth()->user()->name }} 👋
                <span class="jb-badge jb-badge-xs jb-badge-slate ms-2">{{ $isAdmin ? 'Administrator' : 'Employee' }}</span>
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @can('leads.view')
                <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>View All Leads</span>
                </a>
            @endcan
            @can('leads.create')
                <a href="{{ route('admin.leads.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>New Lead</span>
                </a>
            @endcan
        </div>
    </div>

    @if ($isAdmin && isset($expiredInsurances) && $expiredInsurances->isNotEmpty())
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

    @php
        $statusConfig = [
            'new' => ['label' => 'New', 'badge' => 'jb-badge-blue', 'hex' => '#3b82f6'],
            'contacted' => ['label' => 'Contacted', 'badge' => 'jb-badge-cyan', 'hex' => '#06b6d4'],
            'in_progress' => ['label' => 'In Progress', 'badge' => 'jb-badge-indigo', 'hex' => '#6366f1'],
            'visit_pending' => ['label' => 'Visit Pending', 'badge' => 'jb-badge-amber', 'hex' => '#f59e0b'],
            'visit_completed' => ['label' => 'Visit Completed', 'badge' => 'jb-badge-emerald', 'hex' => '#10b981'],
            'documentation_pending' => ['label' => 'Doc Pending', 'badge' => 'jb-badge-indigo', 'hex' => '#6366f1'],
            'documentation_in_progress' => ['label' => 'Doc In Progress', 'badge' => 'jb-badge-purple', 'hex' => '#a855f7'],
            'documentation_completed' => ['label' => 'Doc Completed', 'badge' => 'jb-badge-teal', 'hex' => '#14b8a6'],
            'under_process' => ['label' => 'Under Process', 'badge' => 'jb-badge-cyan', 'hex' => '#06b6d4'],
            'approved' => ['label' => 'Approved', 'badge' => 'jb-badge-emerald', 'hex' => '#10b981'],
            'converted' => ['label' => 'Converted', 'badge' => 'jb-badge-green', 'hex' => '#22c55e'],
            'completed' => ['label' => 'Completed', 'badge' => 'jb-badge-green', 'hex' => '#16a34a'],
            'rejected' => ['label' => 'Rejected', 'badge' => 'jb-badge-rose', 'hex' => '#f43f5e'],
            'cancelled' => ['label' => 'Cancelled', 'badge' => 'jb-badge-slate', 'hex' => '#64748b'],
            'lost' => ['label' => 'Lost', 'badge' => 'jb-badge-rose', 'hex' => '#e11d48'],
        ];
    @endphp

    {{-- ========================================================================= --}}
    {{-- ADMIN DASHBOARD WIDGETS                                                  --}}
    {{-- ========================================================================= --}}
    @if ($isAdmin)
        {{-- 1. Overall Admin Summary Cards --}}
        <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="jb-stat-tile h-100 p-3">
                    <span class="jb-stat-label">Total Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--primary-600)">{{ $leadStats['total_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-primary">All</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="jb-stat-tile h-100 p-3">
                    <span class="jb-stat-label">Pending / Active Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--blue-600)">{{ $leadStats['pending_active_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-blue">In Pipeline</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="jb-stat-tile h-100 p-3">
                    <span class="jb-stat-label">Approved / Converted Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--emerald-600)">{{ $leadStats['approved_converted_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-emerald">Won / Converted</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="jb-stat-tile h-100 p-3">
                    <span class="jb-stat-label">Rejected / Closed Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--rose-600)">{{ $leadStats['rejected_closed_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-rose">Closed / Lost</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Status Breakdown Tiles --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="jb-stat-tile h-100 p-2 px-3">
                    <span class="jb-stat-label" style="font-size:var(--text-xs)">New Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <span class="d-block fw-bold" style="font-size:var(--text-xl);color:var(--blue-600)">{{ $leadStats['new_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-blue">Fresh</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="jb-stat-tile h-100 p-2 px-3">
                    <span class="jb-stat-label" style="font-size:var(--text-xs)">Follow-up Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <span class="d-block fw-bold" style="font-size:var(--text-xl);color:var(--amber-600)">{{ $leadStats['follow_up_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-amber">Follow-up</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="jb-stat-tile h-100 p-2 px-3">
                    <span class="jb-stat-label" style="font-size:var(--text-xs)">Qualified Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <span class="d-block fw-bold" style="font-size:var(--text-xl);color:var(--teal-600)">{{ $leadStats['qualified_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-teal">Process</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="jb-stat-tile h-100 p-2 px-3">
                    <span class="jb-stat-label" style="font-size:var(--text-xs)">Converted Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <span class="d-block fw-bold" style="font-size:var(--text-xl);color:var(--emerald-600)">{{ $leadStats['converted_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-emerald">Won</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="jb-stat-tile h-100 p-2 px-3">
                    <span class="jb-stat-label" style="font-size:var(--text-xs)">Closed Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <span class="d-block fw-bold" style="font-size:var(--text-xl);color:var(--slate-600)">{{ $leadStats['closed_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-slate">Closed</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="jb-stat-tile h-100 p-2 px-3">
                    <span class="jb-stat-label" style="font-size:var(--text-xs)">Active Cities</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <span class="d-block fw-bold" style="font-size:var(--text-xl);color:var(--indigo-600)">{{ $cityLeadStatusCards->count() }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-indigo">Locations</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. City-wise Lead Status Dashboard Matrix --}}
        <div class="jb-card p-4 mb-4">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="jb-section-title m-0">City-wise Lead Status Breakdown</h3>
                        <span class="jb-badge jb-badge-xs jb-badge-slate">{{ $cityLeadStatusCards->count() }} {{ \Illuminate\Support\Str::plural('City', $cityLeadStatusCards->count()) }}</span>
                    </div>
                    <p class="mb-0 mt-1" style="font-size:var(--text-xs);color:var(--slate-500)">
                        Dynamic breakdown of lead status counts across all cities. Click anywhere on a city card or click a status count to filter leads.
                    </p>
                </div>
                @can('leads.view')
                    <a href="{{ route('admin.leads.index') }}" class="jb-file-link" style="font-size:var(--text-xs)">View All Leads &rarr;</a>
                @endcan
            </div>

            @if ($cityLeadStatusCards->isEmpty())
                <div class="text-center py-5 text-muted" style="font-size:var(--text-sm)">
                    No leads available by city.
                </div>
            @else
                <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-4">
                    @foreach ($cityLeadStatusCards as $city)
                        @php
                            $cityFilterParam = $city['city_id'] ? $city['city_id'] : 'unassigned';
                            $cityUrl = route('admin.leads.index', ['city_id' => $cityFilterParam]);
                        @endphp
                        <div class="col">
                            <div class="jb-card h-100 p-3 d-flex flex-column justify-content-between"
                                 style="background:var(--slate-50);border:1px solid var(--slate-200);cursor:pointer;transition:transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;"
                                 onmouseover="this.style.borderColor='var(--primary-300)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.06)';"
                                 onmouseout="this.style.borderColor='var(--slate-200)';this.style.boxShadow='none';"
                                 onclick="window.location='{{ $cityUrl }}'"
                                 title="View all {{ $city['city_name'] }} leads">
                                <div>
                                    {{-- City Header --}}
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                        <a href="{{ $cityUrl }}"
                                           onclick="event.stopPropagation();"
                                           class="fw-bold text-truncate text-uppercase text-decoration-none"
                                           style="font-size:var(--text-sm);color:var(--slate-900);letter-spacing:0.025em"
                                           title="Filter leads for {{ $city['city_name'] }}">
                                            {{ $city['city_name'] }}
                                        </a>
                                        <a href="{{ $cityUrl }}"
                                           onclick="event.stopPropagation();"
                                           class="jb-badge jb-badge-sm jb-badge-primary text-decoration-none flex-shrink-0"
                                           title="Total {{ $city['total_leads'] }} leads in {{ $city['city_name'] }}">
                                            Total: {{ $city['total_leads'] }}
                                        </a>
                                    </div>

                                    <hr class="my-2" style="border-color:var(--slate-200);opacity:0.8">

                                    {{-- Status Breakdown List (Only statuses having count > 0) --}}
                                    <div class="d-flex flex-column gap-1">
                                        @php
                                            $hasStatuses = false;
                                        @endphp
                                        @foreach (\App\Models\Lead::STATUSES as $statusKey => $statusLabel)
                                            @if (!empty($city['status_counts'][$statusKey]))
                                                @php
                                                    $hasStatuses = true;
                                                    $count = $city['status_counts'][$statusKey];
                                                    $cfg = $statusConfig[$statusKey] ?? ['label' => $statusLabel, 'badge' => 'jb-badge-slate', 'hex' => '#64748b'];
                                                    $statusUrl = route('admin.leads.index', ['city_id' => $cityFilterParam, 'status' => $statusKey]);
                                                @endphp
                                                <div onclick="event.stopPropagation(); window.location='{{ $statusUrl }}';"
                                                     class="d-flex align-items-center justify-content-between p-1 px-2 rounded-2 text-decoration-none"
                                                     style="font-size:var(--text-xs);color:var(--slate-700);cursor:pointer;transition:background-color 0.15s ease;"
                                                     onmouseover="this.style.background='var(--slate-200)'"
                                                     onmouseout="this.style.background='transparent'"
                                                     title="Filter {{ $count }} {{ $cfg['label'] }} leads in {{ $city['city_name'] }}">
                                                    <span class="d-flex align-items-center gap-1 text-truncate">
                                                        <span class="d-inline-block rounded-circle flex-shrink-0" style="width:7px;height:7px;background:{{ $cfg['hex'] }}"></span>
                                                        <span class="text-truncate">{{ $cfg['label'] }}</span>
                                                    </span>
                                                    <span class="jb-badge jb-badge-xs {{ $cfg['badge'] }} ms-2">{{ $count }}</span>
                                                </div>
                                            @endif
                                        @endforeach

                                        @if (! $hasStatuses)
                                            <span class="text-muted text-center py-1" style="font-size:var(--text-xs)">No leads available</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- 3. Leads by Status & Leads by City Charts --}}
        <div class="row g-4 mb-4">
            {{-- Leads by Status --}}
            <div class="col-12 col-lg-6">
                <div class="jb-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="jb-section-title m-0">Leads by Status</h3>
                        <span class="jb-badge jb-badge-xs jb-badge-slate">{{ $leadsByStatus->count() }} Statuses</span>
                    </div>

                    @if ($leadsByStatus->isEmpty())
                        <div class="text-center py-5 text-muted" style="font-size:var(--text-sm)">No lead status data available.</div>
                    @else
                        <div class="row align-items-center g-3">
                            <div class="col-12 col-sm-5 d-flex justify-content-center">
                                <div style="position:relative;width:180px;height:180px">
                                    <canvas id="leadsByStatusChart"></canvas>
                                </div>
                            </div>
                            <div class="col-12 col-sm-7">
                                <div class="d-flex flex-column gap-2" style="max-height:220px;overflow-y:auto">
                                    @foreach ($leadsByStatus as $st)
                                        @php
                                            $cfg = $statusConfig[$st->status] ?? ['label' => ucfirst(str_replace('_', ' ', $st->status)), 'badge' => 'jb-badge-slate', 'hex' => '#64748b'];
                                            $pct = $leadStats['total_leads'] > 0 ? round(($st->total / $leadStats['total_leads']) * 100) : 0;
                                        @endphp
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:var(--text-xs)">
                                                <span class="fw-semibold text-truncate" style="color:var(--slate-700)">
                                                    <span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:{{ $cfg['hex'] }}"></span>
                                                    {{ $cfg['label'] }}
                                                </span>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="jb-badge jb-badge-xs {{ $cfg['badge'] }}">{{ $st->total }}</span>
                                                    <span style="color:var(--slate-400);min-width:32px;text-align:right">{{ $pct }}%</span>
                                                </div>
                                            </div>
                                            <div class="jb-progress-track" style="height:4px">
                                                <div class="jb-progress-fill" style="width:{{ $pct }}%;background:{{ $cfg['hex'] }}"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Leads by City --}}
            <div class="col-12 col-lg-6">
                <div class="jb-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="jb-section-title m-0">Leads by City</h3>
                        <span class="jb-badge jb-badge-xs jb-badge-slate">{{ $leadsByCity->count() }} Cities</span>
                    </div>

                    @if ($leadsByCity->isEmpty())
                        <div class="text-center py-5 text-muted" style="font-size:var(--text-sm)">No city lead data available.</div>
                    @else
                        <div class="row align-items-center g-3">
                            <div class="col-12 col-sm-5 d-flex justify-content-center">
                                <div style="position:relative;width:180px;height:180px">
                                    <canvas id="leadsByCityChart"></canvas>
                                </div>
                            </div>
                            <div class="col-12 col-sm-7">
                                <div class="d-flex flex-column gap-2" style="max-height:220px;overflow-y:auto">
                                    @foreach ($leadsByCity as $cityItem)
                                        @php
                                            $pct = $leadStats['total_leads'] > 0 ? round(($cityItem->total / $leadStats['total_leads']) * 100) : 0;
                                        @endphp
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:var(--text-xs)">
                                                <span class="fw-semibold text-truncate" style="color:var(--slate-700)">
                                                    {{ $cityItem->name }}
                                                </span>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="jb-badge jb-badge-xs jb-badge-primary">{{ $cityItem->total }}</span>
                                                    <span style="color:var(--slate-400);min-width:32px;text-align:right">{{ $pct }}%</span>
                                                </div>
                                            </div>
                                            <div class="jb-progress-track" style="height:4px">
                                                <div class="jb-progress-fill" style="width:{{ $pct }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 4. Recent Leads List --}}
        <div class="jb-card p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="jb-section-title m-0">Recent Leads</h3>
                    <p class="mb-0" style="font-size:var(--text-xs);color:var(--slate-500)">Latest main leads created across all locations.</p>
                </div>
                @can('leads.view')
                    <a href="{{ route('admin.leads.index') }}" class="jb-file-link">View All Leads &rarr;</a>
                @endcan
            </div>

            @if ($recentLeads->isEmpty())
                <div class="text-center py-4 text-muted" style="font-size:var(--text-sm)">No leads found.</div>
            @else
                <div class="table-responsive rounded-3" style="border:1px solid var(--slate-100)">
                    <table class="table align-middle m-0" style="font-size:var(--text-xs)">
                        <thead style="background:var(--slate-50);color:var(--slate-500)">
                            <tr>
                                <th class="py-2 px-3">Lead Name</th>
                                <th class="py-2 px-3">City</th>
                                <th class="py-2 px-3">Loan Product</th>
                                <th class="py-2 px-3">Assigned Representative</th>
                                <th class="py-2 px-3">Current Status</th>
                                <th class="py-2 px-3 text-end">Created Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentLeads as $lead)
                                @php
                                    $cfg = $statusConfig[$lead->status] ?? ['label' => ucfirst(str_replace('_', ' ', $lead->status)), 'badge' => 'jb-badge-slate'];
                                @endphp
                                <tr style="border-top:1px solid var(--slate-100)">
                                    <td class="py-2 px-3">
                                        <div class="d-flex flex-column">
                                            <a href="{{ route('admin.leads.show', $lead) }}" class="fw-bold text-decoration-none" style="color:var(--slate-900)">
                                                {{ $lead->name }}
                                            </a>
                                            <span style="font-size:.6875rem;color:var(--slate-400)">#LD-{{ str_pad($lead->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3 fw-medium" style="color:var(--slate-700)">
                                        {{ $lead->city?->name ?: '—' }}
                                    </td>
                                    <td class="py-2 px-3">
                                        <span class="jb-badge jb-badge-xs jb-badge-slate">{{ $lead->loanProduct?->name ?: '—' }}</span>
                                    </td>
                                    <td class="py-2 px-3" style="color:var(--slate-600)">
                                        {{ $lead->assignedEmployee?->name ?: 'Unassigned' }}
                                    </td>
                                    <td class="py-2 px-3">
                                        <span class="jb-badge jb-badge-xs {{ $cfg['badge'] }}">{{ $cfg['label'] }}</span>
                                    </td>
                                    <td class="py-2 px-3 text-end" style="color:var(--slate-500)">
                                        {{ $lead->created_at ? $lead->created_at->format('d M Y') : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- 5. Employee & System Overview --}}
        <div class="row g-4">
            <div class="col-12 col-lg-7">
                <div class="jb-card p-4 h-100">
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
                <div class="jb-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="jb-section-title m-0">Recent Employees</h3>
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

    {{-- ========================================================================= --}}
    {{-- EMPLOYEE DASHBOARD WIDGETS (Strictly scoped to logged-in employee)         --}}
    {{-- ========================================================================= --}}
    @else
        {{-- 1. Employee Overall Summary Cards --}}
        <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="jb-stat-tile h-100 p-3">
                    <span class="jb-stat-label">Total Assigned Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--primary-600)">{{ $leadStats['total_assigned_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-primary">Assigned</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="jb-stat-tile h-100 p-3">
                    <span class="jb-stat-label">Pending / Active Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--blue-600)">{{ $leadStats['pending_active_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-blue">In Pipeline</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="jb-stat-tile h-100 p-3">
                    <span class="jb-stat-label">Approved / Converted Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--emerald-600)">{{ $leadStats['approved_converted_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-emerald">Won / Converted</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="jb-stat-tile h-100 p-3">
                    <span class="jb-stat-label">Rejected / Closed Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                        <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--rose-600)">{{ $leadStats['rejected_closed_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-rose">Closed / Lost</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Employee Quick Status Breakdown Tiles --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="jb-stat-tile h-100 p-2 px-3">
                    <span class="jb-stat-label" style="font-size:var(--text-xs)">New Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <span class="d-block fw-bold" style="font-size:var(--text-xl);color:var(--blue-600)">{{ $leadStats['new_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-blue">Fresh</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="jb-stat-tile h-100 p-2 px-3">
                    <span class="jb-stat-label" style="font-size:var(--text-xs)">Follow-up Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <span class="d-block fw-bold" style="font-size:var(--text-xl);color:var(--amber-600)">{{ $leadStats['follow_up_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-amber">Follow-up</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="jb-stat-tile h-100 p-2 px-3">
                    <span class="jb-stat-label" style="font-size:var(--text-xs)">Qualified Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <span class="d-block fw-bold" style="font-size:var(--text-xl);color:var(--teal-600)">{{ $leadStats['qualified_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-teal">Process</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="jb-stat-tile h-100 p-2 px-3">
                    <span class="jb-stat-label" style="font-size:var(--text-xs)">Converted Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <span class="d-block fw-bold" style="font-size:var(--text-xl);color:var(--emerald-600)">{{ $leadStats['converted_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-emerald">Won</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="jb-stat-tile h-100 p-2 px-3">
                    <span class="jb-stat-label" style="font-size:var(--text-xs)">Closed Leads</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <span class="d-block fw-bold" style="font-size:var(--text-xl);color:var(--slate-600)">{{ $leadStats['closed_leads'] }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-slate">Closed</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="jb-stat-tile h-100 p-2 px-3">
                    <span class="jb-stat-label" style="font-size:var(--text-xs)">Assigned Cities</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <span class="d-block fw-bold" style="font-size:var(--text-xl);color:var(--indigo-600)">{{ $cityLeadStatusCards->count() }}</span>
                        <span class="jb-badge jb-badge-xs jb-badge-indigo">Locations</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. City-wise Lead Status Dashboard Matrix (Assigned Leads Only) --}}
        <div class="jb-card p-4 mb-4">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="jb-section-title m-0">My City-wise Lead Status Breakdown</h3>
                        <span class="jb-badge jb-badge-xs jb-badge-slate">{{ $cityLeadStatusCards->count() }} {{ \Illuminate\Support\Str::plural('City', $cityLeadStatusCards->count()) }}</span>
                    </div>
                    <p class="mb-0 mt-1" style="font-size:var(--text-xs);color:var(--slate-500)">
                        Dynamic breakdown of your assigned leads by city and status. Click anywhere on a city card or click a status count to filter.
                    </p>
                </div>
                @can('leads.view')
                    <a href="{{ route('admin.leads.index', ['assigned_employee_id' => auth()->id()]) }}" class="jb-file-link" style="font-size:var(--text-xs)">View My Leads &rarr;</a>
                @endcan
            </div>

            @if ($cityLeadStatusCards->isEmpty())
                <div class="text-center py-5 text-muted" style="font-size:var(--text-sm)">
                    No leads available by city.
                </div>
            @else
                <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-4">
                    @foreach ($cityLeadStatusCards as $city)
                        @php
                            $cityFilterParam = $city['city_id'] ? $city['city_id'] : 'unassigned';
                            $cityUrl = route('admin.leads.index', ['city_id' => $cityFilterParam, 'assigned_employee_id' => auth()->id()]);
                        @endphp
                        <div class="col">
                            <div class="jb-card h-100 p-3 d-flex flex-column justify-content-between"
                                 style="background:var(--slate-50);border:1px solid var(--slate-200);cursor:pointer;transition:transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;"
                                 onmouseover="this.style.borderColor='var(--primary-300)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.06)';"
                                 onmouseout="this.style.borderColor='var(--slate-200)';this.style.boxShadow='none';"
                                 onclick="window.location='{{ $cityUrl }}'"
                                 title="View your {{ $city['city_name'] }} leads">
                                <div>
                                    {{-- City Header --}}
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                        <a href="{{ $cityUrl }}"
                                           onclick="event.stopPropagation();"
                                           class="fw-bold text-truncate text-uppercase text-decoration-none"
                                           style="font-size:var(--text-sm);color:var(--slate-900);letter-spacing:0.025em"
                                           title="Filter your leads for {{ $city['city_name'] }}">
                                            {{ $city['city_name'] }}
                                        </a>
                                        <a href="{{ $cityUrl }}"
                                           onclick="event.stopPropagation();"
                                           class="jb-badge jb-badge-sm jb-badge-primary text-decoration-none flex-shrink-0"
                                           title="Total {{ $city['total_leads'] }} assigned leads in {{ $city['city_name'] }}">
                                            Total: {{ $city['total_leads'] }}
                                        </a>
                                    </div>

                                    <hr class="my-2" style="border-color:var(--slate-200);opacity:0.8">

                                    {{-- Status Breakdown List (Only statuses having count > 0) --}}
                                    <div class="d-flex flex-column gap-1">
                                        @php
                                            $hasStatuses = false;
                                        @endphp
                                        @foreach (\App\Models\Lead::STATUSES as $statusKey => $statusLabel)
                                            @if (!empty($city['status_counts'][$statusKey]))
                                                @php
                                                    $hasStatuses = true;
                                                    $count = $city['status_counts'][$statusKey];
                                                    $cfg = $statusConfig[$statusKey] ?? ['label' => $statusLabel, 'badge' => 'jb-badge-slate', 'hex' => '#64748b'];
                                                    $statusUrl = route('admin.leads.index', ['city_id' => $cityFilterParam, 'status' => $statusKey, 'assigned_employee_id' => auth()->id()]);
                                                @endphp
                                                <div onclick="event.stopPropagation(); window.location='{{ $statusUrl }}';"
                                                     class="d-flex align-items-center justify-content-between p-1 px-2 rounded-2 text-decoration-none"
                                                     style="font-size:var(--text-xs);color:var(--slate-700);cursor:pointer;transition:background-color 0.15s ease;"
                                                     onmouseover="this.style.background='var(--slate-200)'"
                                                     onmouseout="this.style.background='transparent'"
                                                     title="Filter {{ $count }} {{ $cfg['label'] }} leads in {{ $city['city_name'] }}">
                                                    <span class="d-flex align-items-center gap-1 text-truncate">
                                                        <span class="d-inline-block rounded-circle flex-shrink-0" style="width:7px;height:7px;background:{{ $cfg['hex'] }}"></span>
                                                        <span class="text-truncate">{{ $cfg['label'] }}</span>
                                                    </span>
                                                    <span class="jb-badge jb-badge-xs {{ $cfg['badge'] }} ms-2">{{ $count }}</span>
                                                </div>
                                            @endif
                                        @endforeach

                                        @if (! $hasStatuses)
                                            <span class="text-muted text-center py-1" style="font-size:var(--text-xs)">No leads available</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- 3. Assigned Leads by Status & City Breakdown --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-6">
                <div class="jb-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="jb-section-title m-0">Assigned Leads by Status</h3>
                        <span class="jb-badge jb-badge-xs jb-badge-slate">{{ $leadsByStatus->count() }} Statuses</span>
                    </div>

                    @if ($leadsByStatus->isEmpty())
                        <div class="text-center py-5 text-muted" style="font-size:var(--text-sm)">No assigned leads yet.</div>
                    @else
                        <div class="row align-items-center g-3">
                            <div class="col-12 col-sm-5 d-flex justify-content-center">
                                <div style="position:relative;width:180px;height:180px">
                                    <canvas id="leadsByStatusChart"></canvas>
                                </div>
                            </div>
                            <div class="col-12 col-sm-7">
                                <div class="d-flex flex-column gap-2" style="max-height:220px;overflow-y:auto">
                                    @foreach ($leadsByStatus as $st)
                                        @php
                                            $cfg = $statusConfig[$st->status] ?? ['label' => ucfirst(str_replace('_', ' ', $st->status)), 'badge' => 'jb-badge-slate', 'hex' => '#64748b'];
                                            $pct = $leadStats['total_assigned_leads'] > 0 ? round(($st->total / $leadStats['total_assigned_leads']) * 100) : 0;
                                        @endphp
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:var(--text-xs)">
                                                <span class="fw-semibold text-truncate" style="color:var(--slate-700)">
                                                    <span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:{{ $cfg['hex'] }}"></span>
                                                    {{ $cfg['label'] }}
                                                </span>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="jb-badge jb-badge-xs {{ $cfg['badge'] }}">{{ $st->total }}</span>
                                                    <span style="color:var(--slate-400);min-width:32px;text-align:right">{{ $pct }}%</span>
                                                </div>
                                            </div>
                                            <div class="jb-progress-track" style="height:4px">
                                                <div class="jb-progress-fill" style="width:{{ $pct }}%;background:{{ $cfg['hex'] }}"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="jb-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="jb-section-title m-0">Assigned Leads by City</h3>
                        <span class="jb-badge jb-badge-xs jb-badge-slate">{{ $leadsByCity->count() }} Cities</span>
                    </div>

                    @if ($leadsByCity->isEmpty())
                        <div class="text-center py-5 text-muted" style="font-size:var(--text-sm)">No assigned lead locations.</div>
                    @else
                        <div class="row align-items-center g-3">
                            <div class="col-12 col-sm-5 d-flex justify-content-center">
                                <div style="position:relative;width:180px;height:180px">
                                    <canvas id="leadsByCityChart"></canvas>
                                </div>
                            </div>
                            <div class="col-12 col-sm-7">
                                <div class="d-flex flex-column gap-2" style="max-height:220px;overflow-y:auto">
                                    @foreach ($leadsByCity as $cityItem)
                                        @php
                                            $pct = $leadStats['total_assigned_leads'] > 0 ? round(($cityItem->total / $leadStats['total_assigned_leads']) * 100) : 0;
                                        @endphp
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:var(--text-xs)">
                                                <span class="fw-semibold text-truncate" style="color:var(--slate-700)">
                                                    {{ $cityItem->name }}
                                                </span>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="jb-badge jb-badge-xs jb-badge-primary">{{ $cityItem->total }}</span>
                                                    <span style="color:var(--slate-400);min-width:32px;text-align:right">{{ $pct }}%</span>
                                                </div>
                                            </div>
                                            <div class="jb-progress-track" style="height:4px">
                                                <div class="jb-progress-fill" style="width:{{ $pct }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 3. Recent Assigned Leads & Upcoming Follow-ups --}}
        <div class="row g-4 mb-4">
            {{-- Recent Assigned Leads --}}
            <div class="col-12 col-xl-7">
                <div class="jb-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="jb-section-title m-0">Recent Assigned Leads</h3>
                        <span class="jb-badge jb-badge-xs jb-badge-slate">{{ $recentAssignedLeads->count() }} Leads</span>
                    </div>

                    @if ($recentAssignedLeads->isEmpty())
                        <div class="text-center py-5 text-muted" style="font-size:var(--text-sm)">No assigned leads found.</div>
                    @else
                        <div class="table-responsive rounded-3" style="border:1px solid var(--slate-100)">
                            <table class="table align-middle m-0" style="font-size:var(--text-xs)">
                                <thead style="background:var(--slate-50);color:var(--slate-500)">
                                    <tr>
                                        <th class="py-2 px-3">Lead</th>
                                        <th class="py-2 px-3">City & Product</th>
                                        <th class="py-2 px-3">Status</th>
                                        <th class="py-2 px-3">Next Follow-up</th>
                                        <th class="py-2 px-3 text-end">Last Activity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentAssignedLeads as $lead)
                                        @php
                                            $cfg = $statusConfig[$lead->status] ?? ['label' => ucfirst(str_replace('_', ' ', $lead->status)), 'badge' => 'jb-badge-slate'];
                                            $latestVisit = $lead->visits->first();
                                            $nextFollowUp = $latestVisit?->next_follow_up_date ?: ($latestVisit?->visit_date ?: null);
                                            $latestActivity = $lead->activities->first();
                                        @endphp
                                        <tr style="border-top:1px solid var(--slate-100)">
                                            <td class="py-2 px-3">
                                                <a href="{{ route('admin.leads.show', $lead) }}" class="fw-bold text-decoration-none d-block text-truncate" style="color:var(--slate-900);max-width:180px">
                                                    {{ $lead->name }}
                                                </a>
                                                <span style="font-size:.6875rem;color:var(--slate-400)">#LD-{{ str_pad($lead->id, 5, '0', STR_PAD_LEFT) }}</span>
                                            </td>
                                            <td class="py-2 px-3">
                                                <span class="d-block fw-medium" style="color:var(--slate-700)">{{ $lead->city?->name ?: '—' }}</span>
                                                <span style="font-size:.6875rem;color:var(--slate-400)">{{ $lead->loanProduct?->name ?: '—' }}</span>
                                            </td>
                                            <td class="py-2 px-3">
                                                <span class="jb-badge jb-badge-xs {{ $cfg['badge'] }}">{{ $cfg['label'] }}</span>
                                            </td>
                                            <td class="py-2 px-3" style="color:var(--slate-600)">
                                                @if ($nextFollowUp)
                                                    <span class="d-inline-flex align-items-center gap-1">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                        {{ $nextFollowUp->format('d M Y') }}
                                                    </span>
                                                @else
                                                    <span style="color:var(--slate-400)">—</span>
                                                @endif
                                            </td>
                                            <td class="py-2 px-3 text-end" style="color:var(--slate-500)">
                                                @if ($latestActivity)
                                                    <span class="d-block text-truncate" style="max-width:140px;font-size:.6875rem" title="{{ $latestActivity->description }}">
                                                        {{ $latestActivity->description }}
                                                    </span>
                                                    <span style="font-size:.625rem;color:var(--slate-400)">{{ $latestActivity->created_at->diffForHumans() }}</span>
                                                @else
                                                    <span style="color:var(--slate-400)">{{ $lead->updated_at->diffForHumans() }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Upcoming Follow-ups --}}
            <div class="col-12 col-xl-5">
                <div class="jb-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="jb-section-title m-0">Upcoming Follow-ups</h3>
                        <span class="jb-badge jb-badge-xs jb-badge-amber">{{ $upcomingFollowUps->count() }} Scheduled</span>
                    </div>

                    @if ($upcomingFollowUps->isEmpty())
                        <div class="text-center py-5 text-muted" style="font-size:var(--text-sm)">No upcoming follow-ups scheduled.</div>
                    @else
                        <div class="d-flex flex-column gap-2">
                            @foreach ($upcomingFollowUps as $visit)
                                @php
                                    $followUpDate = $visit->next_follow_up_date ?: $visit->visit_date;
                                    $leadItem = $visit->lead;
                                    $visitCfg = match($visit->status) {
                                        'scheduled' => 'jb-badge-blue',
                                        'completed' => 'jb-badge-emerald',
                                        'cancelled' => 'jb-badge-slate',
                                        'rescheduled' => 'jb-badge-amber',
                                        default => 'jb-badge-slate',
                                    };
                                @endphp
                                <div class="p-3 rounded-3" style="border:1px solid var(--slate-100);background:color-mix(in oklab, var(--slate-50) 50%, transparent)">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <div>
                                            @if ($leadItem)
                                                <a href="{{ route('admin.leads.show', $leadItem) }}" class="fw-bold text-decoration-none d-block" style="font-size:var(--text-xs);color:var(--slate-900)">
                                                    {{ $leadItem->name }}
                                                </a>
                                            @else
                                                <span class="fw-bold d-block" style="font-size:var(--text-xs);color:var(--slate-900)">Lead #{{ $visit->lead_id }}</span>
                                            @endif
                                            <span class="d-block mt-1" style="font-size:.6875rem;color:var(--slate-500)">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="me-1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ $followUpDate ? $followUpDate->format('d M Y, h:i A') : '—' }}
                                            </span>
                                        </div>
                                        <span class="jb-badge jb-badge-xs {{ $visitCfg }}">{{ ucfirst($visit->status) }}</span>
                                    </div>

                                    @if ($leadItem && ($leadItem->mobile_number || $leadItem->email))
                                        <div class="d-flex flex-wrap align-items-center gap-3 mt-2 pt-2 border-top" style="font-size:.6875rem;color:var(--slate-600)">
                                            @if ($leadItem->mobile_number)
                                                <span class="d-inline-flex align-items-center gap-1">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                    {{ $leadItem->mobile_number }}
                                                </span>
                                            @endif
                                            @if ($leadItem->email)
                                                <span class="d-inline-flex align-items-center gap-1">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                    {{ $leadItem->email }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 4. Recent Lead Activities Stream --}}
        <div class="jb-card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h3 class="jb-section-title m-0">Recent Activity</h3>
                <span class="jb-badge jb-badge-xs jb-badge-slate">Activities</span>
            </div>

            @if ($recentActivities->isEmpty())
                <div class="text-center py-4 text-muted" style="font-size:var(--text-sm)">No recent activities logged.</div>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach ($recentActivities as $act)
                        @php
                            $actBadge = match($act->type) {
                                'created' => ['bg' => 'var(--blue-50)', 'color' => 'var(--blue-600)', 'label' => 'Lead Created'],
                                'assigned', 'reassigned' => ['bg' => 'var(--indigo-50)', 'color' => 'var(--indigo-600)', 'label' => 'Assignment'],
                                'status_changed' => ['bg' => 'var(--amber-50)', 'color' => 'var(--amber-600)', 'label' => 'Status Changed'],
                                'visit_scheduled', 'visit_completed', 'visit_cancelled' => ['bg' => 'var(--emerald-50)', 'color' => 'var(--emerald-600)', 'label' => 'Visit'],
                                'document_uploaded' => ['bg' => 'var(--purple-50)', 'color' => 'var(--purple-600)', 'label' => 'Document'],
                                default => ['bg' => 'var(--slate-50)', 'color' => 'var(--slate-600)', 'label' => ucfirst(str_replace('_', ' ', $act->type))],
                            };
                        @endphp
                        <div class="d-flex align-items-start gap-3 pb-3 border-bottom" style="font-size:var(--text-xs)">
                            <span class="jb-icon-tile flex-shrink-0" style="width:2rem;height:2rem;background:{{ $actBadge['bg'] }};color:{{ $actBadge['color'] }}">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold" style="color:var(--slate-900)">{{ $act->description }}</span>
                                        <span class="jb-badge jb-badge-xs jb-badge-slate">{{ $actBadge['label'] }}</span>
                                    </div>
                                    <span style="font-size:.6875rem;color:var(--slate-400)">{{ $act->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-3 mt-1" style="font-size:.6875rem;color:var(--slate-500)">
                                    @if ($act->lead)
                                        <span>Lead: <a href="{{ route('admin.leads.show', $act->lead) }}" class="fw-semibold text-decoration-none" style="color:var(--primary-600)">{{ $act->lead->name }}</a></span>
                                    @endif
                                    @if ($act->user)
                                        <span>By: <strong style="color:var(--slate-700)">{{ $act->user->name }}</strong></span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- Chart.js CDN & Initialization --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Leads by Status Chart
            const statusCanvas = document.getElementById('leadsByStatusChart');
            if (statusCanvas) {
                const statusLabels = @json($leadsByStatus->map(fn($st) => $statusConfig[$st->status]['label'] ?? ucfirst(str_replace('_', ' ', $st->status))));
                const statusData = @json($leadsByStatus->pluck('total'));
                const statusColors = @json($leadsByStatus->map(fn($st) => $statusConfig[$st->status]['hex'] ?? '#64748b'));

                if (statusData.length > 0) {
                    new Chart(statusCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: statusLabels,
                            datasets: [{
                                data: statusData,
                                backgroundColor: statusColors,
                                borderWidth: 2,
                                borderColor: '#ffffff',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => ` ${context.label}: ${context.raw}`
                                    }
                                }
                            },
                            cutout: '68%'
                        }
                    });
                }
            }

            // 2. Leads by City Chart
            const cityCanvas = document.getElementById('leadsByCityChart');
            if (cityCanvas) {
                const cityLabels = @json($leadsByCity->pluck('name'));
                const cityData = @json($leadsByCity->pluck('total'));
                const cityColors = ['#4f46e5', '#3b82f6', '#06b6d4', '#10b981', '#f59e0b', '#a855f7', '#ec4899', '#64748b'];

                if (cityData.length > 0) {
                    new Chart(cityCanvas, {
                        type: 'pie',
                        data: {
                            labels: cityLabels,
                            datasets: [{
                                data: cityData,
                                backgroundColor: cityColors.slice(0, cityLabels.length),
                                borderWidth: 2,
                                borderColor: '#ffffff',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => ` ${context.label}: ${context.raw}`
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });
    </script>
</x-admin-bootstrap-layout>
