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
        @php
            $currentFilter = request('filter', 'all');
        @endphp

        {{-- 1. Dashboard Summary Cards --}}
        <div class="row g-3 mb-3">
            {{-- Total Assigned Leads --}}
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('dashboard', array_merge(request()->except(['page', 'filter']), ['filter' => 'all'])) }}#my-leads-table-section"
                   class="text-decoration-none">
                    <div class="jb-stat-tile h-100 p-3"
                         style="cursor:pointer;transition:transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;{{ in_array($currentFilter, ['all', '']) ? 'border:2px solid var(--primary-500);background:color-mix(in oklab, var(--primary-50) 40%, #fff);' : '' }}">
                        <span class="jb-stat-label">Total Assigned Leads</span>
                        <div class="d-flex align-items-baseline justify-content-between mt-2">
                            <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--primary-600)">{{ $leadStats['total_assigned_leads'] }}</span>
                            <span class="jb-badge jb-badge-xs jb-badge-primary">All</span>
                        </div>
                        <span class="d-block mt-1" style="font-size:.6875rem;color:var(--slate-400)">All assigned leads</span>
                    </div>
                </a>
            </div>

            {{-- Active Leads --}}
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('dashboard', array_merge(request()->except(['page', 'filter']), ['filter' => 'active'])) }}#my-leads-table-section"
                   class="text-decoration-none">
                    <div class="jb-stat-tile h-100 p-3"
                         style="cursor:pointer;transition:transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;{{ $currentFilter === 'active' ? 'border:2px solid var(--blue-500);background:color-mix(in oklab, var(--blue-50) 40%, #fff);' : '' }}">
                        <span class="jb-stat-label">Active Leads</span>
                        <div class="d-flex align-items-baseline justify-content-between mt-2">
                            <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--blue-600)">{{ $leadStats['active_leads'] }}</span>
                            <span class="jb-badge jb-badge-xs jb-badge-blue">In Pipeline</span>
                        </div>
                        <span class="d-block mt-1" style="font-size:.6875rem;color:var(--slate-400)">Active workflow</span>
                    </div>
                </a>
            </div>

            {{-- Pending Work --}}
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('dashboard', array_merge(request()->except(['page', 'filter']), ['filter' => 'pending_work'])) }}#my-leads-table-section"
                   class="text-decoration-none">
                    <div class="jb-stat-tile h-100 p-3"
                         style="cursor:pointer;transition:transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;{{ $currentFilter === 'pending_work' ? 'border:2px solid var(--purple-500);background:color-mix(in oklab, var(--purple-50) 40%, #fff);' : '' }}">
                        <span class="jb-stat-label">Pending Work</span>
                        <div class="d-flex align-items-baseline justify-content-between mt-2">
                            <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--purple-600)">{{ $leadStats['pending_work_count'] }}</span>
                            <span class="jb-badge jb-badge-xs jb-badge-purple">Tasks</span>
                        </div>
                        <span class="d-block mt-1" style="font-size:.6875rem;color:var(--slate-400)">Tasks & documents</span>
                    </div>
                </a>
            </div>

            {{-- Overdue Leads --}}
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('dashboard', array_merge(request()->except(['page', 'filter']), ['filter' => 'overdue'])) }}#my-leads-table-section"
                   class="text-decoration-none">
                    <div class="jb-stat-tile h-100 p-3"
                         style="cursor:pointer;transition:transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;{{ $currentFilter === 'overdue' ? 'border:2px solid var(--rose-500);background:color-mix(in oklab, var(--rose-50) 40%, #fff);' : '' }}">
                        <span class="jb-stat-label">Overdue Leads</span>
                        <div class="d-flex align-items-baseline justify-content-between mt-2">
                            <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--rose-600)">{{ $leadStats['overdue_leads'] }}</span>
                            <span class="jb-badge jb-badge-xs jb-badge-rose">Overdue</span>
                        </div>
                        <span class="d-block mt-1" style="font-size:.6875rem;color:var(--slate-400)">
                            @if ($hasDeadlineColumn) Past deadline @else No deadline field @endif
                        </span>
                    </div>
                </a>
            </div>

            {{-- Due Today --}}
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('dashboard', array_merge(request()->except(['page', 'filter']), ['filter' => 'due_today'])) }}#my-leads-table-section"
                   class="text-decoration-none">
                    <div class="jb-stat-tile h-100 p-3"
                         style="cursor:pointer;transition:transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;{{ $currentFilter === 'due_today' ? 'border:2px solid var(--amber-500);background:color-mix(in oklab, var(--amber-50) 40%, #fff);' : '' }}">
                        <span class="jb-stat-label">Due Today</span>
                        <div class="d-flex align-items-baseline justify-content-between mt-2">
                            <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--amber-600)">{{ $leadStats['due_today_leads'] }}</span>
                            <span class="jb-badge jb-badge-xs jb-badge-amber">Today</span>
                        </div>
                        <span class="d-block mt-1" style="font-size:.6875rem;color:var(--slate-400)">
                            @if ($hasDeadlineColumn) Incomplete work @else No deadline field @endif
                        </span>
                    </div>
                </a>
            </div>

            {{-- Completed Leads --}}
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('dashboard', array_merge(request()->except(['page', 'filter']), ['filter' => 'completed'])) }}#my-leads-table-section"
                   class="text-decoration-none">
                    <div class="jb-stat-tile h-100 p-3"
                         style="cursor:pointer;transition:transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;{{ $currentFilter === 'completed' ? 'border:2px solid var(--emerald-500);background:color-mix(in oklab, var(--emerald-50) 40%, #fff);' : '' }}">
                        <span class="jb-stat-label">Completed Leads</span>
                        <div class="d-flex align-items-baseline justify-content-between mt-2">
                            <span class="d-block fw-bold" style="font-size:var(--text-2xl);color:var(--emerald-600)">{{ $leadStats['completed_leads'] }}</span>
                            <span class="jb-badge jb-badge-xs jb-badge-emerald">Completed</span>
                        </div>
                        <span class="d-block mt-1" style="font-size:.6875rem;color:var(--slate-400)">{{ $leadStats['completed_this_month'] }} this month</span>
                    </div>
                </a>
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

        {{-- 2. My Assigned Leads Table --}}
        <div id="my-leads-table-section" class="jb-card p-4 mb-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="jb-section-title m-0">My Assigned Leads</h3>
                        <span class="jb-badge jb-badge-xs jb-badge-primary">{{ $tableLeads->total() }} Leads</span>
                    </div>
                    <p class="mb-0 mt-1" style="font-size:var(--text-xs);color:var(--slate-500)">
                        Search, filter, and monitor all Leads assigned to your queue. Nearest deadlines are shown first.
                    </p>
                </div>
                @can('leads.view')
                    <a href="{{ route('admin.leads.index', ['assigned_employee_id' => auth()->id()]) }}"
                       class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1"
                       style="font-size:var(--text-xs)">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Open in All Leads</span>
                    </a>
                @endcan
            </div>

            {{-- Filter & Search Form --}}
            <form method="GET" action="{{ route('dashboard') }}#my-leads-table-section" class="mb-3">
                <div class="p-3 rounded-3" style="background:var(--slate-50);border:1px solid var(--slate-200)">
                    {{-- Quick Filter Pills --}}
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3 pb-3 border-bottom" style="border-color:var(--slate-200)!important">
                        <span class="fw-semibold text-muted me-1" style="font-size:var(--text-xs)">Quick Filter:</span>
                        <a href="{{ route('dashboard', array_merge(request()->except(['page', 'filter']), ['filter' => 'all'])) }}#my-leads-table-section"
                           class="btn btn-sm py-1 px-2 {{ in_array($currentFilter, ['all', '']) ? 'btn-primary' : 'btn-outline-secondary' }}"
                           style="font-size:var(--text-xs)">
                            All ({{ $leadStats['total_assigned_leads'] }})
                        </a>
                        <a href="{{ route('dashboard', array_merge(request()->except(['page', 'filter']), ['filter' => 'active'])) }}#my-leads-table-section"
                           class="btn btn-sm py-1 px-2 {{ $currentFilter === 'active' ? 'btn-primary' : 'btn-outline-secondary' }}"
                           style="font-size:var(--text-xs)">
                            Active ({{ $leadStats['active_leads'] }})
                        </a>
                        <a href="{{ route('dashboard', array_merge(request()->except(['page', 'filter']), ['filter' => 'pending_work'])) }}#my-leads-table-section"
                           class="btn btn-sm py-1 px-2 {{ $currentFilter === 'pending_work' ? 'btn-primary' : 'btn-outline-secondary' }}"
                           style="font-size:var(--text-xs)">
                            With Pending Work ({{ count(array_filter($leadPendencyMap, fn($p) => ($p['pending'] ?? 0) > 0)) }})
                        </a>
                        <a href="{{ route('dashboard', array_merge(request()->except(['page', 'filter']), ['filter' => 'overdue'])) }}#my-leads-table-section"
                           class="btn btn-sm py-1 px-2 {{ $currentFilter === 'overdue' ? 'btn-danger' : 'btn-outline-secondary' }}"
                           style="font-size:var(--text-xs)">
                            Overdue ({{ $leadStats['overdue_leads'] }})
                        </a>
                        <a href="{{ route('dashboard', array_merge(request()->except(['page', 'filter']), ['filter' => 'due_today'])) }}#my-leads-table-section"
                           class="btn btn-sm py-1 px-2 {{ $currentFilter === 'due_today' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}"
                           style="font-size:var(--text-xs)">
                            Due Today ({{ $leadStats['due_today_leads'] }})
                        </a>
                        <a href="{{ route('dashboard', array_merge(request()->except(['page', 'filter']), ['filter' => 'completed'])) }}#my-leads-table-section"
                           class="btn btn-sm py-1 px-2 {{ $currentFilter === 'completed' ? 'btn-success' : 'btn-outline-secondary' }}"
                           style="font-size:var(--text-xs)">
                            Completed ({{ $leadStats['completed_leads'] }})
                        </a>
                    </div>

                    {{-- Form Inputs --}}
                    <div class="row g-2 align-items-center">
                        <input type="hidden" name="filter" value="{{ $currentFilter }}">
                        <input type="hidden" name="sort" value="{{ $currentSort }}">
                        <input type="hidden" name="direction" value="{{ $currentDirection }}">

                        {{-- Search Input --}}
                        <div class="col-12 col-md-4 col-xl-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white" style="border-color:var(--slate-300)">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                                <input type="text"
                                       name="search"
                                       value="{{ request('search') }}"
                                       placeholder="Search Name, ID (#LD-...) or Mobile"
                                       class="form-control"
                                       style="font-size:var(--text-xs)">
                            </div>
                        </div>

                        {{-- Status Filter --}}
                        <div class="col-6 col-md-2 col-xl-2">
                            <select name="status" class="form-select form-select-sm" style="font-size:var(--text-xs)">
                                <option value="">All Statuses</option>
                                @foreach (\App\Models\Lead::STATUSES as $sKey => $sLabel)
                                    <option value="{{ $sKey }}" {{ request('status') === $sKey ? 'selected' : '' }}>
                                        {{ $sLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- City Filter --}}
                        <div class="col-6 col-md-2 col-xl-2">
                            <select name="city_id" class="form-select form-select-sm" style="font-size:var(--text-xs)">
                                <option value="">All Cities</option>
                                @foreach ($assignedCities as $ct)
                                    <option value="{{ $ct->id }}" {{ request('city_id') == $ct->id ? 'selected' : '' }}>
                                        {{ $ct->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Loan Product Filter --}}
                        <div class="col-6 col-md-2 col-xl-2">
                            <select name="loan_product_id" class="form-select form-select-sm" style="font-size:var(--text-xs)">
                                <option value="">All Loan Products</option>
                                @foreach ($allLoanProducts as $lp)
                                    <option value="{{ $lp->id }}" {{ request('loan_product_id') == $lp->id ? 'selected' : '' }}>
                                        {{ $lp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Deadline Filter --}}
                        <div class="col-6 col-md-2 col-xl-2">
                            <select name="deadline_filter" class="form-select form-select-sm" style="font-size:var(--text-xs)">
                                <option value="">All Deadlines</option>
                                <option value="overdue" {{ request('deadline_filter') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                                <option value="due_today" {{ request('deadline_filter') === 'due_today' ? 'selected' : '' }}>Due Today</option>
                                <option value="upcoming_7" {{ request('deadline_filter') === 'upcoming_7' ? 'selected' : '' }}>Next 7 Days</option>
                                <option value="no_deadline" {{ request('deadline_filter') === 'no_deadline' ? 'selected' : '' }}>No Deadline Set</option>
                            </select>
                        </div>

                        {{-- Buttons --}}
                        <div class="col-12 col-xl-1 d-flex gap-1 justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm px-3 flex-grow-1 flex-xl-grow-0" style="font-size:var(--text-xs)">
                                Filter
                            </button>
                            @if (request()->hasAny(['search', 'status', 'city_id', 'loan_product_id', 'deadline_filter', 'filter']))
                                <a href="{{ route('dashboard') }}#my-leads-table-section" class="btn btn-outline-secondary btn-sm px-2" title="Reset Filters" style="font-size:var(--text-xs)">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>

            {{-- Leads Table --}}
            @if ($tableLeads->isEmpty())
                <div class="text-center py-5 text-muted" style="font-size:var(--text-sm)">
                    @if ($leadStats['total_assigned_leads'] === 0)
                        No assigned leads found. Leads assigned to you by administrators will appear here.
                    @else
                        No assigned leads match your current search/filter criteria.
                        <div class="mt-2">
                            <a href="{{ route('dashboard') }}#my-leads-table-section" class="jb-file-link" style="font-size:var(--text-xs)">Reset all filters &rarr;</a>
                        </div>
                    @endif
                </div>
            @else
                @php
                    $sortToggle = fn($col) => route('dashboard', array_merge(request()->all(), [
                        'sort' => $col,
                        'direction' => ($currentSort === $col && $currentDirection === 'asc') ? 'desc' : 'asc'
                    ])) . '#my-leads-table-section';
                @endphp
                <div class="table-responsive rounded-3" style="border:1px solid var(--slate-200)">
                    <table class="table align-middle m-0" style="font-size:var(--text-xs)">
                        <thead style="background:var(--slate-50);color:var(--slate-600)">
                            <tr>
                                <th class="py-2 px-3">
                                    <a href="{{ $sortToggle('name') }}" class="text-decoration-none text-muted d-inline-flex align-items-center gap-1">
                                        <span>Lead Name & ID</span>
                                        @if ($currentSort === 'name')
                                            <span>{{ $currentDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="py-2 px-3">City</th>
                                <th class="py-2 px-3">Loan Product</th>
                                <th class="py-2 px-3">
                                    <a href="{{ $sortToggle('status') }}" class="text-decoration-none text-muted d-inline-flex align-items-center gap-1">
                                        <span>Current Status</span>
                                        @if ($currentSort === 'status')
                                            <span>{{ $currentDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="py-2 px-3">
                                    <a href="{{ $sortToggle('assigned_at') }}" class="text-decoration-none text-muted d-inline-flex align-items-center gap-1">
                                        <span>Assigned Date</span>
                                        @if ($currentSort === 'assigned_at' || $currentSort === 'assigned_date')
                                            <span>{{ $currentDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="py-2 px-3">
                                    <a href="{{ $sortToggle('deadline') }}" class="text-decoration-none text-muted d-inline-flex align-items-center gap-1">
                                        <span>Deadline</span>
                                        @if ($currentSort === 'deadline')
                                            <span>{{ $currentDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="py-2 px-3">
                                    <a href="{{ $sortToggle('pending_count') }}" class="text-decoration-none text-muted d-inline-flex align-items-center gap-1">
                                        <span>Pending Items</span>
                                        @if ($currentSort === 'pending_count')
                                            <span>{{ $currentDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="py-2 px-3">Last Activity</th>
                                <th class="py-2 px-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tableLeads as $lead)
                                @php
                                    $cfg = $statusConfig[$lead->status] ?? ['label' => ucfirst(str_replace('_', ' ', $lead->status)), 'badge' => 'jb-badge-slate'];
                                    $latestAct = $lead->activities->first();
                                    $pendingCount = $leadPendencyMap[$lead->id]['pending'] ?? 0;

                                    $hasLeadDeadline = $hasDeadlineColumn && !empty($lead->deadline);
                                    $isOverdue = false;
                                    $isDueToday = false;
                                    if ($hasLeadDeadline) {
                                        $dObj = \Illuminate\Support\Carbon::parse($lead->deadline);
                                        $isOverdue = $dObj->isPast() && !in_array($lead->status, ['completed', 'rejected', 'cancelled', 'lost']);
                                        $isDueToday = $dObj->isToday() && !in_array($lead->status, ['completed', 'rejected', 'cancelled', 'lost']);
                                    }

                                    $rowStyle = '';
                                    if ($isOverdue) {
                                        $rowStyle = 'background:rgba(244, 63, 94, 0.04);border-left:3px solid var(--rose-500);';
                                    } elseif ($isDueToday) {
                                        $rowStyle = 'background:rgba(245, 158, 11, 0.04);border-left:3px solid var(--amber-500);';
                                    }
                                @endphp
                                <tr style="border-top:1px solid var(--slate-100);{{ $rowStyle }}">
                                    {{-- Lead Name & ID --}}
                                    <td class="py-2 px-3">
                                        <div class="d-flex flex-column">
                                            <a href="{{ route('admin.leads.show', $lead) }}"
                                               class="fw-bold text-decoration-none text-truncate"
                                               style="color:var(--slate-900);max-width:200px"
                                               title="View {{ $lead->name }} details">
                                                {{ $lead->name }}
                                            </a>
                                            <span style="font-size:.6875rem;color:var(--slate-400)">#LD-{{ str_pad($lead->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                    </td>

                                    {{-- City --}}
                                    <td class="py-2 px-3 fw-medium" style="color:var(--slate-700)">
                                        {{ $lead->city?->name ?: '—' }}
                                    </td>

                                    {{-- Loan Product --}}
                                    <td class="py-2 px-3">
                                        <span class="jb-badge jb-badge-xs jb-badge-slate">{{ $lead->loanProduct?->name ?: '—' }}</span>
                                    </td>

                                    {{-- Current Status --}}
                                    <td class="py-2 px-3">
                                        <span class="jb-badge jb-badge-xs {{ $cfg['badge'] }}">{{ $cfg['label'] }}</span>
                                    </td>

                                    {{-- Assigned Date --}}
                                    <td class="py-2 px-3" style="color:var(--slate-600)">
                                        {{ $lead->assigned_at ? $lead->assigned_at->format('d M Y') : ($lead->created_at ? $lead->created_at->format('d M Y') : '—') }}
                                    </td>

                                    {{-- Deadline --}}
                                    <td class="py-2 px-3">
                                        @if ($hasLeadDeadline)
                                            @php $dlParsed = \Illuminate\Support\Carbon::parse($lead->deadline); @endphp
                                            @if ($isOverdue)
                                                <span class="jb-badge jb-badge-xs jb-badge-rose d-inline-flex align-items-center gap-1" title="Overdue deadline">
                                                    <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ $dlParsed->format('d M Y') }}
                                                </span>
                                                <span class="d-block text-danger fw-bold" style="font-size:.625rem">Overdue</span>
                                            @elseif ($isDueToday)
                                                <span class="jb-badge jb-badge-xs jb-badge-amber d-inline-flex align-items-center gap-1" title="Due today">
                                                    <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ $dlParsed->format('d M Y') }}
                                                </span>
                                                <span class="d-block text-warning fw-bold" style="font-size:.625rem">Due Today</span>
                                            @else
                                                <span style="color:var(--slate-700)">{{ $dlParsed->format('d M Y') }}</span>
                                            @endif
                                        @else
                                            <span style="color:var(--slate-400)">—</span>
                                        @endif
                                    </td>

                                    {{-- Pending Items Count --}}
                                    <td class="py-2 px-3">
                                        @if ($pendingCount > 0)
                                            <span class="jb-badge jb-badge-xs jb-badge-rose">{{ $pendingCount }} pending</span>
                                        @else
                                            <span class="jb-badge jb-badge-xs jb-badge-emerald">All Complete</span>
                                        @endif
                                    </td>

                                    {{-- Last Activity --}}
                                    <td class="py-2 px-3" style="color:var(--slate-500)">
                                        @if ($latestAct)
                                            <span class="d-block text-truncate" style="max-width:160px;font-size:.6875rem" title="{{ $latestAct->description }}">
                                                {{ $latestAct->description }}
                                            </span>
                                            <span style="font-size:.625rem;color:var(--slate-400)">{{ $latestAct->created_at->diffForHumans() }}</span>
                                        @else
                                            <span style="color:var(--slate-400);font-size:.6875rem">{{ $lead->updated_at ? $lead->updated_at->diffForHumans() : '—' }}</span>
                                        @endif
                                    </td>

                                    {{-- Action --}}
                                    <td class="py-2 px-3 text-end">
                                        <a href="{{ route('admin.leads.show', $lead) }}"
                                           class="btn btn-outline-primary btn-sm py-1 px-2"
                                           style="font-size:var(--text-xs)"
                                           title="Open Lead Details">
                                            View Lead
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links --}}
                @if ($tableLeads->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2">
                        <span style="font-size:var(--text-xs);color:var(--slate-500)">
                            Showing {{ $tableLeads->firstItem() }} to {{ $tableLeads->lastItem() }} of {{ $tableLeads->total() }} leads
                        </span>
                        <div>
                            {{ $tableLeads->fragment('my-leads-table-section')->links() }}
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- 3. Upcoming Deadlines & 4. My Pending Work Widgets --}}
        <div class="row g-4 mb-4">
            {{-- 3. Upcoming Deadlines Widget --}}
            <div class="col-12 col-xl-6">
                <div class="jb-card p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h3 class="jb-section-title m-0">Upcoming Deadlines</h3>
                                <p class="mb-0 mt-1" style="font-size:var(--text-xs);color:var(--slate-500)">
                                    Overdue, today, and next 7 days deadline tracking.
                                </p>
                            </div>
                            <div class="d-flex gap-1">
                                @if ($overdueLeads->isNotEmpty())
                                    <span class="jb-badge jb-badge-xs jb-badge-rose">{{ $overdueLeads->count() }} Overdue</span>
                                @endif
                                @if ($dueTodayLeads->isNotEmpty())
                                    <span class="jb-badge jb-badge-xs jb-badge-amber">{{ $dueTodayLeads->count() }} Today</span>
                                @endif
                            </div>
                        </div>

                        {{-- If no deadline field exists on leads table, report gap --}}
                        @if (! $hasDeadlineField)
                            <div class="p-3 rounded-3 mb-3" style="background:var(--amber-50);border:1px solid var(--amber-200);color:var(--amber-900)">
                                <div class="d-flex align-items-center gap-2">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--amber-700);flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <strong style="font-size:var(--text-xs)">Deadline Field Gap Notice</strong>
                                </div>
                                <p class="mt-1 mb-0" style="font-size:var(--text-xs);color:var(--amber-800)">
                                    The <code>leads</code> table in this database has no <code>deadline</code> column. Deadlines will automatically appear here once authorized and added to the schema.
                                </p>
                            </div>
                            <div class="text-center py-4 text-muted" style="font-size:var(--text-sm)">
                                No overdue or upcoming deadlines.
                            </div>
                        @else
                            @php
                                $hasDeadlines = $overdueLeads->isNotEmpty() || $dueTodayLeads->isNotEmpty() || $upcoming7DaysLeads->isNotEmpty();
                            @endphp

                            @if (! $hasDeadlines)
                                <div class="text-center py-5 text-muted" style="font-size:var(--text-sm)">
                                    No overdue or upcoming deadlines.
                                </div>
                            @else
                                <div class="d-flex flex-column gap-2" style="max-height:360px;overflow-y:auto">
                                    {{-- Overdue Leads --}}
                                    @foreach ($overdueLeads as $l)
                                        @php
                                            $cfg = $statusConfig[$l->status] ?? ['label' => ucfirst(str_replace('_', ' ', $l->status)), 'badge' => 'jb-badge-slate'];
                                            $pCount = $leadPendencyMap[$l->id]['pending'] ?? 0;
                                        @endphp
                                        <div class="d-flex align-items-center justify-content-between p-2 px-3 rounded-2"
                                             style="border:1px solid var(--rose-200);background:var(--rose-50);font-size:var(--text-xs)">
                                            <div>
                                                <a href="{{ route('admin.leads.show', $l) }}" class="fw-bold text-decoration-none text-danger d-block">
                                                    {{ $l->name }}
                                                </a>
                                                <span class="text-muted" style="font-size:.6875rem">
                                                    Deadline: <strong>{{ \Illuminate\Support\Carbon::parse($l->deadline)->format('d M Y') }}</strong>
                                                    &middot; <span class="text-danger fw-semibold">Overdue</span>
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="jb-badge jb-badge-xs {{ $cfg['badge'] }}">{{ $cfg['label'] }}</span>
                                                <span class="jb-badge jb-badge-xs jb-badge-rose">{{ $pCount }} pending</span>
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Due Today Leads --}}
                                    @foreach ($dueTodayLeads as $l)
                                        @php
                                            $cfg = $statusConfig[$l->status] ?? ['label' => ucfirst(str_replace('_', ' ', $l->status)), 'badge' => 'jb-badge-slate'];
                                            $pCount = $leadPendencyMap[$l->id]['pending'] ?? 0;
                                        @endphp
                                        <div class="d-flex align-items-center justify-content-between p-2 px-3 rounded-2"
                                             style="border:1px solid var(--amber-200);background:var(--amber-50);font-size:var(--text-xs)">
                                            <div>
                                                <a href="{{ route('admin.leads.show', $l) }}" class="fw-bold text-decoration-none text-dark d-block">
                                                    {{ $l->name }}
                                                </a>
                                                <span class="text-muted" style="font-size:.6875rem">
                                                    Deadline: <strong>Today ({{ \Illuminate\Support\Carbon::parse($l->deadline)->format('d M Y') }})</strong>
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="jb-badge jb-badge-xs {{ $cfg['badge'] }}">{{ $cfg['label'] }}</span>
                                                <span class="jb-badge jb-badge-xs jb-badge-amber">{{ $pCount }} pending</span>
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Next 7 Days Leads --}}
                                    @foreach ($upcoming7DaysLeads as $l)
                                        @php
                                            $cfg = $statusConfig[$l->status] ?? ['label' => ucfirst(str_replace('_', ' ', $l->status)), 'badge' => 'jb-badge-slate'];
                                            $pCount = $leadPendencyMap[$l->id]['pending'] ?? 0;
                                        @endphp
                                        <div class="d-flex align-items-center justify-content-between p-2 px-3 rounded-2"
                                             style="border:1px solid var(--slate-200);background:var(--slate-50);font-size:var(--text-xs)">
                                            <div>
                                                <a href="{{ route('admin.leads.show', $l) }}" class="fw-bold text-decoration-none text-dark d-block">
                                                    {{ $l->name }}
                                                </a>
                                                <span class="text-muted" style="font-size:.6875rem">
                                                    Due: {{ \Illuminate\Support\Carbon::parse($l->deadline)->format('d M Y') }} (in {{ \Illuminate\Support\Carbon::parse($l->deadline)->diffForHumans() }})
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="jb-badge jb-badge-xs {{ $cfg['badge'] }}">{{ $cfg['label'] }}</span>
                                                <span class="jb-badge jb-badge-xs jb-badge-slate">{{ $pCount }} pending</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- 4. My Pending Work Widget (Grouped by Lead and Module) --}}
            <div class="col-12 col-xl-6">
                <div class="jb-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h3 class="jb-section-title m-0">My Pending Work</h3>
                            <p class="mb-0 mt-1" style="font-size:var(--text-xs);color:var(--slate-500)">
                                Pending tasks and required documents grouped by Lead & module.
                            </p>
                        </div>
                        <span class="jb-badge jb-badge-xs jb-badge-rose">{{ $leadStats['pending_work_count'] }} Pending Items</span>
                    </div>

                    @if (empty($myPendingWorkByLead))
                        <div class="text-center py-5 text-muted" style="font-size:var(--text-sm)">
                            <div class="mb-2">
                                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--emerald-500)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            All caught up! No pending work items for your assigned leads.
                        </div>
                    @else
                        <div class="d-flex flex-column gap-2" style="max-height:380px;overflow-y:auto">
                            @foreach ($myPendingWorkByLead as $item)
                                @php
                                    $pLead = $item['lead'];
                                @endphp
                                <details class="p-2 px-3 rounded-2 border" style="background:var(--slate-50);border-color:var(--slate-200)!important" open>
                                    <summary class="d-flex align-items-center justify-content-between cursor-pointer" style="font-size:var(--text-xs);cursor:pointer;list-style:none">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold" style="color:var(--slate-900)">{{ $pLead->name }}</span>
                                            <span style="font-size:.6875rem;color:var(--slate-400)">#LD-{{ str_pad($pLead->id, 5, '0', STR_PAD_LEFT) }}</span>
                                            @if ($pLead->city)
                                                <span class="jb-badge jb-badge-xs jb-badge-slate">{{ $pLead->city->name }}</span>
                                            @endif
                                        </div>
                                        <span class="jb-badge jb-badge-xs jb-badge-rose">{{ $item['total_pending'] }} pending</span>
                                    </summary>

                                    <div class="mt-2 pt-2 border-top d-flex flex-column gap-2" style="font-size:var(--text-xs)">
                                        @foreach ($item['modules'] as $mod)
                                            <div class="p-2 rounded bg-white" style="border:1px solid var(--slate-200)">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="fw-semibold" style="color:var(--slate-800)">{{ $mod['label'] }}</span>
                                                    <span class="text-danger fw-semibold" style="font-size:.6875rem">{{ $mod['pending_count'] }} pending</span>
                                                </div>

                                                <ul class="list-unstyled m-0 d-flex flex-column gap-1">
                                                    @foreach ($mod['subsections'] as $sub)
                                                        @if ($sub['status'] === 'pending')
                                                            @php
                                                                $targetUrl = route('admin.leads.show', $pLead) . ($sub['anchor'] ?? '');
                                                                if (auth()->user()->can('leads.edit')) {
                                                                    $targetUrl = route('admin.leads.edit', $pLead) . ($sub['anchor'] ?? '');
                                                                }
                                                            @endphp
                                                            <li class="d-flex align-items-center justify-content-between ps-2 py-1 rounded" style="background:var(--slate-50)">
                                                                <span class="d-flex align-items-center gap-2">
                                                                    <span class="d-inline-block rounded-circle" style="width:6px;height:6px;background:var(--rose-500)"></span>
                                                                    <span>{{ $sub['label'] }}</span>
                                                                    @if (!empty($sub['note']))
                                                                        <span class="text-muted" style="font-size:.625rem">({{ $sub['note'] }})</span>
                                                                    @endif
                                                                </span>
                                                                <a href="{{ $targetUrl }}"
                                                                   class="btn btn-outline-secondary btn-sm py-0 px-2"
                                                                   style="font-size:.625rem">
                                                                    Complete &rarr;
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 6. Workload and Performance Widgets --}}
        <div class="row g-4 mb-4">
            {{-- Leads by Status --}}
            <div class="col-12 col-lg-5">
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
                                <div style="position:relative;width:160px;height:160px">
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

            {{-- Pending Work by Module --}}
            <div class="col-12 col-lg-4">
                <div class="jb-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="jb-section-title m-0">Pending Work by Module</h3>
                        <span class="jb-badge jb-badge-xs jb-badge-purple">{{ count($pendingByModule) }} Modules</span>
                    </div>

                    @if (empty($pendingByModule))
                        <div class="text-center py-5 text-muted" style="font-size:var(--text-sm)">
                            No pending tasks across all modules!
                        </div>
                    @else
                        @php
                            $maxPending = max(1, max($pendingByModule));
                        @endphp
                        <div class="d-flex flex-column gap-3" style="max-height:220px;overflow-y:auto">
                            @foreach ($pendingByModule as $modName => $count)
                                @php
                                    $modPct = round(($count / $maxPending) * 100);
                                @endphp
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:var(--text-xs)">
                                        <span class="fw-semibold text-truncate" style="color:var(--slate-700)">{{ $modName }}</span>
                                        <span class="jb-badge jb-badge-xs jb-badge-purple">{{ $count }} pending</span>
                                    </div>
                                    <div class="jb-progress-track" style="height:5px">
                                        <div class="jb-progress-fill" style="width:{{ $modPct }}%;background:var(--purple-600)"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Performance & Completion Tracking --}}
            <div class="col-12 col-lg-3">
                <div class="jb-card p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h3 class="jb-section-title mb-3">Performance</h3>
                        <div class="p-3 rounded-3 mb-3" style="background:var(--slate-50);border:1px solid var(--slate-200)">
                            <span class="d-block text-muted" style="font-size:var(--text-xs)">Completed This Month</span>
                            <div class="d-flex align-items-baseline justify-content-between mt-1">
                                <span class="fw-bold" style="font-size:var(--text-2xl);color:var(--emerald-600)">{{ $leadStats['completed_this_month'] }}</span>
                                <span class="jb-badge jb-badge-xs jb-badge-emerald">This Month</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-3" style="background:var(--slate-50);border:1px solid var(--slate-200)">
                            <span class="d-block text-muted" style="font-size:var(--text-xs)">Total Completed</span>
                            <div class="d-flex align-items-baseline justify-content-between mt-1">
                                <span class="fw-bold" style="font-size:var(--text-xl);color:var(--slate-700)">{{ $leadStats['completed_leads'] }}</span>
                                <span class="jb-badge jb-badge-xs jb-badge-slate">Lifetime</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 pt-2 border-top" style="font-size:.6875rem;color:var(--slate-500)">
                        <strong>Historical On-Time vs Overdue:</strong>
                        <p class="mb-0 mt-1" style="font-size:.625rem;color:var(--slate-400)">
                            Not tracked because the database does not have a historical deadline field.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Preserved: City-wise Lead Status Dashboard Matrix (Assigned Leads Only) --}}
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

                                    {{-- Status Breakdown List --}}
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

        {{-- Preserved: Assigned Leads by City Chart Card --}}
        <div class="row g-4 mb-4">
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
                                <div style="position:relative;width:160px;height:160px">
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

            {{-- Preserved: Upcoming Follow-ups --}}
            <div class="col-12 col-lg-6">
                <div class="jb-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="jb-section-title m-0">Upcoming Follow-ups</h3>
                        <span class="jb-badge jb-badge-xs jb-badge-amber">{{ $upcomingFollowUps->count() }} Scheduled</span>
                    </div>

                    @if ($upcomingFollowUps->isEmpty())
                        <div class="text-center py-5 text-muted" style="font-size:var(--text-sm)">No upcoming follow-ups scheduled.</div>
                    @else
                        <div class="d-flex flex-column gap-2" style="max-height:220px;overflow-y:auto">
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
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 5. Recent Activity Stream --}}
        <div class="jb-card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="jb-section-title m-0">Recent Activity</h3>
                    <p class="mb-0 mt-1" style="font-size:var(--text-xs);color:var(--slate-500)">Latest updates logged across your assigned Leads.</p>
                </div>
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
                                    <span>Date: {{ $act->created_at->format('d M Y, h:i A') }}</span>
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
