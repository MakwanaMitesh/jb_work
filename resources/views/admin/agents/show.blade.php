<x-admin-bootstrap-layout :title="$agent->name">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            @if ($agent->profilePhotoUrl())
                <img src="{{ $agent->profilePhotoUrl() }}" alt="" class="rounded-circle object-fit-cover flex-shrink-0" width="56" height="56">
            @else
                @php
                    $initials = '';
                    if ($agent->first_name && $agent->last_name) {
                        $initials = strtoupper(substr($agent->first_name, 0, 1) . substr($agent->last_name, 0, 1));
                    } else {
                        $initials = strtoupper(substr($agent->first_name ?? $agent->email, 0, 2));
                    }
                @endphp
                <span class="jb-avatar jb-avatar-lg">{{ $initials }}</span>
            @endif
            <div>
                <h1 class="jb-page-title">{{ $agent->name }}</h1>
                <p class="m-0 mt-1" style="font-size:var(--text-sm);color:var(--slate-500)">{{ $agent->email }}</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @can('agent.edit')
                <a href="{{ route('admin.agents.edit', $agent) }}" class="btn btn-primary">Edit</a>
            @endcan
            <a href="{{ route('admin.agents.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="jb-card h-100 p-4">
                <h3 class="jb-section-title mb-3">Basic Information</h3>
                <dl class="jb-dl">
                    <dt>First Name</dt>
                    <dd>{{ $agent->first_name ?: '—' }}</dd>
                    <dt>Last Name</dt>
                    <dd>{{ $agent->last_name ?: '—' }}</dd>
                    <dt>Mobile</dt>
                    <dd>{{ $agent->mobile_number ?: '—' }}</dd>
                    <dt>Alternate Mobile</dt>
                    <dd>{{ $agent->alternate_mobile_number ?: '—' }}</dd>
                    <dt>City</dt>
                    <dd>{{ $agent->city?->name ?: '—' }}</dd>
                    <dt>Address</dt>
                    <dd>{{ $agent->address ?: '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="jb-card h-100 p-4">
                <h3 class="jb-section-title mb-3">Agent Status</h3>
                <dl class="jb-dl">
                    <dt>Status</dt>
                    <dd>
                        @if ($agent->isActive())
                            <span class="jb-badge jb-badge-sm jb-badge-emerald">Active</span>
                        @else
                            <span class="jb-badge jb-badge-sm jb-badge-slate">Inactive</span>
                        @endif
                    </dd>
                    <dt>Created Date</dt>
                    <dd>{{ $agent->created_at?->format('d M Y') ?? '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="jb-card h-100 p-4">
                <h3 class="jb-section-title mb-3">Qualifications</h3>
                <dl class="jb-dl">
                    <dt>Qualification</dt>
                    <dd>{{ $agent->qualification ?: '—' }}</dd>

                    <dt>Resume</dt>
                    <dd>
                        @if ($agent->resume_path)
                            <div class="jb-file-actions">
                                <button type="button" onclick="window.previewFilePopup('{{ $agent->resumeUrl() }}')" class="jb-file-link">Preview</button>
                                <span class="jb-file-sep">|</span>
                                <a href="{{ $agent->resumeUrl() }}" download class="jb-file-link-muted">Download</a>
                            </div>
                        @else
                            —
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="jb-card h-100 p-4">
                <h3 class="jb-section-title mb-3">KYC Verification Documents</h3>
                <dl class="jb-dl jb-dl-roomy">
                    <dt>Aadhaar Number</dt>
                    <dd>{{ $agent->aadhaar_card_number ?: '—' }}</dd>

                    <dt>Aadhaar Photo</dt>
                    <dd>
                        @if ($agent->aadhaar_photo_path)
                            <div class="jb-file-actions">
                                <button type="button" onclick="window.previewFilePopup('{{ $agent->aadhaarPhotoUrl() }}')" class="jb-file-link">Preview</button>
                                <span class="jb-file-sep">|</span>
                                <a href="{{ $agent->aadhaarPhotoUrl() }}" download class="jb-file-link-muted">Download</a>
                            </div>
                        @else
                            <span style="color:var(--slate-400)">—</span>
                        @endif
                    </dd>

                    <dt>PAN Number</dt>
                    <dd class="text-uppercase">{{ $agent->pan_card_number ?: '—' }}</dd>

                    <dt>PAN Photo</dt>
                    <dd>
                        @if ($agent->pan_photo_path)
                            <div class="jb-file-actions">
                                <button type="button" onclick="window.previewFilePopup('{{ $agent->panPhotoUrl() }}')" class="jb-file-link">Preview</button>
                                <span class="jb-file-sep">|</span>
                                <a href="{{ $agent->panPhotoUrl() }}" download class="jb-file-link-muted">Download</a>
                            </div>
                        @else
                            <span style="color:var(--slate-400)">—</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        <div class="col-12">
            <div class="jb-card p-4">
                <h3 class="jb-section-title mb-3">Bank Details</h3>
                <dl class="jb-dl">
                    <dt>Bank Name</dt>
                    <dd>{{ $agent->bank_name ?: '—' }}</dd>

                    <dt>Holder Name</dt>
                    <dd>{{ $agent->bank_account_holder_name ?: '—' }}</dd>

                    <dt>Account Number</dt>
                    <dd>{{ $agent->bank_account_number ?: '—' }}</dd>

                    <dt>IFSC Code</dt>
                    <dd class="text-uppercase">{{ $agent->bank_ifsc_code ?: '—' }}</dd>

                    <dt>Cancelled Cheque</dt>
                    <dd>
                        @if ($agent->bank_cheque_photo_path)
                            <div class="jb-file-actions">
                                <button type="button" onclick="window.previewFilePopup('{{ $agent->bankChequePhotoUrl() }}')" class="jb-file-link">Preview</button>
                                <span class="jb-file-sep">|</span>
                                <a href="{{ $agent->bankChequePhotoUrl() }}" download class="jb-file-link-muted">Download</a>
                            </div>
                        @else
                            <span style="color:var(--slate-400)">—</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</x-admin-bootstrap-layout>
