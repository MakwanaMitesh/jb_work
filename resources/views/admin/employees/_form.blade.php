{{-- Expects: $employee (nullable, for edit), $roles --}}
@php $employee = $employee ?? null; @endphp

<div class="jb-form-card">
    <div class="row g-4">
        <div class="col-12 col-md-4">
            <x-input-label-bs for="first_name" value="First Name" :required="true" />
            <x-text-input-bs id="first_name" type="text" name="first_name" :value="old('first_name', $employee?->first_name)" required autofocus />
            <x-input-error-bs :messages="$errors->get('first_name')" />
        </div>
        <div class="col-12 col-md-4">
            <x-input-label-bs for="middle_name" value="Middle Name" />
            <x-text-input-bs id="middle_name" type="text" name="middle_name" :value="old('middle_name', $employee?->middle_name)" />
            <x-input-error-bs :messages="$errors->get('middle_name')" />
        </div>
        <div class="col-12 col-md-4">
            <x-input-label-bs for="last_name" value="Last Name" :required="true" />
            <x-text-input-bs id="last_name" type="text" name="last_name" :value="old('last_name', $employee?->last_name)" required />
            <x-input-error-bs :messages="$errors->get('last_name')" />
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12 col-md-4">
            <x-input-label-bs for="email" value="Email" :required="true" />
            <x-text-input-bs id="email" type="email" name="email" :value="old('email', $employee?->email)" required />
            <x-input-error-bs :messages="$errors->get('email')" />
        </div>
        <div class="col-12 col-md-4">
            <x-input-label-bs for="mobile_number" value="Mobile Number" :required="true" />
            <x-text-input-bs id="mobile_number" type="tel" name="mobile_number" :value="old('mobile_number', $employee?->mobile_number)" required />
            <x-input-error-bs :messages="$errors->get('mobile_number')" />
        </div>
        <div class="col-12 col-md-4">
            <x-input-label-bs for="alternate_mobile_number" value="Alternate Mobile Number" />
            <x-text-input-bs id="alternate_mobile_number" type="tel" name="alternate_mobile_number" :value="old('alternate_mobile_number', $employee?->alternate_mobile_number)" />
            <x-input-error-bs :messages="$errors->get('alternate_mobile_number')" />
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12 col-md-6">
            <x-input-label-bs for="password" :value="$employee ? 'New Password' : 'Password'" :required="! $employee" />
            <div class="input-group">
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control jb-input"
                    autocomplete="new-password"
                    @if (! $employee) required @endif
                >
                <button type="button" class="btn btn-outline-secondary jb-password-toggle" data-target="password" aria-label="Show password" aria-pressed="false">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="jb-eye-open"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="jb-eye-closed jb-hidden"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                </button>
            </div>
            @if ($employee)
                <div class="jb-muted mt-1">Leave blank to keep the current password. Existing passwords are stored encrypted and can't be displayed — only replaced.</div>
            @endif
            <x-input-error-bs :messages="$errors->get('password')" />
        </div>
    </div>

    @once
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.jb-password-toggle').forEach(function (btn) {
                    const input = document.getElementById(btn.dataset.target);
                    if (!input) return;
                    btn.addEventListener('click', function () {
                        const show = input.type === 'password';
                        input.type = show ? 'text' : 'password';
                        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                        btn.querySelector('.jb-eye-open').classList.toggle('jb-hidden', show);
                        btn.querySelector('.jb-eye-closed').classList.toggle('jb-hidden', !show);
                    });
                });
            });
        </script>
    @endonce

    <div class="row g-4 mt-1">
        <div class="col-12 col-md-6">
            <x-input-label-bs for="city_id" value="City" />
            <select id="city_id" name="city_id" class="form-select jb-input select2">
                <option value="">Select City</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}" @selected(old('city_id', $employee?->city_id) == $city->id)>{{ $city->name }}</option>
                @endforeach
            </select>
            <x-input-error-bs :messages="$errors->get('city_id')" />
        </div>
        <div class="col-12 col-md-6">
            <x-input-label-bs for="joining_date" value="Joining Date" />
            <x-text-input-bs id="joining_date" type="date" name="joining_date" :value="old('joining_date', $employee?->joining_date?->format('Y-m-d'))" />
            <x-input-error-bs :messages="$errors->get('joining_date')" />
        </div>
    </div>

    <div class="mt-4">
        <x-input-label-bs for="address" value="Address" />
        <textarea id="address" name="address" rows="3" class="jb-textarea">{{ old('address', $employee?->address) }}</textarea>
        <x-input-error-bs :messages="$errors->get('address')" />
    </div>

    @php $currentRole = old('role', $employee?->roles->first()->name ?? 'Employee'); @endphp

    <div class="row g-4 mt-1">
        <div class="col-12 col-md-6">
            <x-input-label-bs for="role" value="Role" :required="true" />
            <select id="role" name="role" class="form-select jb-input" @disabled(! auth()->user()->can('employees.assign_role'))>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected($currentRole === $role->name)>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
            @if (! auth()->user()->can('employees.assign_role'))
                <div class="jb-muted mt-1">You don't have permission to change the assigned role.</div>
                <input type="hidden" name="role" value="{{ $currentRole }}">
            @endif
            <x-input-error-bs :messages="$errors->get('role')" />
        </div>
        <div class="col-12 col-md-6">
            <x-input-label-bs for="status" value="Status" :required="true" />
            <select id="status" name="status" class="form-select jb-input">
                @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $employee?->status ?? 'active') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error-bs :messages="$errors->get('status')" />
        </div>
    </div>

    {{-- data-upload-field is the hook window.previewImage/clearImage resolve against. --}}
    <div class="jb-upload-panel d-flex flex-column flex-sm-row align-items-center gap-4" data-upload-field>
        <input type="hidden" name="remove_profile_photo" value="0">
        <div class="jb-upload-preview">
            @if ($employee?->profilePhotoUrl())
                <img src="{{ $employee->profilePhotoUrl() }}" alt="Profile preview" class="profile-preview-img">
            @else
                <div class="profile-placeholder-icon">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </div>
                <img class="profile-preview-img jb-hidden">
            @endif
        </div>
        <div class="flex-fill w-100">
            <x-input-label-bs for="profile_photo" value="Profile Photo" />
            <div class="d-flex align-items-center gap-3">
                <input id="profile_photo" type="file" name="profile_photo" class="jb-file-input" accept="image/*" onchange="window.previewImage(this)">

                <button type="button" id="remove_photo_btn" onclick="window.clearImage(this)" class="jb-btn-remove {{ ($employee?->profilePhotoUrl()) ? '' : 'jb-hidden' }}">
                    Remove
                </button>
            </div>
            <p class="jb-muted mt-2 mb-0">PNG, JPG or WEBP. Max 2MB.</p>
            <x-input-error-bs :messages="$errors->get('profile_photo')" />
        </div>
    </div>

    <div class="jb-form-section">
        <h3 class="jb-form-section-title">Qualifications &amp; Alternate Contact</h3>
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <x-input-label-bs for="qualification" value="Qualification" />
                <x-text-input-bs id="qualification" type="text" name="qualification" :value="old('qualification', $employee?->qualification)" placeholder="e.g. Bachelor of Science" />
                <x-input-error-bs :messages="$errors->get('qualification')" />
            </div>

            <div class="col-12 col-md-6" data-upload-field>
                <x-input-label-bs for="resume" value="Resume Upload (PDF/Word/Image)" />
                <input type="hidden" name="remove_resume" value="0">
                <div class="d-flex align-items-center gap-3">
                    <div class="jb-thumb">
                        @if ($employee?->resume_path)
                            @if (preg_match('/\.(pdf|doc|docx)$/i', $employee->resume_path))
                                <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $employee->resumeUrl() }}')" class="download-link jb-thumb-label">DOC</a>
                                <img class="resume-preview-img jb-hidden" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                            @else
                                <img src="{{ $employee->resumeUrl() }}" alt="Resume preview" class="resume-preview-img" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                            @endif
                        @else
                            <div class="resume-placeholder-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            </div>
                            <img class="resume-preview-img jb-hidden" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                        @endif
                    </div>
                    <input id="resume" type="file" name="resume" class="jb-file-input" accept="image/*,.pdf,.doc,.docx" onchange="window.previewImage(this, '#remove_resume_btn', '.resume-preview-img', '.resume-placeholder-icon')">
                    <button type="button" id="remove_resume_btn" onclick="window.clearFormFile(this, 'remove_resume', '.resume-preview-img', '.resume-placeholder-icon')" class="jb-btn-remove {{ ($employee?->resume_path) ? '' : 'jb-hidden' }}">
                        Remove
                    </button>
                </div>
                <x-input-error-bs :messages="$errors->get('resume')" />
            </div>
        </div>
    </div>

    <div class="jb-form-section">
        <h3 class="jb-form-section-title">Insurance Policy Details</h3>
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <x-input-label-bs for="insurance_policy_number" value="Insurance Policy Number" />
                <x-text-input-bs id="insurance_policy_number" type="text" name="insurance_policy_number" :value="old('insurance_policy_number', $employee?->insurance_policy_number)" placeholder="e.g. POL-123456" />
                <x-input-error-bs :messages="$errors->get('insurance_policy_number')" />
            </div>
            <div class="col-12 col-md-6" data-upload-field>
                <x-input-label-bs for="insurance_policy_pdf" value="Insurance Policy Document (PDF/Image)" />
                <input type="hidden" name="remove_insurance_policy_pdf" value="0">
                <div class="d-flex align-items-center gap-3">
                    <div class="jb-thumb">
                        @if ($employee?->insurance_policy_pdf_path)
                            @if (str_ends_with(strtolower($employee->insurance_policy_pdf_path), '.pdf'))
                                <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $employee->insurancePolicyPdfUrl() }}')" class="download-link jb-thumb-label">PDF</a>
                                <img class="insurance-preview-img jb-hidden" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                            @else
                                <img src="{{ $employee->insurancePolicyPdfUrl() }}" alt="Insurance preview" class="insurance-preview-img" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                            @endif
                        @else
                            <div class="insurance-placeholder-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            </div>
                            <img class="insurance-preview-img jb-hidden" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                        @endif
                    </div>
                    <input id="insurance_policy_pdf" type="file" name="insurance_policy_pdf" class="jb-file-input" accept="image/*,.pdf" onchange="window.previewImage(this, '#remove_insurance_pdf_btn', '.insurance-preview-img', '.insurance-placeholder-icon')">
                    @if ($employee?->insurance_policy_pdf_path)
                        <a href="{{ $employee->insurancePolicyPdfUrl() }}" target="_blank" class="download-link jb-thumb-action" title="Download Document">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        </a>
                    @endif
                    <button type="button" id="remove_insurance_pdf_btn" onclick="window.clearFormFile(this, 'remove_insurance_policy_pdf', '.insurance-preview-img', '.insurance-placeholder-icon')" class="jb-btn-remove {{ ($employee?->insurance_policy_pdf_path) ? '' : 'jb-hidden' }}">
                        Remove
                    </button>
                </div>
                <x-input-error-bs :messages="$errors->get('insurance_policy_pdf')" />
            </div>
            <div class="col-12 col-md-6">
                <x-input-label-bs for="insurance_start_date" value="Insurance Start Date" />
                <x-text-input-bs id="insurance_start_date" type="date" name="insurance_start_date" :value="old('insurance_start_date', $employee?->insurance_start_date?->format('Y-m-d'))" />
                <x-input-error-bs :messages="$errors->get('insurance_start_date')" />
            </div>
            <div class="col-12 col-md-6">
                <x-input-label-bs for="insurance_end_date" value="Insurance End Date" />
                <x-text-input-bs id="insurance_end_date" type="date" name="insurance_end_date" :value="old('insurance_end_date', $employee?->insurance_end_date?->format('Y-m-d'))" />
                <x-input-error-bs :messages="$errors->get('insurance_end_date')" />
            </div>
        </div>
    </div>

    <div class="jb-form-section">
        <h3 class="jb-form-section-title">KYC Documents</h3>
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="jb-doc-tile h-100 d-flex flex-column gap-3" data-upload-field>
                    <h4 class="fw-bold m-0" style="font-size:var(--text-sm);color:var(--slate-800)">Aadhaar Card Details</h4>
                    <div>
                        <x-input-label-bs for="aadhaar_card_number" value="Aadhaar Card Number" />
                        <x-text-input-bs id="aadhaar_card_number" type="text" name="aadhaar_card_number" :value="old('aadhaar_card_number', $employee?->aadhaar_card_number)" placeholder="e.g. 1234 5678 9012" />
                        <x-input-error-bs :messages="$errors->get('aadhaar_card_number')" />
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <input type="hidden" name="remove_aadhaar_photo" value="0">
                        <div class="jb-thumb jb-thumb-lg">
                            @if ($employee?->aadhaar_photo_path)
                                @if (str_ends_with(strtolower($employee->aadhaar_photo_path), '.pdf'))
                                    <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $employee->aadhaarPhotoUrl() }}')" class="download-link jb-thumb-label">PDF</a>
                                    <img class="aadhaar-preview-img jb-hidden" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                                @else
                                    <img src="{{ $employee->aadhaarPhotoUrl() }}" alt="Aadhaar preview" class="aadhaar-preview-img" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                                @endif
                            @else
                                <div class="aadhaar-placeholder-icon">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                </div>
                                <img class="aadhaar-preview-img jb-hidden" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                            @endif
                        </div>
                        <div class="flex-fill">
                            <input id="aadhaar_photo" type="file" name="aadhaar_photo" class="jb-file-input jb-file-input-sm" accept="image/*,.pdf" onchange="window.previewImage(this, '#remove_aadhaar_btn', '.aadhaar-preview-img', '.aadhaar-placeholder-icon')">
                            @if ($employee?->aadhaar_photo_path)
                                <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $employee->aadhaarPhotoUrl() }}')" class="download-link jb-file-link d-block mt-1">Download Document</a>
                            @endif
                            <button type="button" id="remove_aadhaar_btn" onclick="window.clearFormFile(this, 'remove_aadhaar_photo', '.aadhaar-preview-img', '.aadhaar-placeholder-icon')" class="jb-btn-remove-link d-block {{ ($employee?->aadhaar_photo_path) ? '' : 'jb-hidden' }}">Remove Photo/PDF</button>
                        </div>
                    </div>
                    <x-input-error-bs :messages="$errors->get('aadhaar_photo')" />
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="jb-doc-tile h-100 d-flex flex-column gap-3" data-upload-field>
                    <h4 class="fw-bold m-0" style="font-size:var(--text-sm);color:var(--slate-800)">PAN Card Details</h4>
                    <div>
                        <x-input-label-bs for="pan_card_number" value="PAN Card Number" />
                        <x-text-input-bs id="pan_card_number" type="text" name="pan_card_number" :value="old('pan_card_number', $employee?->pan_card_number)" placeholder="e.g. ABCDE1234F" class="text-uppercase" />
                        <x-input-error-bs :messages="$errors->get('pan_card_number')" />
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <input type="hidden" name="remove_pan_photo" value="0">
                        <div class="jb-thumb jb-thumb-lg">
                            @if ($employee?->pan_photo_path)
                                @if (str_ends_with(strtolower($employee->pan_photo_path), '.pdf'))
                                    <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $employee->panPhotoUrl() }}')" class="download-link jb-thumb-label">PDF</a>
                                    <img class="pan-preview-img jb-hidden" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                                @else
                                    <img src="{{ $employee->panPhotoUrl() }}" alt="PAN preview" class="pan-preview-img" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                                @endif
                            @else
                                <div class="pan-placeholder-icon">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                </div>
                                <img class="pan-preview-img jb-hidden" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                            @endif
                        </div>
                        <div class="flex-fill">
                            <input id="pan_photo" type="file" name="pan_photo" class="jb-file-input jb-file-input-sm" accept="image/*,.pdf" onchange="window.previewImage(this, '#remove_pan_btn', '.pan-preview-img', '.pan-placeholder-icon')">
                            @if ($employee?->pan_photo_path)
                                <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $employee->panPhotoUrl() }}')" class="download-link jb-file-link d-block mt-1">Download Document</a>
                            @endif
                            <button type="button" id="remove_pan_btn" onclick="window.clearFormFile(this, 'remove_pan_photo', '.pan-preview-img', '.pan-placeholder-icon')" class="jb-btn-remove-link d-block {{ ($employee?->pan_photo_path) ? '' : 'jb-hidden' }}">Remove Photo/PDF</button>
                        </div>
                    </div>
                    <x-input-error-bs :messages="$errors->get('pan_photo')" />
                </div>
            </div>
        </div>
    </div>

    <div class="jb-form-section">
        <h3 class="jb-form-section-title">Bank Details</h3>
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <x-input-label-bs for="bank_name" value="Bank Name" />
                <x-text-input-bs id="bank_name" type="text" name="bank_name" :value="old('bank_name', $employee?->bank_name)" placeholder="e.g. HDFC Bank" />
                <x-input-error-bs :messages="$errors->get('bank_name')" />
            </div>
            <div class="col-12 col-md-6">
                <x-input-label-bs for="bank_account_holder_name" value="Account Holder Name" />
                <x-text-input-bs id="bank_account_holder_name" type="text" name="bank_account_holder_name" :value="old('bank_account_holder_name', $employee?->bank_account_holder_name)" placeholder="e.g. John Doe" />
                <x-input-error-bs :messages="$errors->get('bank_account_holder_name')" />
            </div>
            <div class="col-12 col-md-6">
                <x-input-label-bs for="bank_account_number" value="Bank Account Number" />
                <x-text-input-bs id="bank_account_number" type="text" name="bank_account_number" :value="old('bank_account_number', $employee?->bank_account_number)" placeholder="e.g. 50100012345678" />
                <x-input-error-bs :messages="$errors->get('bank_account_number')" />
            </div>
            <div class="col-12 col-md-6">
                <x-input-label-bs for="bank_ifsc_code" value="IFSC Code" />
                <x-text-input-bs id="bank_ifsc_code" type="text" name="bank_ifsc_code" :value="old('bank_ifsc_code', $employee?->bank_ifsc_code)" placeholder="e.g. HDFC0000123" class="text-uppercase" />
                <x-input-error-bs :messages="$errors->get('bank_ifsc_code')" />
            </div>
            <div class="col-12" data-upload-field>
                <x-input-label-bs for="bank_cheque_photo" value="Cancelled Cheque Photo / PDF" />
                <input type="hidden" name="remove_bank_cheque_photo" value="0">
                <div class="d-flex align-items-center gap-3 mt-2">
                    <div class="jb-thumb jb-thumb-lg">
                        @if ($employee?->bank_cheque_photo_path)
                            @if (str_ends_with(strtolower($employee->bank_cheque_photo_path), '.pdf'))
                                <div class="bank-cheque-placeholder-icon jb-hidden">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                </div>
                                <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $employee->bankChequePhotoUrl() }}')" class="download-link jb-thumb-label">PDF</a>
                                <img class="bank-cheque-preview-img jb-hidden" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                            @else
                                <img src="{{ $employee->bankChequePhotoUrl() }}" alt="Cheque preview" class="bank-cheque-preview-img" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                                <div class="bank-cheque-placeholder-icon jb-hidden">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                </div>
                            @endif
                        @else
                            <div class="bank-cheque-placeholder-icon">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            </div>
                            <img class="bank-cheque-preview-img jb-hidden" style="cursor:pointer" onclick="window.previewFilePopup(this.src)">
                        @endif
                    </div>
                    <div class="flex-fill">
                        <input id="bank_cheque_photo" type="file" name="bank_cheque_photo" class="jb-file-input jb-file-input-sm" accept="image/*,.pdf" onchange="window.previewImage(this, '#remove_cheque_btn', '.bank-cheque-preview-img', '.bank-cheque-placeholder-icon')">
                        @if ($employee?->bank_cheque_photo_path)
                            <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $employee->bankChequePhotoUrl() }}')" class="download-link jb-file-link d-block mt-1">Download Document</a>
                        @endif
                        <button type="button" id="remove_cheque_btn" onclick="window.clearFormFile(this, 'remove_bank_cheque_photo', '.bank-cheque-preview-img', '.bank-cheque-placeholder-icon')" class="jb-btn-remove-link d-block {{ ($employee?->bank_cheque_photo_path) ? '' : 'jb-hidden' }}">Remove Attachment</button>
                    </div>
                </div>
                <x-input-error-bs :messages="$errors->get('bank_cheque_photo')" />
            </div>
        </div>
    </div>
</div>
