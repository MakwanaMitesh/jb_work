<x-admin-bootstrap-layout :title="$lead->name">
    <div class="d-flex flex-column gap-3 mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.leads.index') }}">Leads</a>
            <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">View</span>
        </nav>

        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <h1 class="jb-page-title" style="line-height:1">{{ $lead->name }}</h1>
                    <span class="jb-badge jb-badge-slate">#LD-{{ str_pad($lead->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3 mt-1" style="font-size:var(--text-sm);color:var(--slate-500)">
                    <span class="d-inline-flex align-items-center gap-2">
                        <svg width="16" height="16" style="color:var(--slate-400)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $lead->mobile_number }}
                    </span>
                    @if ($lead->email)
                        <span class="d-inline-flex align-items-center gap-2">
                            <svg width="16" height="16" style="color:var(--slate-400)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $lead->email }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <a href="{{ route('admin.leads.inspection-sheet.pdf', $lead) }}" target="_blank" class="btn btn-sm d-inline-flex align-items-center gap-2" style="background:var(--amber-600);color:#fff">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Inspection Sheet (PDF)
                </a>
            </div>
        </div>
    </div>

    @php
        $color = match($lead->status) {
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
            default => 'jb-badge-slate',
        };
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <div class="jb-stat-tile">
                <span class="jb-stat-label">Status</span>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <span class="jb-badge {{ $color }}">{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</span>

                </div>
            </div>
        </div>

        <div class="col-6 col-lg-2">
            <div class="jb-stat-tile">
                <span class="jb-stat-label">Bank</span>
                <span class="jb-stat-value">{{ $lead->bank?->name ?: '—' }}</span>
            </div>
        </div>

        <div class="col-6 col-lg-2">
            <div class="jb-stat-tile">
                <span class="jb-stat-label">Loan Product</span>
                <span class="jb-stat-value">{{ $lead->loanProduct?->name ?: '—' }}</span>
            </div>
        </div>

        <div class="col-6 col-lg-2">
            <div class="jb-stat-tile">
                <span class="jb-stat-label">Constitution</span>
                <span class="jb-stat-value">{{ $lead->constitution?->name ?: $lead->constitution_of_business ?: '—' }}</span>
            </div>
        </div>

        <div class="col-6 col-lg-2">
            <div class="jb-stat-tile">
                <span class="jb-stat-label">Representative</span>
                <span class="jb-stat-value">{{ $lead->assignedEmployee?->name ?: 'Unassigned' }}</span>
            </div>
        </div>

        <div class="col-6 col-lg-2">
            <div class="jb-stat-tile">
                <span class="jb-stat-label">Referral Source</span>
                <span class="jb-stat-value">{{ $lead->agent?->name ?: 'Direct Client' }}</span>
            </div>
        </div>
    </div>

    <div class="jb-card mb-4" style="overflow:hidden">
        <div class="jb-tabs">
            <button type="button" onclick="switchTab('overview-tab', 'overview-panel')" id="overview-tab" class="jb-tab-btn active">
                Workflow & Requested Loan
            </button>
            <button type="button" onclick="switchTab('kyc-tab', 'kyc-panel')" id="kyc-tab" class="jb-tab-btn">
                KYC & Personal Details
            </button>
            @if (!empty($lead->bank_loan_details))
                <button type="button" onclick="switchTab('bank-loan-tab', 'bank-loan-panel')" id="bank-loan-tab" class="jb-tab-btn">
                    Bank Loan Report
                </button>
            @endif
            <button type="button" onclick="switchTab('business-tab', 'business-panel')" id="business-tab" class="jb-tab-btn">
                Business Profile
            </button>
            <button type="button" onclick="switchTab('finance-tab', 'finance-panel')" id="finance-tab" class="jb-tab-btn">
                Financials & Active Liabilities
            </button>
            @php
                $uploadedDocCount = $lead->leadDocuments->count();
            @endphp
            <button type="button" onclick="switchTab('documents-tab', 'documents-panel')" id="documents-tab" class="jb-tab-btn d-inline-flex align-items-center gap-2">
                <span>Documents</span>
                @if ($uploadedDocCount > 0)
                    <span class="jb-badge jb-badge-sm jb-badge-primary" style="padding:.0625rem .375rem">{{ $uploadedDocCount }}</span>
                @endif
            </button>
        </div>

        <div class="p-4">

            <!-- PANEL 1: Overview & Workflow -->
            <div id="overview-panel" class="tab-panel">
                <div class="row g-4">

                    <div class="col-12 col-xl-8">

                        <div class="mb-4">
                            <h3 class="jb-section-title border-bottom pb-2 mb-3">Requested Financing Details</h3>
                            <div class="row g-4">
                                <div class="col-12 col-md-4">
                                    <span class="jb-detail-label">Required Loan Amount</span>
                                    <span class="d-block fw-bold mt-1" style="font-size:var(--text-base);color:var(--primary-600)">{{ $lead->required_loan_amount ?: '—' }}</span>
                                </div>
                                <div class="col-12 col-md-4">
                                    <span class="jb-detail-label">CC Amount</span>
                                    <span class="jb-detail-value">{{ $lead->cc_amount ?: '—' }}</span>
                                    @if ($lead->cc_details)
                                        <p class="jb-muted mt-1 mb-0" style="white-space:pre-line">{{ $lead->cc_details }}</p>
                                    @endif
                                </div>
                                <div class="col-12 col-md-4">
                                    <span class="jb-detail-label">Term Loan Amount</span>
                                    <span class="jb-detail-value">{{ $lead->term_loan_amount ?: '—' }}</span>
                                    @if ($lead->term_loan_machinery_details)
                                        <p class="jb-muted mt-1 mb-0" style="white-space:pre-line">Machinery: {{ $lead->term_loan_machinery_details }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                                <h3 class="jb-section-title m-0">Scheduled & Past Visits</h3>
                                @can('visits.create')
                                    <button type="button"
                                        onclick="document.getElementById('scheduleVisitModal').showModal(); if(window.initSelect2){ window.initSelect2('#scheduleVisitModal select'); }"
                                        class="btn btn-primary btn-sm">
                                        + Schedule Visit
                                    </button>
                                @endcan
                            </div>

                            @if ($lead->visits->isNotEmpty())
                                <div class="d-flex flex-column gap-3">
                                    @foreach ($lead->visits as $visit)
                                        @php
                                            $visitBadge = match($visit->status) {
                                                'scheduled'   => 'jb-badge-blue',
                                                'completed'   => 'jb-badge-emerald',
                                                'cancelled'   => 'jb-badge-rose',
                                                'rescheduled' => 'jb-badge-amber',
                                                default       => 'jb-badge-slate',
                                            };
                                        @endphp
                                        <div class="jb-subcard d-flex align-items-start justify-content-between gap-3">
                                            <div class="min-w-0">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="fw-bold" style="font-size:var(--text-sm);color:var(--slate-900)">{{ $visit->visit_date->format('d M Y, h:i A') }}</span>
                                                    <span class="jb-badge jb-badge-xs {{ $visitBadge }}">{{ $visit->status }}</span>
                                                </div>
                                                <div class="jb-muted mt-1" style="font-size:var(--text-xs)">
                                                    <p class="mb-0">
                                                        <span class="fw-medium">Rep:</span>
                                                        <strong style="color:var(--slate-800)">{{ $visit->employee->name }}</strong>
                                                        <span class="mx-1" style="color:var(--slate-300)">|</span>
                                                        <span class="fw-medium">By:</span> {{ $visit->creator->name }}
                                                    </p>
                                                    <p class="mb-0"><span class="fw-medium">Purpose:</span> {{ $visit->purpose }}</p>
                                                    @if ($visit->outcome)
                                                        <p class="mb-0" style="color:var(--emerald-600)"><span class="fw-medium">Outcome:</span> {{ $visit->outcome }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            @can('visits.edit')
                                                <button type="button"
                                                    onclick="openEditVisitModal({{ json_encode($visit) }})"
                                                    class="btn btn-secondary btn-sm flex-shrink-0">
                                                    Update
                                                </button>
                                            @endcan
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 rounded-3" style="border:1px dashed var(--slate-200)">
                                    <p class="m-0" style="font-size:var(--text-sm);color:var(--slate-400)">No visits scheduled yet.</p>
                                </div>
                            @endif
                        </div>

                    </div>

                    <div class="col-12 col-xl-4 d-flex flex-column gap-3">

                        <div class="jb-card" style="overflow:hidden">
                            <div class="jb-card-header">
                                <h4 class="m-0 fw-bold text-uppercase" style="font-size:var(--text-xs);letter-spacing:.08em;color:var(--slate-500)">Representative Assignment</h4>
                            </div>
                            <div class="p-3 d-flex flex-column gap-3">

                                @if ($lead->assignedEmployee)
                                    <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background:color-mix(in oklab, var(--primary-50) 60%, transparent);border:1px solid var(--primary-100)">
                                        <div class="jb-avatar" style="background:var(--primary-600);color:#fff">
                                            {{ strtoupper(substr($lead->assignedEmployee->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-fill">
                                            <p class="fw-bold m-0" style="font-size:var(--text-sm);color:var(--slate-900)">{{ $lead->assignedEmployee->name }}</p>
                                            <p class="m-0 mt-1" style="font-size:.625rem;color:var(--slate-500)">Assigned on {{ $lead->assigned_at?->format('d M Y') }}</p>
                                            @can('leads.assign')
                                                <button type="button" onclick="document.getElementById('reassignFormContainer').classList.toggle('jb-hidden')"
                                                    class="btn btn-link p-0 mt-2 fw-bold" style="font-size:.6875rem;color:var(--primary-600)">
                                                    Change Representative
                                                </button>
                                            @endcan
                                        </div>
                                    </div>

                                    @can('leads.assign')
                                        <div id="reassignFormContainer" class="jb-hidden">
                                            <form action="{{ route('admin.leads.assign', $lead) }}" method="POST" class="d-flex flex-column gap-2 p-3 rounded-3 jb-card">
                                                @csrf
                                                <select name="employee_id" required class="form-select form-select-sm select2">
                                                    <option value="">Select Employee...</option>
                                                    @foreach ($employees as $emp)
                                                        <option value="{{ $emp->id }}" @selected($lead->assigned_employee_id == $emp->id)>{{ $emp->name }}</option>
                                                    @endforeach
                                                </select>
                                                <textarea name="notes" rows="2" placeholder="Reassignment notes..." class="form-control form-control-sm"></textarea>
                                                <button type="submit" class="btn btn-primary btn-sm">Confirm Change</button>
                                            </form>
                                        </div>
                                    @endcan
                                @else
                                    @can('leads.assign')
                                        <form action="{{ route('admin.leads.assign', $lead) }}" method="POST" class="d-flex flex-column gap-2 p-3 rounded-3" style="border:1px dashed var(--slate-300);background:color-mix(in oklab, var(--slate-50) 50%, transparent)">
                                            @csrf
                                            <p class="fw-semibold mb-0" style="font-size:.6875rem;color:var(--rose-500)">⚠ No representative assigned</p>
                                            <select name="employee_id" required class="form-select form-select-sm select2">
                                                <option value="">Select Employee...</option>
                                                @foreach ($employees as $emp)
                                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                                @endforeach
                                            </select>
                                            <textarea name="notes" rows="2" placeholder="Assignment notes..." class="form-control form-control-sm"></textarea>
                                            <button type="submit" class="btn btn-primary btn-sm">Assign Representative</button>
                                        </form>
                                    @else
                                        <div class="jb-alert jb-alert-danger">
                                            <span class="fw-semibold">No representative assigned</span>
                                        </div>
                                    @endcan
                                @endif

                                @if ($lead->assignments->isNotEmpty())
                                    <div class="pt-3 border-top d-flex flex-column gap-2">
                                        <span class="d-block fw-bold text-uppercase" style="font-size:.625rem;letter-spacing:.08em;color:var(--slate-400)">Assignment History</span>
                                        @foreach ($lead->assignments->sortByDesc('assigned_at')->take(3) as $history)
                                            <div class="d-flex align-items-start justify-content-between gap-2" style="font-size:.6875rem">
                                                <span class="fw-semibold" style="color:var(--slate-700)">{{ $history->employee->name }}</span>
                                                <span class="flex-shrink-0" style="font-size:.625rem;color:var(--slate-400)">{{ $history->assigned_at->format('d M Y') }}</span>
                                            </div>
                                            @if ($history->notes)
                                                <p class="fst-italic mb-0" style="font-size:.625rem;color:var(--slate-400);margin-top:-.375rem">"{{ $history->notes }}"</p>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="jb-card" style="overflow:hidden">
                            <div class="jb-card-header">
                                <h4 class="m-0 fw-bold text-uppercase" style="font-size:var(--text-xs);letter-spacing:.08em;color:var(--slate-500)">Activity Timeline</h4>
                            </div>
                            <div class="p-3">
                                @if ($lead->activities->isNotEmpty())
                                    <div class="jb-timeline">
                                        @foreach ($lead->activities->take(8) as $activity)
                                            @php
                                                $dotColor = match($activity->type) {
                                                    'created'         => 'var(--blue-500)',
                                                    'assigned'        => 'var(--indigo-500)',
                                                    'reassigned'      => 'var(--purple-500)',
                                                    'status_changed'  => 'var(--amber-500)',
                                                    'visit_created'   => 'var(--cyan-500)',
                                                    'visit_completed' => 'var(--emerald-500)',
                                                    'visit_cancelled' => 'var(--rose-500)',
                                                    default           => 'var(--slate-400)',
                                                };
                                            @endphp
                                            <div class="jb-timeline-item">
                                                <span class="jb-timeline-dot" style="background:{{ $dotColor }}"></span>
                                                <div class="d-flex align-items-center gap-2 mb-1" style="font-size:.625rem;color:var(--slate-400)">
                                                    <span class="fw-semibold" style="color:var(--slate-600)">{{ $activity->user->name }}</span>
                                                    <span style="color:var(--slate-300)">·</span>
                                                    <span>{{ $activity->created_at->format('d M, h:i A') }}</span>
                                                </div>
                                                <p class="fw-bold mb-0" style="font-size:var(--text-xs);color:var(--slate-900)">{{ ucfirst(str_replace('_', ' ', $activity->type)) }}</p>
                                                <p class="mt-1 mb-0" style="font-size:.6875rem;color:var(--slate-500)">{{ $activity->description }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="fst-italic py-2 mb-0" style="font-size:var(--text-xs);color:var(--slate-400)">No activity yet.</p>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- PANEL 2: KYC & Personal Details -->
            <div id="kyc-panel" class="tab-panel jb-hidden">
                <div class="mb-4">
                    <h3 class="jb-section-title border-bottom pb-2 mb-3">KYC Profile</h3>
                    <div class="row g-4">
                        <div class="col-6 col-md-4">
                            <span class="jb-detail-label">Date of Birth</span>
                            <span class="jb-detail-value">{{ $lead->date_of_birth?->format('d M Y') ?: '—' }}</span>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="jb-detail-label">Gender</span>
                            <span class="jb-detail-value">{{ $lead->gender ?: '—' }}</span>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="jb-detail-label">Mother Name</span>
                            <span class="jb-detail-value">{{ $lead->mother_name ?: '—' }}</span>
                        </div>
                        <div class="col-12 col-md-8">
                            <span class="jb-detail-label">Personal Address</span>
                            <span class="jb-detail-value" style="white-space:pre-line">{{ $lead->address ?: '—' }}</span>
                        </div>
                        <div class="col-6 col-md-4">
                            <span class="jb-detail-label">Education</span>
                            <span class="jb-detail-value">{{ $lead->education ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3 class="jb-section-title border-bottom pb-2 mb-3">KYC Identification Numbers</h3>
                    <div class="row g-4">
                        <div class="col-6 col-md-3">
                            <span class="jb-detail-label">Aadhaar Card</span>
                            <span class="jb-detail-value font-monospace">{{ $lead->aadhar_card ?: '—' }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="jb-detail-label">PAN Card</span>
                            <span class="jb-detail-value font-monospace">{{ $lead->pan_card ?: '—' }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="jb-detail-label">Udyam Registration</span>
                            <span class="jb-detail-value font-monospace">{{ $lead->udyam_registration ?: '—' }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="jb-detail-label">FSSAI License No.</span>
                            <span class="jb-detail-value font-monospace">{{ $lead->fssai_license ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="jb-section-title border-bottom pb-2 mb-3">ITR Filing & Login Credentials</h3>

                    <div class="jb-inset-box mb-3">
                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                <span class="jb-detail-label">ITR Portal User ID</span>
                                <span class="jb-detail-value">{{ $lead->itr_id ?: '—' }}</span>
                            </div>
                            <div class="col-12 col-md-6">
                                <span class="jb-detail-label">ITR Password</span>
                                <span class="jb-detail-value font-monospace">{{ $lead->itr_password ?: '—' }}</span>
                            </div>
                        </div>
                    </div>

                    @php $itrList = $lead->formatted_itr_details; @endphp
                    @if (count($itrList) > 0)
                        <div class="d-flex flex-column gap-3">
                            @foreach ($itrList as $idx => $itr)
                                <div class="jb-subcard d-flex flex-column gap-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="fw-bold text-uppercase" style="font-size:var(--text-xs);letter-spacing:.05em;color:var(--slate-700)">ITR Profile #{{ $idx + 1 }}</div>
                                        @if(!empty($itr['assessment_year']))
                                            <span class="jb-badge jb-badge-emerald">
                                                <span class="jb-dot jb-dot-emerald"></span>
                                                {{ $itr['assessment_year'] }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <span class="jb-detail-label">ITR Audited Status</span>
                                            <span class="jb-detail-value">{{ $itr['itr_audited'] ?? '—' }}</span>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <span class="jb-detail-label">Assessment Year</span>
                                            <span class="jb-detail-value">{{ $itr['assessment_year'] ?? '—' }}</span>
                                        </div>
                                    </div>

                                    <div class="pt-3 border-top">
                                        <span class="jb-detail-label d-block mb-2">Uploaded ITR Documents</span>
                                        <div class="d-flex flex-wrap gap-2">
                                            @if(!empty($itr['audit_report']))
                                                <a href="{{ Storage::url($itr['audit_report']) }}" target="_blank" class="jb-chip-link">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <span>Audit Report</span>
                                                </a>
                                            @endif
                                            @if(!empty($itr['itr_file']))
                                                <a href="{{ Storage::url($itr['itr_file']) }}" target="_blank" class="jb-chip-link">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <span>ITR File</span>
                                                </a>
                                            @endif
                                            @if(!empty($itr['computation']))
                                                <a href="{{ Storage::url($itr['computation']) }}" target="_blank" class="jb-chip-link">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <span>Computation</span>
                                                </a>
                                            @endif
                                            @if(!empty($itr['itr_form']))
                                                <a href="{{ Storage::url($itr['itr_form']) }}" target="_blank" class="jb-chip-link">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <span>ITR Form</span>
                                                </a>
                                            @endif
                                            @if(empty($itr['audit_report']) && empty($itr['itr_file']) && empty($itr['computation']) && empty($itr['itr_form']))
                                                <span class="fst-italic" style="font-size:var(--text-xs);color:var(--slate-400)">No document files uploaded for this profile.</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="font-size:var(--text-sm);color:var(--slate-500)">No ITR details available.</p>
                    @endif
                </div>
            </div>

            <!-- PANEL 2.5: Bank Loan Verification Report -->
            @if (!empty($lead->bank_loan_details))
                @php $bld = $lead->bank_loan_details; @endphp
                <div id="bank-loan-panel" class="tab-panel jb-hidden">
                    <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                        <h3 class="jb-section-title m-0">Bank Loan Verification Report (Annexure V-A)</h3>
                        <a href="{{ route('admin.leads.inspection-sheet.pdf', $lead) }}" target="_blank" class="jb-chip-link" style="background:var(--amber-50);color:var(--amber-700);border-color:color-mix(in oklab, var(--amber-200) 60%, transparent)">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download Inspection Sheet (PDF)
                        </a>
                    </div>
                    <div class="table-responsive rounded-3" style="border:1px solid var(--slate-200)">
                        <table class="jb-report-table">
                            <tbody>
                                <tr>
                                    <td class="jb-report-num">1</td>
                                    <td class="jb-report-label">Name of Applicant / Co-Applicant / Guarantor</td>
                                    <td class="fw-medium" style="color:var(--slate-900)">{{ $bld['applicant_coapplicant_guarantor_name'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="jb-report-num">2</td>
                                    <td class="jb-report-label">Visit to Office / Work Place of Borrower</td>
                                    <td class="fw-medium" style="color:var(--slate-900)">Date of Visit: {{ !empty($bld['visit_office_date']) ? \Carbon\Carbon::parse($bld['visit_office_date'])->format('d/m/Y') : '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="jb-report-num">3</td>
                                    <td class="jb-report-label">Name of Office / Organization, Address & Office Phone No.</td>
                                    <td>
                                        <p class="fw-medium mb-1" style="color:var(--slate-900);white-space:pre-line">{{ $bld['office_organization_address'] ?? '—' }}</p>
                                        @if (!empty($bld['office_phone_no']))
                                            <p class="mb-0" style="font-size:var(--text-xs);color:var(--slate-500)"><span class="fw-semibold">Office Phone:</span> {{ $bld['office_phone_no'] }}</p>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="jb-report-num">4</td>
                                    <td class="jb-report-label">For Self Employed:</td>
                                    <td>
                                        <div class="mb-3">
                                            <span class="d-block fw-bold text-uppercase mb-1" style="font-size:var(--text-xs);color:var(--slate-400)">A. Type of Organization</span>
                                            <div class="d-flex flex-wrap gap-2">
                                                @forelse ($bld['type_of_organization'] ?? [] as $item)
                                                    <span class="jb-badge jb-badge-blue">✓ {{ $item }}</span>
                                                @empty
                                                    <span class="fst-italic" style="color:var(--slate-400)">None selected</span>
                                                @endforelse
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <span class="d-block fw-bold text-uppercase mb-1" style="font-size:var(--text-xs);color:var(--slate-400)">B. Nature of Business</span>
                                            <div class="d-flex flex-wrap gap-2">
                                                @forelse ($bld['nature_of_business'] ?? [] as $item)
                                                    <span class="jb-badge jb-badge-emerald">✓ {{ $item }}</span>
                                                @empty
                                                    <span class="fst-italic" style="color:var(--slate-400)">None selected</span>
                                                @endforelse
                                            </div>
                                        </div>
                                        <div>
                                            <span class="d-block fw-bold text-uppercase mb-1" style="font-size:var(--text-xs);color:var(--slate-400)">C. Whether own office / rented / leased</span>
                                            <div class="d-flex flex-wrap gap-2">
                                                @forelse ($bld['office_ownership'] ?? [] as $item)
                                                    <span class="jb-badge jb-badge-purple">✓ {{ $item }}</span>
                                                @empty
                                                    <span class="fst-italic" style="color:var(--slate-400)">None selected</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="jb-report-num">5</td>
                                    <td class="jb-report-label">Land mark for Place of work</td>
                                    <td class="fw-medium" style="color:var(--slate-900)">{{ $bld['workplace_landmark'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="jb-report-num">6</td>
                                    <td class="jb-report-label">Number of year Service / Business</td>
                                    <td class="fw-medium" style="color:var(--slate-900)">{{ $bld['years_in_business'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="jb-report-num">7</td>
                                    <td class="jb-report-label">Designation of APPLICANT / GUARANTOR</td>
                                    <td class="fw-medium" style="color:var(--slate-900)">{{ $bld['designation_applicant_guarantor'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="jb-report-num">8</td>
                                    <td class="jb-report-label">Whom met (Person contacted & designation & Tel)</td>
                                    <td class="fw-medium" style="color:var(--slate-900)">{{ $bld['whom_met_details'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="jb-report-num">9</td>
                                    <td class="jb-report-label">Office TVR Result (Positive / Negative)</td>
                                    <td class="fw-bold">
                                        @if (($bld['office_tvr_result'] ?? '') === 'Positive')
                                            <span class="jb-badge jb-badge-green">Positive</span>
                                        @elseif (($bld['office_tvr_result'] ?? '') === 'Negative')
                                            <span class="jb-badge jb-badge-rose">Negative</span>
                                        @else
                                            <span style="color:var(--slate-400)">—</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- PANEL 3: Business Profile -->
            <div id="business-panel" class="tab-panel jb-hidden">
                <div class="mb-4">
                    <h3 class="jb-section-title border-bottom pb-2 mb-3">Business Details</h3>
                    <div class="row g-4">
                        <div class="col-12 col-md-4">
                            <span class="jb-detail-label">Registered Business Name</span>
                            <span class="jb-detail-value">{{ $lead->business_name ?: '—' }}</span>
                        </div>
                        <div class="col-12 col-md-4">
                            <span class="jb-detail-label">Constitution (of Business)</span>
                            <span class="jb-detail-value">{{ $lead->constitution_of_business ?: '—' }}</span>
                        </div>
                        <div class="col-12 col-md-4">
                            <span class="jb-detail-label">Firm / Entity Name</span>
                            <span class="jb-detail-value">{{ $lead->firm_name ?: '—' }}</span>
                        </div>
                        <div class="col-12 col-md-8">
                            <span class="jb-detail-label">Business Introduction / Description</span>
                            <span class="d-block fw-medium mt-1" style="color:var(--slate-700);white-space:pre-line">{{ $lead->introduction ?: '—' }}</span>
                        </div>
                        <div class="col-12 col-md-4">
                            <span class="jb-detail-label">Business Activity Profile</span>
                            <span class="jb-detail-value">{{ $lead->business_activity ?: '—' }}</span>
                        </div>
                        <div class="col-12 col-md-8">
                            <span class="jb-detail-label">Premises Address</span>
                            <span class="jb-detail-value" style="white-space:pre-line">{{ $lead->business_address ?: '—' }}</span>
                        </div>
                        <div class="col-12 col-md-4">
                            <span class="jb-detail-label">Operating Location</span>
                            <span class="jb-detail-value">{{ $lead->business_location ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3 class="jb-section-title border-bottom pb-2 mb-3">Operations Metrics</h3>
                    <div class="row g-4">
                        <div class="col-6 col-md">
                            <span class="jb-detail-label">Experience (in Years)</span>
                            <span class="jb-detail-value">{{ $lead->business_experience ?: '—' }}</span>
                        </div>
                        <div class="col-6 col-md">
                            <span class="jb-detail-label">Total Manpower</span>
                            <span class="jb-detail-value">{{ $lead->no_of_manpower ?: '—' }}</span>
                        </div>
                        <div class="col-6 col-md">
                            <span class="jb-detail-label">Premises Area Size</span>
                            <span class="jb-detail-value">{{ $lead->area_of_premises ?: '—' }}</span>
                        </div>
                        <div class="col-6 col-md">
                            <span class="jb-detail-label">Land & Factory Building</span>
                            <span class="jb-detail-value">{{ $lead->land_and_factory_building ?: '—' }}</span>
                        </div>
                        <div class="col-6 col-md">
                            <span class="jb-detail-label">Site Connectivity</span>
                            <span class="jb-detail-value">{{ $lead->connectivity ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="jb-section-title border-bottom pb-2 mb-3">GST Registration</h3>
                    <div class="row g-4">
                        <div class="col-6 col-md-3">
                            <span class="jb-detail-label">GST Applicable</span>
                            <span class="jb-detail-value">{{ $lead->gst_applicable ?: '—' }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="jb-detail-label">GSTIN Number</span>
                            <span class="jb-detail-value font-monospace">{{ $lead->gst_number ?: '—' }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="jb-detail-label">GST Portal User ID</span>
                            <span class="jb-detail-value">{{ $lead->gst_id ?: '—' }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="jb-detail-label">GST Portal Password</span>
                            <span class="jb-detail-value font-monospace">{{ $lead->gst_password ?: '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL 4: Financials & Active Liabilities -->
            <div id="finance-panel" class="tab-panel jb-hidden">
                <div class="mb-4">
                    <h3 class="jb-section-title mb-3">Active Bank Accounts</h3>
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
                        <div class="table-responsive rounded-3" style="border:1px solid var(--slate-200)">
                            <table class="jb-table">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:3rem">SR</th>
                                        <th>Bank Name</th>
                                        <th>Account Number</th>
                                        <th>Account Type</th>
                                        <th>IFSC Code</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lead->bank_details as $index => $bank)
                                        @if (!empty($bank['bank_name']) || !empty($bank['account_number']))
                                            <tr>
                                                <td class="text-center fw-semibold" style="color:var(--slate-400)">{{ $index + 1 }}</td>
                                                <td class="jb-cell-strong">{{ $bank['bank_name'] ?: '—' }}</td>
                                                <td class="font-monospace">{{ $bank['account_number'] ?: '—' }}</td>
                                                <td>{{ $bank['account_type'] ?: '—' }}</td>
                                                <td class="font-monospace">{{ $bank['ifsc_code'] ?: '—' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="mb-0" style="font-size:var(--text-xs);color:var(--slate-400)">No bank details recorded.</p>
                    @endif
                </div>

                <div class="pt-3">
                    <h3 class="d-flex align-items-center gap-2 border-bottom pb-2 mb-3" style="font-size:var(--text-sm);font-weight:var(--font-weight-bold);color:var(--rose-600)">
                        <span class="jb-dot jb-dot-rose"></span>
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
                        <div class="table-responsive rounded-3" style="border:1px solid var(--slate-200)">
                            <table class="jb-table">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:3rem">SR</th>
                                        <th>Creditor Bank</th>
                                        <th>Liability Type</th>
                                        <th>Sanctioned Amount</th>
                                        <th>Disbursal Date</th>
                                        <th>Monthly EMI</th>
                                        <th>Outstanding Balance</th>
                                        <th>Tenure</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lead->current_loans as $index => $loan)
                                        @if (!empty($loan['bank_name']) || !empty($loan['loan_amount']))
                                            <tr>
                                                <td class="text-center fw-semibold" style="color:var(--slate-400)">{{ $index + 1 }}</td>
                                                <td class="jb-cell-strong">{{ $loan['bank_name'] ?: '—' }}</td>
                                                <td>{{ $loan['loan_type'] ?: '—' }}</td>
                                                <td class="fw-bold" style="color:var(--rose-600)">{{ $loan['loan_amount'] ?: '—' }}</td>
                                                <td>{{ $loan['disburse_date'] ?: '—' }}</td>
                                                <td>{{ $loan['emi'] ?: '—' }}</td>
                                                <td class="fw-semibold">{{ $loan['outstanding_amount'] ?: '—' }}</td>
                                                <td>{{ $loan['tenure'] ?: '—' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="mb-0" style="font-size:var(--text-xs);color:var(--slate-400)">No active liabilities recorded.</p>
                    @endif
                </div>
            </div>

            <!-- PANEL 5: Documents Checklist & Files -->
            @php
                $docTypesShowList = $documentTypes ?? \App\Models\DocumentType::where('status', 'active')->ordered()->get();
                $groupedLeadDocs = $lead->leadDocuments->groupBy('document_type_id');
                $totalDocTypes = $docTypesShowList->count();
            @endphp
            <div id="documents-panel" class="tab-panel jb-hidden">
                <p class="mb-3" style="font-size:var(--text-xs);color:var(--slate-400)">{{ $uploadedDocCount }} of {{ $totalDocTypes }} uploaded</p>

                <div class="rounded-3" style="border:1px solid var(--slate-200);overflow:hidden">
                    @foreach ($docTypesShowList as $type)
                        @php
                            $docsForType = $groupedLeadDocs->get($type->id, collect());
                            $hasDocs = $docsForType->isNotEmpty();
                        @endphp
                        <div class="px-4 py-3" style="border-top:1px solid var(--slate-100)">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <span class="jb-dot flex-shrink-0" style="background:{{ $hasDocs ? 'var(--emerald-500)' : 'var(--slate-300)' }}"></span>
                                <span class="fw-medium text-truncate" style="font-size:var(--text-sm);color:var(--slate-800)">{{ $type->name }}</span>
                            </div>

                            @if ($hasDocs)
                                <div class="mt-2 ms-4 d-flex flex-column gap-1">
                                    @foreach ($docsForType as $docItem)
                                        <div class="d-flex align-items-center justify-content-between gap-3 px-2 py-2 rounded-2" style="background:color-mix(in oklab, var(--slate-50) 70%, transparent);font-size:var(--text-xs)">
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                <svg width="14" height="14" style="color:var(--slate-400)" class="flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                @if ($docItem->side)
                                                    <span class="text-capitalize fw-semibold flex-shrink-0" style="color:var(--slate-500)">{{ $docItem->side }}</span>
                                                    <span class="flex-shrink-0" style="color:var(--slate-300)">/</span>
                                                @endif
                                                <span class="text-truncate" style="color:var(--slate-600)" title="{{ $docItem->original_name }}">{{ $docItem->original_name }}</span>
                                                <span class="flex-shrink-0" style="color:var(--slate-400)">{{ $docItem->formatted_file_size }}</span>
                                            </div>

                                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                <a href="{{ route('admin.leads.documents.download', [$lead, $docItem]) }}" download title="Download" class="jb-icon-btn" style="width:1.75rem;height:1.75rem">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                </a>
                                                @can('leads.edit')
                                                    <form action="{{ route('admin.leads.documents.destroy', [$lead, $docItem]) }}" method="POST" onsubmit="return confirm('Remove this document?')" class="d-inline-block">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" title="Delete" class="jb-icon-btn" style="width:1.75rem;height:1.75rem">
                                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Notes / Requirements -->
    @if ($lead->notes)
        <div class="jb-card p-4 mb-4">
            <h3 class="jb-section-title border-bottom pb-2 mb-3">Notes & Requirements</h3>
            <p class="mb-0" style="font-size:var(--text-xs);color:var(--slate-600);line-height:var(--leading-relaxed);white-space:pre-line">{{ $lead->notes }}</p>
        </div>
    @endif

    <!-- Schedule Visit Dialog -->
    <dialog id="scheduleVisitModal" class="jb-dialog">
        <div class="jb-dialog-header">
            <h3 class="jb-dialog-title m-0">Schedule Visit</h3>
            <button type="button" onclick="document.getElementById('scheduleVisitModal').close()" class="jb-dialog-close">&times;</button>
        </div>
        <form action="{{ route('admin.leads.visits.store', $lead) }}" method="POST" class="d-flex flex-column gap-4">
            @csrf
            <input type="hidden" name="status" value="scheduled">

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="jb-form-label">
                        Representative <span style="color:var(--rose-500)">*</span>
                    </label>
                    <select name="employee_id" id="visit_employee_id" required class="form-select select2">
                        <option value="">Select Employee...</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $lead->assigned_employee_id == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label for="visit_date" class="jb-form-label">
                        Visit Date & Time <span style="color:var(--rose-500)">*</span>
                    </label>
                    <input type="datetime-local" name="visit_date" id="visit_date" required class="form-control">
                </div>
            </div>

            <div>
                <label for="visit_purpose" class="jb-form-label">
                    Purpose <span style="color:var(--rose-500)">*</span>
                </label>
                <textarea name="purpose" id="visit_purpose" required rows="3" placeholder="Describe visit purpose..." class="form-control"></textarea>
            </div>

            <div>
                <label for="visit_notes" class="jb-form-label">Notes (Optional)</label>
                <textarea name="notes" id="visit_notes" rows="2" placeholder="Optional pre-visit notes..." class="form-control"></textarea>
            </div>

            <div class="d-flex align-items-center gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary btn-sm">Schedule</button>
                <button type="button" onclick="document.getElementById('scheduleVisitModal').close()" class="btn btn-secondary btn-sm">Cancel</button>
            </div>
        </form>
    </dialog>

    <!-- Update Visit Dialog -->
    <dialog id="updateVisitModal" class="jb-dialog">
        <div class="jb-dialog-header">
            <h3 class="jb-dialog-title m-0">Update Visit Status</h3>
            <button type="button" onclick="document.getElementById('updateVisitModal').close()" class="jb-dialog-close">&times;</button>
        </div>
        <form id="updateVisitForm" method="POST" class="d-flex flex-column gap-4">
            @csrf
            @method('PUT')

            <input type="hidden" name="employee_id" id="edit_visit_employee_id">
            <input type="hidden" name="visit_date" id="edit_visit_date">
            <input type="hidden" name="purpose" id="edit_visit_purpose">

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="jb-form-label">
                        Visit Status <span style="color:var(--rose-500)">*</span>
                    </label>
                    <select name="status" id="edit_visit_status" required class="form-select select2">
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="rescheduled">Rescheduled</option>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label for="edit_visit_next_follow_up" class="jb-form-label">Next Follow-up Date (Optional)</label>
                    <input type="date" name="next_follow_up_date" id="edit_visit_next_follow_up" class="form-control">
                </div>
            </div>

            <div>
                <label for="edit_visit_outcome" class="jb-form-label">Outcome / Findings</label>
                <textarea name="outcome" id="edit_visit_outcome" rows="2" placeholder="Outcome findings..." class="form-control"></textarea>
            </div>

            <div>
                <label for="edit_visit_next_action" class="jb-form-label">Next Steps</label>
                <textarea name="next_action" id="edit_visit_next_action" rows="2" placeholder="Next steps..." class="form-control"></textarea>
            </div>

            <div>
                <label for="edit_visit_notes" class="jb-form-label">Notes / Feedback</label>
                <textarea name="notes" id="edit_visit_notes" rows="2" placeholder="Remarks..." class="form-control"></textarea>
            </div>

            <div class="d-flex align-items-center gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                <button type="button" onclick="document.getElementById('updateVisitModal').close()" class="btn btn-secondary btn-sm">Cancel</button>
            </div>
        </form>
    </dialog>

    <script>
        function switchTab(tabId, panelId) {
            document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.add('jb-hidden'));
            document.getElementById(panelId).classList.remove('jb-hidden');

            document.querySelectorAll('.jb-tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
        }

        function openEditVisitModal(visit) {
            const form = document.getElementById('updateVisitForm');
            form.action = `/admin/leads/${visit.lead_id}/visits/${visit.id}`;

            document.getElementById('edit_visit_employee_id').value = visit.employee_id;
            document.getElementById('edit_visit_date').value = visit.visit_date.replace(' ', 'T');
            document.getElementById('edit_visit_purpose').value = visit.purpose;

            document.getElementById('edit_visit_status').value = visit.status;
            document.getElementById('edit_visit_outcome').value = visit.outcome || '';
            document.getElementById('edit_visit_next_action').value = visit.next_action || '';
            document.getElementById('edit_visit_next_follow_up').value = visit.next_follow_up_date || '';
            document.getElementById('edit_visit_notes').value = visit.notes || '';

            document.getElementById('updateVisitModal').showModal();
            if (window.initSelect2) {
                window.initSelect2('#updateVisitModal select');
            }
        }
    </script>
</x-admin-bootstrap-layout>
