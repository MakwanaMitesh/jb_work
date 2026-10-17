<x-admin-bootstrap-layout title="Contact Enquiry #{{ $contactEnquiry->id }}">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.contact-enquiries.index') }}">Contact Enquiries</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">{{ $contactEnquiry->full_name }}</span>
        </nav>

        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
            <div>
                <h1 class="jb-page-title d-flex align-items-center gap-3">
                    {{ $contactEnquiry->full_name }}
                    <span class="jb-badge {{ $contactEnquiry->status_badge_class }}">
                        {{ $contactEnquiry->status }}
                    </span>
                </h1>
                <p class="m-0 mt-1" style="font-size:var(--text-sm);color:var(--slate-500)">
                    Submitted on {{ $contactEnquiry->created_at?->format('F d, Y \a\t h:i A') }} ({{ $contactEnquiry->created_at?->diffForHumans() }})
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.contact-enquiries.index') }}" class="btn btn-secondary">
                    Back to List
                </a>
                @can('contact_enquiry.delete')
                    <form method="POST" action="{{ route('admin.contact-enquiries.destroy', $contactEnquiry) }}" onsubmit="return confirm('Are you sure you want to delete this enquiry?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            Delete
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="jb-alert jb-alert-success fw-medium mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="jb-card p-4 mb-4">
                <h2 class="fw-bold mb-3" style="font-size:var(--text-lg);color:var(--slate-800)">Enquiry Information</h2>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="d-block text-uppercase fw-semibold" style="font-size:var(--text-xs);color:var(--slate-400)">Full Name</label>
                        <div class="fw-semibold mt-1" style="font-size:var(--text-base);color:var(--slate-900)">{{ $contactEnquiry->full_name }}</div>
                    </div>

                    <div class="col-sm-6">
                        <label class="d-block text-uppercase fw-semibold" style="font-size:var(--text-xs);color:var(--slate-400)">Mobile Number</label>
                        <div class="mt-1">
                            <a href="tel:{{ $contactEnquiry->mobile }}" class="fw-semibold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $contactEnquiry->mobile }}
                            </a>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <label class="d-block text-uppercase fw-semibold" style="font-size:var(--text-xs);color:var(--slate-400)">Email Address</label>
                        <div class="mt-1">
                            @if($contactEnquiry->email)
                                <a href="mailto:{{ $contactEnquiry->email }}" class="text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    {{ $contactEnquiry->email }}
                                </a>
                            @else
                                <span style="color:var(--slate-400)">Not provided</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <label class="d-block text-uppercase fw-semibold" style="font-size:var(--text-xs);color:var(--slate-400)">City</label>
                        <div class="fw-medium mt-1" style="color:var(--slate-800)">{{ $contactEnquiry->city ?? 'Not provided' }}</div>
                    </div>

                    <div class="col-12">
                        <label class="d-block text-uppercase fw-semibold" style="font-size:var(--text-xs);color:var(--slate-400)">Loan / Product Requirement</label>
                        <div class="fw-semibold mt-1 p-2 rounded bg-light border" style="color:var(--slate-900)">
                            {{ $contactEnquiry->loan_requirement ?? 'General Financial / CA Enquiry' }}
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="d-block text-uppercase fw-semibold" style="font-size:var(--text-xs);color:var(--slate-400)">Message / Requirement Details</label>
                        <div class="mt-1 p-3 rounded bg-light border" style="color:var(--slate-800);white-space:pre-line;line-height:1.6">
                            {{ $contactEnquiry->message ?: 'No additional message provided.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="jb-card p-4 mb-4">
                <h2 class="fw-bold mb-3" style="font-size:var(--text-lg);color:var(--slate-800)">Status & Actions</h2>

                @can('contact_enquiry.update')
                    <form method="POST" action="{{ route('admin.contact-enquiries.status', $contactEnquiry) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:var(--text-sm)">Update Status</label>
                            <select name="status" class="form-select">
                                @foreach (\App\Models\ContactEnquiry::STATUSES as $val => $lbl)
                                    <option value="{{ $val }}" @selected($contactEnquiry->status === $val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Save Status
                        </button>
                    </form>
                @else
                    <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light border">
                        <span class="fw-medium" style="font-size:var(--text-sm)">Current Status:</span>
                        <span class="jb-badge {{ $contactEnquiry->status_badge_class }}">{{ $contactEnquiry->status }}</span>
                    </div>
                @endcan

                <hr class="my-4">

                <div class="d-flex flex-column gap-2" style="font-size:var(--text-xs);color:var(--slate-500)">
                    <div class="d-flex justify-content-between">
                        <span>Submitted:</span>
                        <span class="fw-medium text-slate-700">{{ $contactEnquiry->created_at?->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Last Updated:</span>
                        <span class="fw-medium text-slate-700">{{ $contactEnquiry->updated_at?->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-bootstrap-layout>
