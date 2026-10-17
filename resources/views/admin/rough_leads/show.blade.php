<x-admin-bootstrap-layout :title="'Rough Lead — ' . $roughLead->full_name">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.rough-leads.index') }}">Rough Leads</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">{{ $roughLead->full_name }}</span>
        </nav>

        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.rough-leads.index') }}" class="jb-back-btn">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="jb-page-title">{{ $roughLead->full_name }}</h1>
            </div>

            <div class="d-flex align-items-center gap-3">
                @can('rough_leads.edit')
                    <a href="{{ route('admin.rough-leads.edit', $roughLead) }}" class="btn btn-secondary">Edit</a>
                @endcan
                @can('rough_leads.convert')
                    @if (! $roughLead->isConverted())
                        <form method="POST" action="{{ route('admin.rough-leads.convert', $roughLead) }}" onsubmit="return confirm('Convert this Rough Lead into a Main Lead? This cannot be undone.');">
                            @csrf
                            <button type="submit" class="btn btn-primary">Convert to Main Lead</button>
                        </form>
                    @endif
                @endcan
            </div>
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

    @if ($roughLead->isConverted())
        <div class="jb-alert jb-alert-success fw-medium mb-4 d-flex align-items-center justify-content-between">
            <span>This Rough Lead was converted to a Main Lead on {{ $roughLead->converted_at?->format('M d, Y H:i') }}.</span>
            @can('leads.view')
                <a href="{{ route('admin.leads.show', $roughLead->converted_lead_id) }}" class="fw-bold text-decoration-underline">
                    View Main Lead &rarr;
                </a>
            @endcan
        </div>
    @endif

    <div class="jb-panel d-flex flex-column gap-4">
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="jb-muted">Full Name</div>
                <div class="fw-semibold">{{ $roughLead->full_name }}</div>
            </div>
            <div class="col-12 col-md-6">
                <div class="jb-muted">Mobile Number</div>
                <div class="fw-semibold">{{ $roughLead->mobile_number }}</div>
            </div>
            <div class="col-12 col-md-6">
                <div class="jb-muted">Alternate Mobile Number</div>
                <div class="fw-semibold">{{ $roughLead->alternate_mobile_number ?? '—' }}</div>
            </div>
            <div class="col-12 col-md-6">
                <div class="jb-muted">Email</div>
                <div class="fw-semibold">{{ $roughLead->email ?? '—' }}</div>
            </div>
            <div class="col-12 col-md-6">
                <div class="jb-muted">City / Location</div>
                <div class="fw-semibold">{{ $roughLead->city?->name ?? '—' }}</div>
            </div>
            <div class="col-12 col-md-6">
                <div class="jb-muted">Loan Product / Requirement</div>
                <div class="fw-semibold">{{ $roughLead->loan_requirement ?? '—' }}</div>
            </div>
            <div class="col-12 col-md-6">
                <div class="jb-muted">Source / Referral</div>
                <div class="fw-semibold">{{ $roughLead->source ?? '—' }}</div>
            </div>
            <div class="col-12 col-md-6">
                <div class="jb-muted">Assigned Employee</div>
                <div class="fw-semibold">{{ $roughLead->assignedEmployee?->name ?? '—' }}</div>
            </div>
            <div class="col-12 col-md-6">
                <div class="jb-muted">Status</div>
                <div class="fw-semibold">{{ \App\Models\RoughLead::STATUSES[$roughLead->status] ?? ucfirst($roughLead->status) }}</div>
            </div>
            <div class="col-12 col-md-6">
                <div class="jb-muted">Created Date</div>
                <div class="fw-semibold">{{ $roughLead->created_at?->format('M d, Y H:i') ?? '—' }}</div>
            </div>
            <div class="col-12">
                <div class="jb-muted">Description / Enquiry Notes</div>
                <div class="fw-semibold" style="white-space:pre-line">{{ $roughLead->description ?? '—' }}</div>
            </div>
        </div>
    </div>
</x-admin-bootstrap-layout>
