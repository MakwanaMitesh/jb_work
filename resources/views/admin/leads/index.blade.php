<x-admin-bootstrap-layout title="Leads" :white-bg="true">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.leads.index') }}">Leads</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">List</span>
        </nav>

        <div class="d-flex align-items-center justify-content-between">
            <h1 class="jb-page-title">Leads</h1>
            @can('leads.create')
                <a href="{{ route('admin.leads.create') }}" class="btn btn-primary">
                    New Lead
                </a>
            @endcan
        </div>
    </div>

    @if (request('city') || request('city_id') || request('status') || request('assigned_employee_id') || request('search'))
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2 px-3 mb-3 rounded-3" style="background:var(--slate-100);border:1px solid var(--slate-200);font-size:var(--text-xs)">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="fw-bold" style="color:var(--slate-700)">Active Filter:</span>
                @if ($activeCityParam = (request('city_id') ?? request('city')))
                    @php
                        $cityNameDisplay = $activeCityParam === 'unassigned' ? 'Unassigned' : ($cities->firstWhere('id', $activeCityParam)?->name ?? ucfirst($activeCityParam));
                    @endphp
                    <span class="jb-badge jb-badge-xs jb-badge-primary">City: {{ $cityNameDisplay }}</span>
                @endif
                @if ($activeStatus = request('status'))
                    @php
                        $statusNorm = strtolower(str_replace(' ', '_', $activeStatus));
                        $statusText = \App\Models\Lead::STATUSES[$statusNorm] ?? ucfirst($activeStatus);
                    @endphp
                    <span class="jb-badge jb-badge-xs jb-badge-blue">Status: {{ $statusText }}</span>
                @endif
                @if ($activeEmp = request('assigned_employee_id'))
                    <span class="jb-badge jb-badge-xs jb-badge-indigo">Assigned Rep</span>
                @endif
                @if ($searchVal = request('search'))
                    <span class="jb-badge jb-badge-xs jb-badge-slate">Search: "{{ $searchVal }}"</span>
                @endif
            </div>
            <a href="{{ route('admin.leads.index') }}" class="jb-file-link" style="font-size:var(--text-xs)">Clear Filters</a>
        </div>
    @endif

    <x-datatable-card-bs tableId="leads-table" searchPlaceholder="Search">
        <x-slot:filters>
            <div>
                <label class="jb-form-label">Status</label>
                <select data-filter-column="4" data-filter-type="regex" class="form-select form-select-sm" name="status">
                    <option value="">All</option>
                    @foreach(\App\Models\Lead::STATUSES as $stKey => $stLabel)
                        @php
                            $selectedStatus = request('status');
                            $isSelected = $selectedStatus && (
                                strtolower($selectedStatus) === strtolower($stKey) ||
                                strtolower(str_replace(' ', '_', $selectedStatus)) === strtolower($stKey) ||
                                strtolower($selectedStatus) === strtolower($stLabel)
                            );
                        @endphp
                        <option value="{{ $stLabel }}" {{ $isSelected ? 'selected' : '' }}>{{ $stLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="jb-form-label">City</label>
                <select data-filter-column="2" class="form-select form-select-sm" name="city">
                    <option value="">All</option>
                    @php
                        $selectedCity = request('city_id') ?? request('city');
                    @endphp
                    @foreach ($cities as $city)
                        @php
                            $isSelected = $selectedCity && (
                                (string)$selectedCity === (string)$city->id ||
                                strtolower($selectedCity) === strtolower($city->name)
                            );
                        @endphp
                        <option value="{{ $city->name }}" {{ $isSelected ? 'selected' : '' }}>{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="jb-form-label">Assigned Agent</label>
                <select data-filter-column="3" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($agents as $agent)
                        <option value="{{ $agent->name }}">{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="jb-form-label">Loan Product</label>
                <select data-filter-column="6" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($loanProducts as $product)
                        <option value="{{ $product->name }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="jb-form-label">Constitution</label>
                <select data-filter-column="7" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($constitutions as $const)
                        <option value="{{ $const->name }}">{{ $const->name }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>

        <x-slot:thead>
            <th>Customer Name</th>
            <th>Mobile Number</th>
            <th data-col="city">City</th>
            <th data-col="agent">Assigned Agent</th>
            <th data-col="status">Status</th>
            <th data-col="created">Created Date</th>
            <th class="text-end" style="width:4rem"></th>
        </x-slot:thead>

        @forelse ($leads as $lead)
            <tr>
                <td class="jb-cell-strong">
                    <div class="d-flex align-items-center gap-3">
                        <span class="jb-avatar">LD</span>
                        <span>{{ $lead->name }}</span>
                    </div>
                </td>
                <td>{{ $lead->mobile_number }}</td>
                <td data-col="city">{{ $lead->city?->name ?? '—' }}</td>
                <td data-col="agent">{{ $lead->agent?->name ?? '—' }}</td>
                <td data-col="status">
                    @php
                        $badge = match($lead->status) {
                            'new' => 'jb-badge-blue',
                            'visit_pending' => 'jb-badge-amber',
                            'visit_completed' => 'jb-badge-emerald',
                            'documentation_pending' => 'jb-badge-indigo',
                            'documentation_in_progress' => 'jb-badge-purple',
                            'documentation_completed' => 'jb-badge-teal',
                            'under_process' => 'jb-badge-cyan',
                            'approved' => 'jb-badge-emerald',
                            'rejected' => 'jb-badge-rose',
                            'completed' => 'jb-badge-green',
                            'cancelled' => 'jb-badge-slate',
                            default => 'jb-badge-slate'
                        };
                    @endphp
                    <span class="jb-badge {{ $badge }}">
                        {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                    </span>
                </td>
                <td data-col="created" data-order="{{ $lead->created_at?->timestamp ?? 0 }}">{{ $lead->created_at?->format('M d, Y') ?? '—' }}</td>
                <td class="text-end">
                    <div class="dropdown">
                        <button class="jb-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" /></svg>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end text-start text-nowrap" style="min-width:13rem">
                            <li>
                                <a href="{{ route('admin.leads.show', $lead) }}" class="dropdown-item">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>View details</span>
                                </a>
                            </li>

                            @can('leads.edit')
                                <li>
                                    <a href="{{ route('admin.leads.edit', $lead) }}" class="dropdown-item">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        <span>Edit Lead</span>
                                    </a>
                                </li>
                            @endcan

                            @canany(['cma.generate', 'cma.download', 'dpr.generate', 'dpr.download', 'or.generate', 'or.download', 'sbi_stock_statement.generate', 'sbi_stock_statement.download', 'inspection_sheet.generate', 'inspection_sheet.download', 'documents.download_all'])
                                <li><hr class="dropdown-divider"></li>
                            @endcanany

                            @can('cma.generate')
                                <li>
                                    <form method="POST" action="{{ route('admin.leads.cma.generate', $lead) }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                            <span>Generate CMA</span>
                                        </button>
                                    </form>
                                </li>
                            @endcan
                            @can('cma.download')
                                <li>
                                    <a href="{{ route('admin.leads.cma.download', $lead) }}" class="dropdown-item">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Download CMA</span>
                                    </a>
                                </li>
                            @endcan

                            @can('dpr.generate')
                                <li>
                                    <form method="POST" action="{{ route('admin.leads.dpr.generate', $lead) }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                            <span>Generate DPR</span>
                                        </button>
                                    </form>
                                </li>
                            @endcan
                            @can('dpr.download')
                                <li>
                                    <a href="{{ route('admin.leads.dpr.download', $lead) }}" class="dropdown-item">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Download DPR</span>
                                    </a>
                                </li>
                            @endcan

                            @can('or.generate')
                                <li>
                                    <form method="POST" action="{{ route('admin.leads.or.generate', $lead) }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                            <span>Generate OR</span>
                                        </button>
                                    </form>
                                </li>
                            @endcan
                            @can('or.download')
                                <li>
                                    <a href="{{ route('admin.leads.or.download', $lead) }}" class="dropdown-item">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Download OR</span>
                                    </a>
                                </li>
                            @endcan

                            @can('sbi_stock_statement.generate')
                                <li>
                                    <form method="POST" action="{{ route('admin.leads.sbi-stock-statement.generate', $lead) }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                            <span>Generate SBI Stock Statement</span>
                                        </button>
                                    </form>
                                </li>
                            @endcan
                            @can('sbi_stock_statement.download')
                                <li>
                                    <a href="{{ route('admin.leads.sbi-stock-statement.download', $lead) }}" class="dropdown-item">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Download SBI Stock Statement</span>
                                    </a>
                                </li>
                            @endcan

                            @if ($lead->isInspectionSheetApplicable())
                                @can('inspection_sheet.generate')
                                    <li>
                                        <form method="POST" action="{{ route('admin.leads.inspection-sheet.generate', $lead) }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                <span>Generate Inspection Sheet</span>
                                            </button>
                                        </form>
                                    </li>
                                @endcan
                                @can('inspection_sheet.download')
                                    <li>
                                        <a href="{{ route('admin.leads.inspection-sheet.pdf', $lead) }}" target="_blank" class="dropdown-item">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Download Inspection Sheet (PDF)</span>
                                        </a>
                                    </li>
                                @endcan
                            @endif

                            @can('documents.download_all')
                                <li>
                                    <a href="{{ route('admin.leads.uploaded-documents.download-all', $lead) }}" class="dropdown-item">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                                        <span>Download Uploaded Docs (ZIP)</span>
                                    </a>
                                </li>
                            @endcan

                            @canany(['cma.download', 'dpr.download', 'or.download', 'sbi_stock_statement.download', 'inspection_sheet.download', 'documents.download'])
                                <li>
                                    <a href="{{ route('admin.leads.download-all', $lead) }}" class="dropdown-item">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span>Download Generated Reports (ZIP)</span>
                                    </a>
                                </li>
                            @endcanany

                            @can('leads.delete')
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}" onsubmit="return confirm('Are you sure you want to delete this lead?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item dropdown-item-danger">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
                <td colspan="9" class="text-center py-5" style="color:var(--slate-400)">No leads found.</td>
            </tr>
        @endforelse
    </x-datatable-card-bs>
</x-admin-bootstrap-layout>
