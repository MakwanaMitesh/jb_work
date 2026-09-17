<x-admin-layout :title="$lead->name">
    <!-- Filament-style Page Header -->
    <div class="flex flex-col gap-4 mb-6">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500 text-xs font-medium">
            <a href="{{ route('admin.leads.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300">Leads</a>
            <svg class="w-3 h-3 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="text-slate-900 dark:text-slate-200">View</span>
        </nav>

        <!-- Header content and actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white leading-none">{{ $lead->name }}</h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">
                        #LD-{{ str_pad($lead->id, 5, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $lead->mobile_number }}
                    </span>
                    @if ($lead->email)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $lead->email }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.leads.inspection-sheet.pdf', $lead) }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold rounded-lg shadow-sm transition no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Inspection Sheet (PDF)
                </a>
                @can('leads.edit')
                    <a href="{{ route('admin.leads.edit', $lead) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-semibold rounded-lg shadow-sm transition no-underline">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Edit
                    </a>
                @endcan
                <a href="{{ route('admin.leads.index') }}" class="inline-flex items-center justify-center px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg font-semibold text-xs text-slate-750 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm no-underline">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Filament-style Header Widgets (Summary Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <!-- Status Widget -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm flex flex-col justify-between min-h-[90px]">
            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider font-semibold">Status</span>
            <div class="flex items-center justify-between mt-2">
                @php
                    $color = match($lead->status) {
                        'new' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400 border border-blue-200/30 dark:border-blue-900/30',
                        'visit_pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 border border-amber-200/30 dark:border-amber-900/30',
                        'visit_completed' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 border border-emerald-200/30 dark:border-emerald-900/30',
                        'documentation_pending' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/20 dark:text-indigo-400 border border-indigo-200/30 dark:border-indigo-900/30',
                        'documentation_in_progress' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/20 dark:text-purple-400 border border-purple-200/30 dark:border-purple-900/30',
                        'documentation_completed' => 'bg-teal-50 text-teal-700 dark:bg-teal-950/20 dark:text-teal-400 border border-teal-200/30 dark:border-teal-900/30',
                        'under_process' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/20 dark:text-cyan-400 border border-cyan-200/30 dark:border-cyan-900/30',
                        'approved' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/40 dark:border-emerald-900/40',
                        'rejected' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/20 dark:text-rose-400 border border-rose-200/30 dark:border-rose-900/30',
                        'completed' => 'bg-green-100 text-green-800 dark:bg-green-950/30 dark:text-green-400 border border-green-200/40 dark:border-green-900/40',
                        'cancelled' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border border-slate-200/30 dark:border-slate-700/30',
                        default => 'bg-slate-100 text-slate-700'
                    };
                @endphp
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $color }}">
                    {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                </span>
                @can('leads.change_status')
                    <form action="{{ route('admin.leads.status', $lead) }}" method="POST" class="inline">
                        @csrf
                        <select name="status" onchange="this.form.submit()" class="text-[10px] rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 py-1 pl-1 pr-6 focus:ring-1 focus:ring-primary-500 select2">
                            <option value="" disabled selected>Change Status...</option>
                            @foreach(['new', 'visit_pending', 'visit_completed', 'documentation_pending', 'documentation_in_progress', 'documentation_completed', 'under_process', 'approved', 'rejected', 'completed', 'cancelled'] as $st)
                                @if($lead->status !== $st)
                                    <option value="{{ $st }}">{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                                @endif
                            @endforeach
                        </select>
                    </form>
                @endcan
            </div>
        </div>

        <!-- Bank Widget -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm flex flex-col justify-between min-h-[90px]">
            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider font-semibold">Bank</span>
            <span class="text-sm font-bold text-slate-900 dark:text-white mt-1">{{ $lead->bank?->name ?: '—' }}</span>
        </div>

        <!-- Product Widget -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm flex flex-col justify-between min-h-[90px]">
            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider font-semibold">Loan Product</span>
            <span class="text-sm font-bold text-slate-900 dark:text-white mt-1">{{ $lead->loanProduct?->name ?: '—' }}</span>
        </div>

        <!-- Constitution Widget -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm flex flex-col justify-between min-h-[90px]">
            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider font-semibold">Constitution</span>
            <span class="text-sm font-bold text-slate-900 dark:text-white mt-1">{{ $lead->constitution?->name ?: $lead->constitution_of_business ?: '—' }}</span>
        </div>

        <!-- Assigned Widget -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm flex flex-col justify-between min-h-[90px]">
            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider font-semibold">Representative</span>
            <span class="text-sm font-bold text-slate-900 dark:text-white mt-1">{{ $lead->assignedEmployee?->name ?: 'Unassigned' }}</span>
        </div>

        <!-- Agent Widget -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm flex flex-col justify-between min-h-[90px]">
            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider font-semibold">Referral Source</span>
            <span class="text-sm font-bold text-slate-900 dark:text-white mt-1">{{ $lead->agent?->name ?: 'Direct Client' }}</span>
        </div>
    </div>

    <!-- Tab Section Container -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden mb-6">
        <!-- Tab Navigation Bar -->
        <div class="bg-slate-50/50 dark:bg-slate-950/25 border-b border-slate-200/80 dark:border-slate-800 px-6 py-2.5 flex flex-wrap gap-1">
            <button type="button" onclick="switchTab('overview-tab', 'overview-panel')" id="overview-tab" class="tab-btn flex items-center px-4 py-2 bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 font-semibold text-sm rounded-lg shadow-sm border border-primary-600 dark:border-primary-500 focus:outline-none transition">
                Workflow & Requested Loan
            </button>
            <button type="button" onclick="switchTab('kyc-tab', 'kyc-panel')" id="kyc-tab" class="tab-btn flex items-center px-4 py-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium text-sm rounded-lg transition focus:outline-none">
                KYC & Personal Details
            </button>
            @if (!empty($lead->bank_loan_details))
                <button type="button" onclick="switchTab('bank-loan-tab', 'bank-loan-panel')" id="bank-loan-tab" class="tab-btn flex items-center px-4 py-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium text-sm rounded-lg transition focus:outline-none">
                    Bank Loan Report
                </button>
            @endif
            <button type="button" onclick="switchTab('business-tab', 'business-panel')" id="business-tab" class="tab-btn flex items-center px-4 py-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium text-sm rounded-lg transition focus:outline-none">
                Business Profile
            </button>
            <button type="button" onclick="switchTab('finance-tab', 'finance-panel')" id="finance-tab" class="tab-btn flex items-center px-4 py-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium text-sm rounded-lg transition focus:outline-none">
                Financials & Active Liabilities
            </button>
            @php
                $uploadedDocCount = $lead->leadDocuments->count();
            @endphp
            <button type="button" onclick="switchTab('documents-tab', 'documents-panel')" id="documents-tab" class="tab-btn flex items-center gap-1.5 px-4 py-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium text-sm rounded-lg transition focus:outline-none">
                <span>Documents</span>
                @if ($uploadedDocCount > 0)
                    <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-primary-100 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300">
                        {{ $uploadedDocCount }}
                    </span>
                @endif
            </button>
        </div>

        <div class="p-6">
            
            <!-- PANEL 1: Overview & Workflow -->
            <div id="overview-panel" class="tab-panel">
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                    <!-- Left Column: Requested Loan + Visits -->
                    <div class="xl:col-span-2 space-y-6">

                        <!-- Requested Financing -->
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-2.5 mb-5">Requested Financing Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                                <div class="space-y-1">
                                    <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Required Loan Amount</span>
                                    <span class="block text-base font-bold text-primary-600 dark:text-primary-400">{{ $lead->required_loan_amount ?: '—' }}</span>
                                </div>
                                <div class="space-y-1">
                                    <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">CC Amount</span>
                                    <span class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $lead->cc_amount ?: '—' }}</span>
                                    @if ($lead->cc_details)
                                        <p class="text-xs text-slate-500 mt-1 whitespace-pre-line">{{ $lead->cc_details }}</p>
                                    @endif
                                </div>
                                <div class="space-y-1">
                                    <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Term Loan Amount</span>
                                    <span class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $lead->term_loan_amount ?: '—' }}</span>
                                    @if ($lead->term_loan_machinery_details)
                                        <p class="text-xs text-slate-500 mt-1 whitespace-pre-line">Machinery: {{ $lead->term_loan_machinery_details }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Scheduled & Past Visits -->
                        <div class="space-y-5">
                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Scheduled & Past Visits</h3>
                                @can('visits.create')
                                    <button type="button"
                                        onclick="document.getElementById('scheduleVisitModal').showModal(); if(window.initSelect2){ window.initSelect2('#scheduleVisitModal select'); }"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-lg shadow-sm transition">
                                        + Schedule Visit
                                    </button>
                                @endcan
                            </div>

                            @if ($lead->visits->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach ($lead->visits as $visit)
                                        @php
                                            $visitBadge = match($visit->status) {
                                                'scheduled'   => 'bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800',
                                                'completed'   => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
                                                'cancelled'   => 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-300 border border-rose-200 dark:border-rose-800',
                                                'rescheduled' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
                                                default       => 'bg-slate-100 text-slate-600 border border-slate-200',
                                            };
                                        @endphp
                                        <div class="flex items-start justify-between gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800">
                                            <div class="space-y-2 min-w-0">
                                                <div class="flex items-center gap-2.5 flex-wrap">
                                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $visit->visit_date->format('d M Y, h:i A') }}</span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $visitBadge }}">{{ $visit->status }}</span>
                                                </div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400 space-y-0.5">
                                                    <p>
                                                        <span class="font-medium text-slate-600 dark:text-slate-350">Rep:</span>
                                                        <strong class="text-slate-800 dark:text-slate-200 font-semibold">{{ $visit->employee->name }}</strong>
                                                        <span class="mx-1 text-slate-300">|</span>
                                                        <span class="font-medium text-slate-600 dark:text-slate-350">By:</span> {{ $visit->creator->name }}
                                                    </p>
                                                    <p><span class="font-medium text-slate-600 dark:text-slate-350">Purpose:</span> {{ $visit->purpose }}</p>
                                                    @if ($visit->outcome)
                                                        <p class="text-emerald-600 dark:text-emerald-400"><span class="font-medium">Outcome:</span> {{ $visit->outcome }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            @can('visits.edit')
                                                <button type="button"
                                                    onclick="openEditVisitModal({{ json_encode($visit) }})"
                                                    class="shrink-0 mt-0.5 px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:bg-primary-50 hover:border-primary-300 hover:text-primary-700 dark:hover:bg-primary-950/20 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-300 shadow-sm transition">
                                                    Update
                                                </button>
                                            @endcan
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="py-8 text-center rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                                    <p class="text-sm text-slate-400 dark:text-slate-500">No visits scheduled yet.</p>
                                </div>
                            @endif
                        </div>

                    </div>

                    <!-- Right Column: Assignment + Timeline -->
                    <div class="space-y-4">

                        <!-- Representative Assignment Card -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-950/30">
                                <h4 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Representative Assignment</h4>
                            </div>
                        <div class="p-4 space-y-4">

                            @if ($lead->assignedEmployee)
                                <div class="flex items-start gap-3 p-4 rounded-xl bg-primary-50/60 dark:bg-primary-950/20 border border-primary-100 dark:border-primary-900/40">
                                    <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($lead->assignedEmployee->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $lead->assignedEmployee->name }}</p>
                                        <p class="text-[10px] text-slate-500 mt-0.5">Assigned on {{ $lead->assigned_at?->format('d M Y') }}</p>
                                        @can('leads.assign')
                                            <button type="button" onclick="document.getElementById('reassignFormContainer').classList.toggle('hidden')"
                                                class="mt-2 text-[11px] font-bold text-primary-600 hover:text-primary-500 underline-offset-2 hover:underline transition focus:outline-none">
                                                Change Representative
                                            </button>
                                        @endcan
                                    </div>
                                </div>

                                @can('leads.assign')
                                    <div id="reassignFormContainer" class="hidden">
                                        <form action="{{ route('admin.leads.assign', $lead) }}" method="POST" class="space-y-3 p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                            @csrf
                                            <select name="employee_id" required class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 py-2 select2">
                                                <option value="">Select Employee...</option>
                                                @foreach ($employees as $emp)
                                                    <option value="{{ $emp->id }}" @selected($lead->assigned_employee_id == $emp->id)>{{ $emp->name }}</option>
                                                @endforeach
                                            </select>
                                            <textarea name="notes" rows="2" placeholder="Reassignment notes..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-primary-500"></textarea>
                                            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-lg transition shadow-sm">Confirm Change</button>
                                        </form>
                                    </div>
                                @endcan
                            @else
                                @can('leads.assign')
                                    <form action="{{ route('admin.leads.assign', $lead) }}" method="POST" class="space-y-3 p-4 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-950/20">
                                        @csrf
                                        <p class="text-[11px] font-semibold text-rose-500">⚠ No representative assigned</p>
                                        <select name="employee_id" required class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 py-2 select2">
                                            <option value="">Select Employee...</option>
                                            @foreach ($employees as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                            @endforeach
                                        </select>
                                        <textarea name="notes" rows="2" placeholder="Assignment notes..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-primary-500"></textarea>
                                        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-lg transition shadow-sm">Assign Representative</button>
                                    </form>
                                @else
                                    <div class="p-4 rounded-xl border border-rose-200 dark:border-rose-900/40 bg-rose-50 dark:bg-rose-950/20">
                                        <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold">No representative assigned</span>
                                    </div>
                                @endcan
                            @endif

                            @if ($lead->assignments->isNotEmpty())
                                <div class="pt-3 mt-1 border-t border-slate-100 dark:border-slate-800 space-y-3">
                                    <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-400">Assignment History</span>
                                    @foreach ($lead->assignments->sortByDesc('assigned_at')->take(3) as $history)
                                        <div class="flex items-start justify-between gap-2 text-[11px]">
                                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $history->employee->name }}</span>
                                            <span class="text-[10px] text-slate-400 shrink-0">{{ $history->assigned_at->format('d M Y') }}</span>
                                        </div>
                                        @if ($history->notes)
                                            <p class="text-[10px] text-slate-400 italic -mt-2">"{{ $history->notes }}"</p>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        </div><!-- end card -->

                        <!-- Activity Timeline Card -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-950/30">
                                <h4 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Activity Timeline</h4>
                            </div>
                        <div class="p-4 space-y-4">
                            @if ($lead->activities->isNotEmpty())
                                <div class="relative pl-5 border-l-2 border-slate-100 dark:border-slate-800 space-y-5">
                                    @foreach ($lead->activities->take(8) as $activity)
                                        @php
                                            $dotColor = match($activity->type) {
                                                'created'         => 'bg-blue-500',
                                                'assigned'        => 'bg-indigo-500',
                                                'reassigned'      => 'bg-purple-500',
                                                'status_changed'  => 'bg-amber-500',
                                                'visit_created'   => 'bg-cyan-500',
                                                'visit_completed' => 'bg-emerald-500',
                                                'visit_cancelled' => 'bg-rose-500',
                                                default           => 'bg-slate-400',
                                            };
                                        @endphp
                                        <div class="relative">
                                            <span class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full {{ $dotColor }} ring-2 ring-white dark:ring-slate-950 shadow-sm"></span>
                                            <div class="flex items-center gap-1.5 text-[10px] mb-1" style="color:#94a3b8">
                                                <span class="font-semibold" style="color:#475569">{{ $activity->user->name }}</span>
                                                <span style="color:#cbd5e1">·</span>
                                                <span>{{ $activity->created_at->format('d M, h:i A') }}</span>
                                            </div>
                                            <p class="text-xs font-bold leading-snug" style="color:#1e293b">{{ ucfirst(str_replace('_', ' ', $activity->type)) }}</p>
                                            <p class="text-[11px] mt-0.5 leading-relaxed" style="color:#64748b">{{ $activity->description }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs italic py-2" style="color:#94a3b8">No activity yet.</p>
                            @endif
                        </div>
                        </div><!-- end timeline card -->

                    </div>
                </div>
            </div>

            <!-- PANEL 2: KYC & Personal Details -->
            <div id="kyc-panel" class="tab-panel space-y-8 hidden">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">KYC Profile</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Date of Birth</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->date_of_birth?->format('d M Y') ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Gender</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->gender ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Mother Name</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->mother_name ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5 md:col-span-2">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Personal Address</span>
                            <span class="font-medium text-slate-900 dark:text-white whitespace-pre-line">{{ $lead->address ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Education</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->education ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">KYC Identification Numbers</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-sm">
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Aadhaar Card</span>
                            <span class="font-medium text-slate-900 dark:text-white font-mono">{{ $lead->aadhar_card ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">PAN Card</span>
                            <span class="font-medium text-slate-900 dark:text-white font-mono">{{ $lead->pan_card ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Udyam Registration</span>
                            <span class="font-medium text-slate-900 dark:text-white font-mono">{{ $lead->udyam_registration ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">FSSAI License No.</span>
                            <span class="font-medium text-slate-900 dark:text-white font-mono">{{ $lead->fssai_license ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">ITR Filing & Login Credentials</h3>
                    
                    <!-- Top-Level Credentials -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm bg-slate-50/70 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 mb-4">
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">ITR Portal User ID</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->itr_id ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">ITR Password</span>
                            <span class="font-medium text-slate-900 dark:text-white font-mono">{{ $lead->itr_password ?: '—' }}</span>
                        </div>
                    </div>

                    @php $itrList = $lead->formatted_itr_details; @endphp
                    @if (count($itrList) > 0)
                        <div class="space-y-4">
                            @foreach ($itrList as $idx => $itr)
                                <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">ITR Profile #{{ $idx + 1 }}</div>
                                        @if(!empty($itr['assessment_year']))
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50 text-xs font-semibold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                {{ $itr['assessment_year'] }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div class="space-y-0.5">
                                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">ITR Audited Status</span>
                                            <span class="font-medium text-slate-900 dark:text-white">{{ $itr['itr_audited'] ?? '—' }}</span>
                                        </div>
                                        <div class="space-y-0.5">
                                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Assessment Year</span>
                                            <span class="font-medium text-slate-900 dark:text-white">{{ $itr['assessment_year'] ?? '—' }}</span>
                                        </div>
                                    </div>

                                    <!-- Documents -->
                                    <div class="pt-3 border-t border-slate-200/60 dark:border-slate-800/60">
                                        <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold mb-2">Uploaded ITR Documents</span>
                                        <div class="flex flex-wrap gap-2">
                                            @if(!empty($itr['audit_report']))
                                                <a href="{{ Storage::url($itr['audit_report']) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/30 transition">
                                                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <span>Audit Report</span>
                                                </a>
                                            @endif
                                            @if(!empty($itr['itr_file']))
                                                <a href="{{ Storage::url($itr['itr_file']) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/30 transition">
                                                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <span>ITR File</span>
                                                </a>
                                            @endif
                                            @if(!empty($itr['computation']))
                                                <a href="{{ Storage::url($itr['computation']) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/30 transition">
                                                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <span>Computation</span>
                                                </a>
                                            @endif
                                            @if(!empty($itr['itr_form']))
                                                <a href="{{ Storage::url($itr['itr_form']) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/30 transition">
                                                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <span>ITR Form</span>
                                                </a>
                                            @endif
                                            @if(empty($itr['audit_report']) && empty($itr['itr_file']) && empty($itr['computation']) && empty($itr['itr_form']))
                                                <span class="text-xs text-slate-400 dark:text-slate-500 italic">No document files uploaded for this profile.</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400">No ITR details available.</p>
                    @endif
                </div>
            </div>

            <!-- PANEL 2.5: Bank Loan Verification Report -->
            @if (!empty($lead->bank_loan_details))
                @php $bld = $lead->bank_loan_details; @endphp
                <div id="bank-loan-panel" class="tab-panel space-y-6 hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Bank Loan Verification Report (Annexure V-A)</h3>
                        <a href="{{ route('admin.leads.inspection-sheet.pdf', $lead) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/40 text-xs font-semibold rounded-lg hover:bg-amber-100 transition no-underline">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download Inspection Sheet (PDF)
                        </a>
                    </div>
                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                            <table class="w-full border-collapse text-sm">
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-800 dark:text-slate-200">
                                    <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                        <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 w-12 text-center border-r border-slate-200 dark:border-slate-800">1</td>
                                        <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 w-1/3 border-r border-slate-200 dark:border-slate-800">Name of Applicant / Co-Applicant / Guarantor</td>
                                        <td class="p-4 font-medium text-slate-900 dark:text-white">{{ $bld['applicant_coapplicant_guarantor_name'] ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">2</td>
                                        <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">Visit to Office / Work Place of Borrower</td>
                                        <td class="p-4 font-medium text-slate-900 dark:text-white">Date of Visit: {{ !empty($bld['visit_office_date']) ? \Carbon\Carbon::parse($bld['visit_office_date'])->format('d/m/Y') : '—' }}</td>
                                    </tr>
                                    <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                        <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">3</td>
                                        <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">Name of Office / Organization, Address & Office Phone No.</td>
                                        <td class="p-4 space-y-1">
                                            <p class="font-medium text-slate-900 dark:text-white whitespace-pre-line">{{ $bld['office_organization_address'] ?? '—' }}</p>
                                            @if (!empty($bld['office_phone_no']))
                                                <p class="text-xs text-slate-500"><span class="font-semibold">Office Phone:</span> {{ $bld['office_phone_no'] }}</p>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">4</td>
                                        <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">For Self Employed:</td>
                                        <td class="p-4 space-y-3">
                                            <div>
                                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">A. Type of Organization</span>
                                                <div class="flex flex-wrap gap-2">
                                                    @forelse ($bld['type_of_organization'] ?? [] as $item)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800">✓ {{ $item }}</span>
                                                    @empty
                                                        <span class="text-slate-400 italic">None selected</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                            <div>
                                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">B. Nature of Business</span>
                                                <div class="flex flex-wrap gap-2">
                                                    @forelse ($bld['nature_of_business'] ?? [] as $item)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">✓ {{ $item }}</span>
                                                    @empty
                                                        <span class="text-slate-400 italic">None selected</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                            <div>
                                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">C. Whether own office / rented / leased</span>
                                                <div class="flex flex-wrap gap-2">
                                                    @forelse ($bld['office_ownership'] ?? [] as $item)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 dark:bg-purple-950/30 dark:text-purple-300 border border-purple-200 dark:border-purple-800">✓ {{ $item }}</span>
                                                    @empty
                                                        <span class="text-slate-400 italic">None selected</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                        <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">5</td>
                                        <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">Land mark for Place of work</td>
                                        <td class="p-4 font-medium text-slate-900 dark:text-white">{{ $bld['workplace_landmark'] ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">6</td>
                                        <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">Number of year Service / Business</td>
                                        <td class="p-4 font-medium text-slate-900 dark:text-white">{{ $bld['years_in_business'] ?? '—' }}</td>
                                    </tr>
                                    <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                        <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">7</td>
                                        <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">Designation of APPLICANT / GUARANTOR</td>
                                        <td class="p-4 font-medium text-slate-900 dark:text-white">{{ $bld['designation_applicant_guarantor'] ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">8</td>
                                        <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">Whom met (Person contacted & designation & Tel)</td>
                                        <td class="p-4 font-medium text-slate-900 dark:text-white">{{ $bld['whom_met_details'] ?? '—' }}</td>
                                    </tr>
                                    <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                        <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">9</td>
                                        <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">Office TVR Result (Positive / Negative)</td>
                                        <td class="p-4 font-bold">
                                            @if (($bld['office_tvr_result'] ?? '') === 'Positive')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">Positive</span>
                                            @elseif (($bld['office_tvr_result'] ?? '') === 'Negative')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300">Negative</span>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- PANEL 3: Business Profile -->
            <div id="business-panel" class="tab-panel space-y-8 hidden">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">Business Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Registered Business Name</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->business_name ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Constitution (of Business)</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->constitution_of_business ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Firm / Entity Name</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->firm_name ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5 md:col-span-2">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Business Introduction / Description</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $lead->introduction ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Business Activity Profile</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->business_activity ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5 md:col-span-2">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Premises Address</span>
                            <span class="font-medium text-slate-900 dark:text-white whitespace-pre-line">{{ $lead->business_address ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Operating Location</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->business_location ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">Operations Metrics</h3>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-6 text-sm">
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Experience (in Years)</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->business_experience ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Total Manpower</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->no_of_manpower ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Premises Area Size</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->area_of_premises ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Land & Factory Building</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->land_and_factory_building ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">Site Connectivity</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->connectivity ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">GST Registration</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">GST Applicable</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->gst_applicable ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">GSTIN Number</span>
                            <span class="font-medium text-slate-900 dark:text-white font-mono">{{ $lead->gst_number ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">GST Portal User ID</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $lead->gst_id ?: '—' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs text-slate-400 dark:text-slate-500 block font-semibold">GST Portal Password</span>
                            <span class="font-medium text-slate-900 dark:text-white font-mono">{{ $lead->gst_password ?: '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL 4: Financials & Active Liabilities -->
            <div id="finance-panel" class="tab-panel space-y-8 hidden">
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Active Bank Accounts</h3>
                    @php
                        $hasBankDetails = false;
                        if (is_array($lead->bank_details)) {
                            foreach ($lead->bank_details as $bank) {
                                if (!empty($bank['bank_name']) || !empty($bank['account_number'])) {
                                    $hasBankDetails = true;
                                    break;
                                }
                            }
                        }
                    @endphp
                    @if ($hasBankDetails)
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800">
                                    <tr>
                                        <th class="p-3 font-semibold w-12 text-center">SR</th>
                                        <th class="p-3 font-semibold">Bank Name</th>
                                        <th class="p-3 font-semibold">Account Number</th>
                                        <th class="p-3 font-semibold">Account Type</th>
                                        <th class="p-3 font-semibold">IFSC Code</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach ($lead->bank_details as $index => $bank)
                                        @if (!empty($bank['bank_name']) || !empty($bank['account_number']))
                                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10">
                                                <td class="p-3 text-center text-slate-400 font-semibold">{{ $index + 1 }}</td>
                                                <td class="p-3 font-medium text-slate-900 dark:text-white">{{ $bank['bank_name'] ?: '—' }}</td>
                                                <td class="p-3 text-slate-600 dark:text-slate-300 font-mono">{{ $bank['account_number'] ?: '—' }}</td>
                                                <td class="p-3 text-slate-600 dark:text-slate-400">{{ $bank['account_type'] ?: '—' }}</td>
                                                <td class="p-3 text-slate-600 dark:text-slate-400 font-mono">{{ $bank['ifsc_code'] ?: '—' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 dark:text-slate-500 py-1">No bank details recorded.</p>
                    @endif
                </div>

                <div class="space-y-4 pt-4">
                    <h3 class="text-sm font-bold text-rose-600 dark:text-rose-455 flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        Existing Liabilities (Current Loans)
                    </h3>
                    @php
                        $hasLoans = false;
                        if (is_array($lead->current_loans)) {
                            foreach ($lead->current_loans as $loan) {
                                if (!empty($loan['bank_name']) || !empty($loan['loan_amount'])) {
                                    $hasLoans = true;
                                    break;
                                }
                            }
                        }
                    @endphp
                    @if ($hasLoans)
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead class="bg-rose-50/20 dark:bg-rose-950/10 text-rose-900 dark:text-rose-400 border-b border-slate-200 dark:border-slate-800">
                                    <tr>
                                        <th class="p-3 font-semibold w-12 text-center">SR</th>
                                        <th class="p-3 font-semibold">Creditor Bank</th>
                                        <th class="p-3 font-semibold">Liability Type</th>
                                        <th class="p-3 font-semibold">Sanctioned Amount</th>
                                        <th class="p-3 font-semibold">Disbursal Date</th>
                                        <th class="p-3 font-semibold">Monthly EMI</th>
                                        <th class="p-3 font-semibold">Outstanding Balance</th>
                                        <th class="p-3 font-semibold">Tenure</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach ($lead->current_loans as $index => $loan)
                                        @if (!empty($loan['bank_name']) || !empty($loan['loan_amount']))
                                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10">
                                                <td class="p-3 text-center text-slate-400 font-semibold">{{ $index + 1 }}</td>
                                                <td class="p-3 font-medium text-slate-900 dark:text-white">{{ $loan['bank_name'] ?: '—' }}</td>
                                                <td class="p-3 text-slate-600 dark:text-slate-300">{{ $loan['loan_type'] ?: '—' }}</td>
                                                <td class="p-3 text-rose-600 dark:text-rose-400 font-bold">{{ $loan['loan_amount'] ?: '—' }}</td>
                                                <td class="p-3 text-slate-600 dark:text-slate-400">{{ $loan['disburse_date'] ?: '—' }}</td>
                                                <td class="p-3 text-slate-600 dark:text-slate-400">{{ $loan['emi'] ?: '—' }}</td>
                                                <td class="p-3 text-slate-600 dark:text-slate-400 font-semibold">{{ $loan['outstanding_amount'] ?: '—' }}</td>
                                                <td class="p-3 text-slate-600 dark:text-slate-400">{{ $loan['tenure'] ?: '—' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 dark:text-slate-500 py-1">No active liabilities recorded.</p>
                    @endif
                </div>
            </div>

            <!-- PANEL 5: Documents Checklist & Files -->
            @php
                $docTypesShowList = $documentTypes ?? \App\Models\DocumentType::where('status', 'active')->ordered()->get();
                $groupedLeadDocs = $lead->leadDocuments->groupBy('document_type_id');
            @endphp
            <div id="documents-panel" class="tab-panel space-y-6 hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-200/80 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-4 bg-primary-600 rounded-full"></span>
                            Lead Documents
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Uploaded KYC, financial and project documents ({{ $uploadedDocCount }} attached).</p>
                    </div>
                    @can('leads.edit')
                        <a href="{{ route('admin.leads.edit', $lead) }}#documents-panel" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-semibold rounded-xl shadow-sm transition no-underline">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            Manage & Edit Documents
                        </a>
                    @endcan
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($docTypesShowList as $type)
                        @php
                            $docsForType = $groupedLeadDocs->get($type->id, collect());
                            $isUploaded = $docsForType->isNotEmpty();
                        @endphp
                        <div class="p-5 sm:p-6 rounded-2xl border transition-all duration-200 flex flex-col justify-between space-y-4 {{ $isUploaded ? 'bg-white dark:bg-slate-900 border-slate-200/90 dark:border-slate-800 shadow-sm hover:border-slate-300 dark:hover:border-slate-700' : 'bg-slate-50/50 dark:bg-slate-950/30 border-dashed border-slate-200 dark:border-slate-800' }}">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-mono font-bold {{ $isUploaded ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' : 'bg-slate-200/60 dark:bg-slate-800 text-slate-500' }}">
                                        #{{ $loop->iteration }}
                                    </span>
                                    @if ($isUploaded)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 text-[11px] font-bold border border-emerald-200/60 dark:border-emerald-800/60">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Uploaded ({{ $docsForType->count() }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800/80 text-slate-400 text-[11px] font-semibold">
                                            Pending
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">
                                        {{ $type->name }}
                                    </h4>
                                    @can('leads.edit')
                                        <a href="{{ route('admin.leads.edit', $lead) }}#documents-panel" title="Edit / Upload {{ $type->name }}" class="p-1 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-md transition shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>
                                    @endcan
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 space-y-2.5">
                                @if ($isUploaded)
                                    @foreach ($docsForType as $docItem)
                                        <div class="p-3 bg-slate-50/80 dark:bg-slate-800/40 rounded-xl border border-slate-200/60 dark:border-slate-800 flex items-center justify-between gap-3 text-xs hover:border-slate-300 dark:hover:border-slate-700 transition">
                                            <div class="truncate max-w-[150px] sm:max-w-[170px]">
                                                <span class="font-semibold text-slate-900 dark:text-white block truncate" title="{{ $docItem->original_name }}">
                                                    @if ($docItem->side)
                                                        <span class="capitalize font-bold text-primary-600 dark:text-primary-400">[{{ $docItem->side }}]</span>
                                                    @endif
                                                    {{ $docItem->original_name }}
                                                </span>
                                                <span class="text-[10px] text-slate-400 font-medium block mt-0.5">{{ $docItem->formatted_file_size }}</span>
                                            </div>

                                            <div class="flex items-center gap-1 shrink-0">
                                                {{-- View Button --}}
                                                <a href="{{ route('admin.leads.documents.download', [$lead, $docItem]) }}" target="_blank" title="View Document" class="p-1.5 text-slate-500 hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400 rounded-lg hover:bg-white dark:hover:bg-slate-900 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </a>

                                                {{-- Download Button --}}
                                                <a href="{{ route('admin.leads.documents.download', [$lead, $docItem]) }}" download title="Download Document" class="p-1.5 text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 rounded-lg hover:bg-white dark:hover:bg-slate-900 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                </a>

                                                @can('leads.edit')
                                                    {{-- Edit Button --}}
                                                    <a href="{{ route('admin.leads.edit', $lead) }}#documents-panel" title="Edit / Replace Document" class="p-1.5 text-slate-500 hover:text-amber-600 dark:text-slate-400 dark:hover:text-amber-400 rounded-lg hover:bg-white dark:hover:bg-slate-900 transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                    </a>

                                                    {{-- Delete Button --}}
                                                    <form action="{{ route('admin.leads.documents.destroy', [$lead, $docItem]) }}" method="POST" onsubmit="return confirm('Remove this document?')" class="inline-block">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" title="Delete Document" class="p-1.5 text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 rounded-lg hover:bg-white dark:hover:bg-slate-900 transition">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="py-4 text-center">
                                        <span class="text-xs text-slate-400 dark:text-slate-500 italic block mb-2">No file uploaded</span>
                                        @can('leads.edit')
                                            <a href="{{ route('admin.leads.edit', $lead) }}#documents-panel" class="inline-flex items-center gap-1 text-xs text-primary-600 dark:text-primary-400 font-semibold hover:underline">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Upload Document
                                            </a>
                                        @endcan
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Notes / Requirements -->
    @if ($lead->notes)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5 mb-6">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-2 mb-3">Notes & Requirements</h3>
            <p class="text-xs text-slate-600 dark:text-slate-350 leading-relaxed whitespace-pre-line">{{ $lead->notes }}</p>
        </div>
    @endif

    <!-- Custom Dialog & Input Overrides for Filament Look -->
    <style>
        dialog::backdrop {
            background: rgba(15, 23, 42, 0.3) !important;
            backdrop-filter: blur(8px) !important;
        }
        .filament-input {
            border: 1px solid #e2e8f0 !important;
            background-color: #ffffff !important;
            border-radius: 0.5rem !important;
            font-size: 0.875rem !important;
            line-height: 1.25rem !important;
            color: #1e293b !important;
            transition: all 0.15s ease-in-out;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .dark .filament-input {
            border-color: #334155 !important;
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
        }
        .filament-input:focus {
            border-color: #3b82f6 !important;
            outline: none !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1) !important;
        }
        /* Select2 Filament Override */
        .select2-container--bootstrap-5 .select2-selection {
            border: 1px solid #e2e8f0 !important;
            background-color: #ffffff !important;
            border-radius: 0.5rem !important;
            height: 42px !important;
            padding: 0.375rem 0.75rem !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .dark .select2-container--bootstrap-5 .select2-selection {
            border-color: #334155 !important;
            background-color: #0f172a !important;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            padding: 4px 0 0 0 !important;
            font-size: 0.875rem !important;
        }
        .dark .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #f1f5f9 !important;
        }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1) !important;
        }
        .select2-dropdown {
            border-radius: 0.5rem !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }
        .dark .select2-dropdown {
            border-color: #334155 !important;
            background-color: #0f172a !important;
        }
    </style>

    <!-- Schedule Visit Dialog -->
    <dialog id="scheduleVisitModal" class="rounded-xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-2xl backdrop:bg-slate-900/40 focus:outline-none" style="margin: auto; overflow: visible; width: 680px; max-width: 95%;">
        <div class="flex items-center justify-between pb-4 mb-6">
            <h3 class="text-lg font-bold text-slate-950 dark:text-white">Schedule Visit</h3>
            <button type="button" onclick="document.getElementById('scheduleVisitModal').close()" class="text-slate-400 hover:text-slate-600 transition">&times;</button>
        </div>
        <form action="{{ route('admin.leads.visits.store', $lead) }}" method="POST" class="space-y-6 text-sm">
            @csrf
            <input type="hidden" name="status" value="scheduled">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-800 dark:text-slate-200">
                        Representative <span class="text-rose-500">*</span>
                    </label>
                    <select name="employee_id" id="visit_employee_id" required class="w-full select2">
                        <option value="">Select Employee...</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $lead->assigned_employee_id == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label for="visit_date" class="block text-xs font-semibold text-slate-800 dark:text-slate-200">
                        Visit Date & Time <span class="text-rose-500">*</span>
                    </label>
                    <input type="datetime-local" name="visit_date" id="visit_date" required class="w-full filament-input p-3">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="visit_purpose" class="block text-xs font-semibold text-slate-800 dark:text-slate-200">
                    Purpose <span class="text-rose-500">*</span>
                </label>
                <textarea name="purpose" id="visit_purpose" required rows="3" placeholder="Describe visit purpose..." class="w-full filament-input p-3"></textarea>
            </div>

            <div class="space-y-1.5">
                <label for="visit_notes" class="block text-xs font-semibold text-slate-800 dark:text-slate-200">Notes (Optional)</label>
                <textarea name="notes" id="visit_notes" rows="2" placeholder="Optional pre-visit notes..." class="w-full filament-input p-3"></textarea>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    Schedule
                </button>
                <button type="button" onclick="document.getElementById('scheduleVisitModal').close()" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg shadow-sm transition">
                    Cancel
                </button>
            </div>
        </form>
    </dialog>

    <!-- Update Visit Dialog -->
    <dialog id="updateVisitModal" class="rounded-xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-2xl backdrop:bg-slate-900/40 focus:outline-none" style="margin: auto; overflow: visible; width: 680px; max-width: 95%;">
        <div class="flex items-center justify-between pb-4 mb-6">
            <h3 class="text-lg font-bold text-slate-950 dark:text-white font-sans">Update Visit Status</h3>
            <button type="button" onclick="document.getElementById('updateVisitModal').close()" class="text-slate-400 hover:text-slate-600 transition">&times;</button>
        </div>
        <form id="updateVisitForm" method="POST" class="space-y-6 text-sm">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="employee_id" id="edit_visit_employee_id">
            <input type="hidden" name="visit_date" id="edit_visit_date">
            <input type="hidden" name="purpose" id="edit_visit_purpose">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-800 dark:text-slate-200">
                        Visit Status <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" id="edit_visit_status" required class="w-full select2">
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="rescheduled">Rescheduled</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label for="edit_visit_next_follow_up" class="block text-xs font-semibold text-slate-800 dark:text-slate-200">Next Follow-up Date (Optional)</label>
                    <input type="date" name="next_follow_up_date" id="edit_visit_next_follow_up" class="w-full filament-input p-3">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="edit_visit_outcome" class="block text-xs font-semibold text-slate-800 dark:text-slate-200">Outcome / Findings</label>
                <textarea name="outcome" id="edit_visit_outcome" rows="2" placeholder="Outcome findings..." class="w-full filament-input p-3"></textarea>
            </div>

            <div class="space-y-1.5">
                <label for="edit_visit_next_action" class="block text-xs font-semibold text-slate-800 dark:text-slate-200">Next Steps</label>
                <textarea name="next_action" id="edit_visit_next_action" rows="2" placeholder="Next steps..." class="w-full filament-input p-3"></textarea>
            </div>

            <div class="space-y-1.5">
                <label for="edit_visit_notes" class="block text-xs font-semibold text-slate-800 dark:text-slate-200">Notes / Feedback</label>
                <textarea name="notes" id="edit_visit_notes" rows="2" placeholder="Remarks..." class="w-full filament-input p-3"></textarea>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-lg shadow-sm transition">Save Changes</button>
                <button type="button" onclick="document.getElementById('updateVisitModal').close()" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg shadow-sm transition">Cancel</button>
            </div>
        </form>
    </dialog>

    <script>
        function switchTab(tabId, panelId) {
            // Hide all panels
            document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.add('hidden'));
            // Show target panel
            document.getElementById(panelId).classList.remove('hidden');
            
            // Reset all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-white', 'dark:bg-slate-800', 'text-primary-600', 'dark:text-primary-400', 'shadow-sm', 'border', 'border-primary-600', 'dark:border-primary-500');
                btn.classList.add('text-slate-500');
            });
            // Highlight active tab button
            const activeBtn = document.getElementById(tabId);
            activeBtn.classList.remove('text-slate-500');
            activeBtn.classList.add('bg-white', 'dark:bg-slate-800', 'text-primary-600', 'dark:text-primary-400', 'shadow-sm', 'border', 'border-primary-600', 'dark:border-primary-500');
        }

        function openEditVisitModal(visit) {
            const form = document.getElementById('updateVisitForm');
            // Set dynamic action URL
            form.action = `/admin/leads/${visit.lead_id}/visits/${visit.id}`;
            
            // Set hidden fields
            document.getElementById('edit_visit_employee_id').value = visit.employee_id;
            // format visit date to ISO string style compatible with controller expectations
            document.getElementById('edit_visit_date').value = visit.visit_date.replace(' ', 'T');
            document.getElementById('edit_visit_purpose').value = visit.purpose;

            // Set editable fields
            document.getElementById('edit_visit_status').value = visit.status;
            document.getElementById('edit_visit_outcome').value = visit.outcome || '';
            document.getElementById('edit_visit_next_action').value = visit.next_action || '';
            document.getElementById('edit_visit_next_follow_up').value = visit.next_follow_up_date || '';
            document.getElementById('edit_visit_notes').value = visit.notes || '';

            // Show dialog
            document.getElementById('updateVisitModal').showModal();
            if (window.initSelect2) {
                window.initSelect2('#updateVisitModal select');
            }
        }
    </script>
</x-admin-layout>
