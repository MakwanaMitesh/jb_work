<x-admin-bootstrap-layout :title="$employee->name">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            @if ($employee->profilePhotoUrl())
                <img src="{{ $employee->profilePhotoUrl() }}" alt="" class="rounded-circle object-fit-cover flex-shrink-0" width="56" height="56">
            @else
                <span class="jb-brand-mark rounded-circle text-uppercase fw-bold" style="width:3.5rem;height:3.5rem;font-size:var(--text-lg)">
                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                </span>
            @endif
            <div>
                <h1 class="jb-page-title">{{ $employee->name }}</h1>
                <p class="m-0 mt-1" style="font-size:var(--text-sm);color:var(--slate-500)">{{ $employee->email }}</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @can('employees.edit')
                <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-primary">Edit</a>
            @endcan
            <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="jb-card h-100 p-4">
                <h3 class="jb-section-title mb-3">Basic Information</h3>
                <dl class="jb-dl">
                    <dt>First Name</dt>
                    <dd>{{ $employee->first_name ?: '—' }}</dd>
                    <dt>Middle Name</dt>
                    <dd>{{ $employee->middle_name ?: '—' }}</dd>
                    <dt>Last Name</dt>
                    <dd>{{ $employee->last_name ?: '—' }}</dd>
                    <dt>Mobile</dt>
                    <dd>{{ $employee->mobile_number ?: '—' }}</dd>
                    <dt>Alternate Mobile</dt>
                    <dd>{{ $employee->alternate_mobile_number ?: '—' }}</dd>
                    <dt>City</dt>
                    <dd>{{ $employee->city?->name ?: '—' }}</dd>
                    <dt>Address</dt>
                    <dd>{{ $employee->address ?: '—' }}</dd>
                    <dt>Joining Date</dt>
                    <dd>{{ $employee->joining_date?->format('d M Y') ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="jb-card h-100 p-4">
                <h3 class="jb-section-title mb-3">Account Status</h3>
                <dl class="jb-dl mb-4">
                    <dt>Status</dt>
                    <dd>
                        @if ($employee->isActive())
                            <span class="jb-badge jb-badge-sm jb-badge-emerald">Active</span>
                        @else
                            <span class="jb-badge jb-badge-sm jb-badge-slate">Inactive</span>
                        @endif
                    </dd>
                    <dt>Role</dt>
                    <dd>
                        @forelse ($employee->roles as $role)
                            <span class="jb-badge jb-badge-sm jb-badge-primary">{{ $role->name }}</span>
                            @if (! $role->is_active)
                                <span class="jb-badge jb-badge-sm jb-badge-red mt-1">role deactivated</span>
                            @endif
                        @empty
                            <span style="color:var(--slate-400)">No role assigned</span>
                        @endforelse
                    </dd>
                    <dt>Account created</dt>
                    <dd>{{ $employee->created_at->format('d M Y') }}</dd>
                    <dt>Email verified</dt>
                    <dd>{{ $employee->email_verified_at ? 'Yes' : 'Not yet (pending password setup)' }}</dd>
                </dl>

                @can('managePermissions', $employee)
                    <a href="{{ route('admin.users.permissions.edit', $employee) }}" class="btn btn-secondary btn-sm">
                        Manage Direct Permissions
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="jb-card h-100 p-4">
                <h3 class="jb-section-title mb-3">Insurance Details</h3>
                <dl class="jb-dl">
                    <dt>Policy Number</dt>
                    <dd>{{ $employee->insurance_policy_number ?: '—' }}</dd>

                    <dt>Policy Document</dt>
                    <dd>
                        @if ($employee->insurance_policy_pdf_path)
                            <div class="jb-file-actions">
                                <button type="button" onclick="window.previewFilePopup('{{ $employee->insurancePolicyPdfUrl() }}')" class="jb-file-link">Preview</button>
                                <span class="jb-file-sep">|</span>
                                <a href="{{ $employee->insurancePolicyPdfUrl() }}" download class="jb-file-link-muted">Download</a>
                            </div>
                        @else
                            —
                        @endif
                    </dd>

                    <dt>Start Date</dt>
                    <dd>{{ $employee->insurance_start_date?->format('d M Y') ?? '—' }}</dd>

                    <dt>End Date</dt>
                    <dd>
                        {{ $employee->insurance_end_date?->format('d M Y') ?? '—' }}
                        @if ($employee->hasExpiredInsurance())
                            <span class="jb-badge jb-badge-sm jb-badge-red ms-2">Expired</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="jb-card h-100 p-4">
                <h3 class="jb-section-title mb-3">Qualifications</h3>
                <dl class="jb-dl">
                    <dt>Qualification</dt>
                    <dd>{{ $employee->qualification ?: '—' }}</dd>

                    <dt>Resume</dt>
                    <dd>
                        @if ($employee->resume_path)
                            <div class="jb-file-actions">
                                <button type="button" onclick="window.previewFilePopup('{{ $employee->resumeUrl() }}')" class="jb-file-link">Preview</button>
                                <span class="jb-file-sep">|</span>
                                <a href="{{ $employee->resumeUrl() }}" download class="jb-file-link-muted">Download</a>
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
                    <dd>{{ $employee->aadhaar_card_number ?: '—' }}</dd>

                    <dt>Aadhaar Photo</dt>
                    <dd>
                        @if ($employee->aadhaar_photo_path)
                            <div class="jb-file-actions">
                                <button type="button" onclick="window.previewFilePopup('{{ $employee->aadhaarPhotoUrl() }}')" class="jb-file-link">Preview</button>
                                <span class="jb-file-sep">|</span>
                                <a href="{{ $employee->aadhaarPhotoUrl() }}" download class="jb-file-link-muted">Download</a>
                            </div>
                        @else
                            <span style="color:var(--slate-400)">—</span>
                        @endif
                    </dd>

                    <dt>PAN Number</dt>
                    <dd class="text-uppercase">{{ $employee->pan_card_number ?: '—' }}</dd>

                    <dt>PAN Photo</dt>
                    <dd>
                        @if ($employee->pan_photo_path)
                            <div class="jb-file-actions">
                                <button type="button" onclick="window.previewFilePopup('{{ $employee->panPhotoUrl() }}')" class="jb-file-link">Preview</button>
                                <span class="jb-file-sep">|</span>
                                <a href="{{ $employee->panPhotoUrl() }}" download class="jb-file-link-muted">Download</a>
                            </div>
                        @else
                            <span style="color:var(--slate-400)">—</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="jb-card h-100 p-4">
                <h3 class="jb-section-title mb-3">Bank Details</h3>
                <dl class="jb-dl">
                    <dt>Bank Name</dt>
                    <dd>{{ $employee->bank_name ?: '—' }}</dd>

                    <dt>Holder Name</dt>
                    <dd>{{ $employee->bank_account_holder_name ?: '—' }}</dd>

                    <dt>Account Number</dt>
                    <dd>{{ $employee->bank_account_number ?: '—' }}</dd>

                    <dt>IFSC Code</dt>
                    <dd class="text-uppercase">{{ $employee->bank_ifsc_code ?: '—' }}</dd>

                    <dt>Cancelled Cheque</dt>
                    <dd>
                        @if ($employee->bank_cheque_photo_path)
                            <div class="jb-file-actions">
                                <button type="button" onclick="window.previewFilePopup('{{ $employee->bankChequePhotoUrl() }}')" class="jb-file-link">Preview</button>
                                <span class="jb-file-sep">|</span>
                                <a href="{{ $employee->bankChequePhotoUrl() }}" download class="jb-file-link-muted">Download</a>
                            </div>
                        @else
                            <span style="color:var(--slate-400)">—</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="jb-card p-4 mb-4">
        <h3 class="jb-section-title mb-1">Effective Permissions</h3>
        <p class="jb-muted mb-3">Role permissions (from active roles) + permissions granted directly to this user.</p>

        @php $effective = $employee->effectivePermissionNames(); @endphp

        @if ($effective->isEmpty())
            <p class="m-0" style="color:var(--slate-400)">No permissions granted yet.</p>
        @else
            <div class="d-flex flex-wrap gap-2">
                @foreach ($effective as $permission)
                    <span class="jb-badge jb-badge-sm jb-badge-slate">{{ $permission }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="row g-4">
        <div class="col-12 col-md-4">
            <div class="jb-card h-100 p-4">
                <h3 class="fw-bold mb-2" style="font-size:var(--text-sm);color:var(--slate-900)">Assigned / Pending Tasks</h3>
                <p class="jb-muted m-0">Task Management module is not implemented yet.</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="jb-card h-100 p-4">
                <h3 class="fw-bold mb-2" style="font-size:var(--text-sm);color:var(--slate-900)">Completed Tasks</h3>
                <p class="jb-muted m-0">Task Management module is not implemented yet.</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="jb-card h-100 p-4">
                <h3 class="fw-bold mb-2" style="font-size:var(--text-sm);color:var(--slate-900)">EOD Submissions</h3>
                <p class="jb-muted m-0">EOD module is not implemented yet.</p>
            </div>
        </div>
    </div>
</x-admin-bootstrap-layout>
