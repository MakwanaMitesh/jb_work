{{-- Expects: $lead (nullable, for edit), $cities, $agents --}}
@php $lead = $lead ?? null; @endphp

<div class="jb-card" style="overflow:hidden">

    <div class="jb-tabs">
        <button type="button" onclick="switchTab('kyc-tab', 'kyc-panel')" id="kyc-tab" class="jb-tab-btn active">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Details Sheet</span>
        </button>

        <button type="button" onclick="switchTab('bank-loan-tab', 'bank-loan-panel')" id="bank-loan-tab" class="jb-tab-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Bank Loan</span>
        </button>

        @can('application_form.view')
                <button type="button" onclick="switchTab('application-form-tab', 'application-form-panel')" id="application-form-tab" class="jb-tab-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Application Form</span>
                </button>
        @endcan

        <button type="button" onclick="switchTab('documents-tab', 'documents-panel')" id="documents-tab" class="jb-tab-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Documents</span>
        </button>

        @can('sbi_stock_statement.view')
        <button type="button" onclick="switchTab('stock-statement-tab', 'stock-statement-panel')" id="stock-statement-tab" class="jb-tab-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <span>Stock Statement</span>
        </button>
        @endcan

        @can('or.view')
        <button type="button" onclick="switchTab('opinion-report-tab', 'opinion-report-panel')" id="opinion-report-tab" class="jb-tab-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span>Opinion Report</span>
        </button>
        @endcan

        @can('cma.view')
        <button type="button" onclick="switchTab('cma-tab', 'cma-panel')" id="cma-tab" class="jb-tab-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>CMA</span>
        </button>
        @endcan

        @can('dpr.view')
        <button type="button" onclick="switchTab('dpr-tab', 'dpr-panel')" id="dpr-tab" class="jb-tab-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>DPR</span>
        </button>
        @endcan

        <button type="button" onclick="switchTab('bank-tab', 'bank-panel')" id="bank-tab" class="jb-tab-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <span>Bank Details</span>
        </button>

        <button type="button" onclick="switchTab('business-tab', 'business-panel')" id="business-tab" class="jb-tab-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <span>Business Details</span>
        </button>

        <button type="button" onclick="switchTab('loan-tab', 'loan-panel')" id="loan-tab" class="jb-tab-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Loan Details</span>
        </button>

    </div>

    <div class="p-4 p-sm-5">

        <!-- 1. KYC Details Panel -->
        <div id="kyc-panel" class="tab-panel d-flex flex-column gap-4">
            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <x-input-label-bs for="name" value="Customer Name" :required="true" />
                    <x-text-input-bs id="name" type="text" name="name" :value="old('name', $lead?->name)" placeholder="Enter full name of applicant" required autofocus />
                    <x-input-error-bs :messages="$errors->get('name')" />
                </div>
                <div class="col-12 col-md-6">
                    <x-input-label-bs for="date_of_birth" value="Date of Birth" />
                    <x-text-input-bs id="date_of_birth" type="date" name="date_of_birth" :value="old('date_of_birth', $lead?->date_of_birth?->format('Y-m-d'))" />
                    <x-input-error-bs :messages="$errors->get('date_of_birth')" />
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="gender" value="Gender" />
                    <select id="gender" name="gender" class="form-select jb-input">
                        <option value="">Select Gender</option>
                        <option value="Male" @selected(old('gender', $lead?->gender) === 'Male')>Male</option>
                        <option value="Female" @selected(old('gender', $lead?->gender) === 'Female')>Female</option>
                    </select>
                    <x-input-error-bs :messages="$errors->get('gender')" />
                </div>
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="education" value="Education" />
                    <x-text-input-bs id="education" type="text" name="education" :value="old('education', $lead?->education)" placeholder="e.g. Graduate, Post Graduate" />
                    <x-input-error-bs :messages="$errors->get('education')" />
                </div>
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="mother_name" value="Mother Name" />
                    <x-text-input-bs id="mother_name" type="text" name="mother_name" :value="old('mother_name', $lead?->mother_name)" placeholder="Enter mother's name" />
                    <x-input-error-bs :messages="$errors->get('mother_name')" />
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="father_full_name" value="Father Full Name" />
                    <x-text-input-bs id="father_full_name" type="text" name="father_full_name" :value="old('father_full_name', $lead?->father_full_name)" placeholder="Enter father's full name" />
                    <x-input-error-bs :messages="$errors->get('father_full_name')" />
                </div>
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="relationship" value="Relationship (for affidavit)" />
                    <select id="relationship" name="relationship" class="form-select jb-input">
                        <option value="">Select Relationship</option>
                        <option value="Son" @selected(old('relationship', $lead?->relationship) === 'Son')>Son</option>
                        <option value="Daughter" @selected(old('relationship', $lead?->relationship) === 'Daughter')>Daughter</option>
                        <option value="Wife" @selected(old('relationship', $lead?->relationship) === 'Wife')>Wife</option>
                    </select>
                    <x-input-error-bs :messages="$errors->get('relationship')" />
                </div>
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="place_of_birth" value="Place of Birth" />
                    <x-text-input-bs id="place_of_birth" type="text" name="place_of_birth" :value="old('place_of_birth', $lead?->place_of_birth)" placeholder="Enter place of birth" />
                    <x-input-error-bs :messages="$errors->get('place_of_birth')" />
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <x-input-label-bs for="occupation" value="Occupation" />
                    <x-text-input-bs id="occupation" type="text" name="occupation" :value="old('occupation', $lead?->occupation)" placeholder="e.g. Business, Service, Self-Employed" />
                    <x-input-error-bs :messages="$errors->get('occupation')" />
                </div>
            </div>

            <div>
                <x-input-label-bs for="address" value="Address" />
                <textarea id="address" name="address" rows="3" class="jb-textarea" placeholder="Enter complete residential address...">{{ old('address', $lead?->address) }}</textarea>
                <x-input-error-bs :messages="$errors->get('address')" />
            </div>

            <div class="row g-4">
                <div class="col-6 col-md-3">
                    <x-input-label-bs for="aadhar_card" value="Aadhaar Card" />
                    <x-text-input-bs id="aadhar_card" type="text" name="aadhar_card" :value="old('aadhar_card', $lead?->aadhar_card)" placeholder="Enter 12-digit Aadhaar Number" />
                    <x-input-error-bs :messages="$errors->get('aadhar_card')" />
                </div>
                <div class="col-6 col-md-3">
                    <x-input-label-bs for="pan_card" value="PAN Card" />
                    <x-text-input-bs id="pan_card" type="text" name="pan_card" :value="old('pan_card', $lead?->pan_card)" placeholder="Enter 10-digit PAN (e.g. ABCDE1234F)" />
                    <x-input-error-bs :messages="$errors->get('pan_card')" />
                </div>
                <div class="col-6 col-md-3">
                    <x-input-label-bs for="udyam_registration" value="Udyam Registration" />
                    <x-text-input-bs id="udyam_registration" type="text" name="udyam_registration" :value="old('udyam_registration', $lead?->udyam_registration)" placeholder="e.g. UDYAM-GJ-00-0000000" />
                    <x-input-error-bs :messages="$errors->get('udyam_registration')" />
                </div>
                <div class="col-6 col-md-3">
                    <x-input-label-bs for="fssai_license" value="FSSAI License No." />
                    <x-text-input-bs id="fssai_license" type="text" name="fssai_license" :value="old('fssai_license', $lead?->fssai_license)" placeholder="e.g. 10012022000123" />
                    <x-input-error-bs :messages="$errors->get('fssai_license')" />
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="mobile_number" value="Mobile Number" :required="true" />
                    <x-text-input-bs id="mobile_number" type="tel" name="mobile_number" :value="old('mobile_number', $lead?->mobile_number)" placeholder="Enter 10-digit mobile number" required />
                    <x-input-error-bs :messages="$errors->get('mobile_number')" />
                </div>
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="alternate_mobile_number" value="Alternate Mobile Number" />
                    <x-text-input-bs id="alternate_mobile_number" type="tel" name="alternate_mobile_number" :value="old('alternate_mobile_number', $lead?->alternate_mobile_number)" placeholder="Enter alternate contact number" />
                    <x-input-error-bs :messages="$errors->get('alternate_mobile_number')" />
                </div>
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="email" value="Email" />
                    <x-text-input-bs id="email" type="email" name="email" :value="old('email', $lead?->email)" placeholder="e.g. applicant@example.com" />
                    <x-input-error-bs :messages="$errors->get('email')" />
                </div>
            </div>

            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="d-flex align-items-center gap-2 fw-bold m-0" style="font-size:var(--text-sm);color:var(--slate-900)">
                        <span style="width:.375rem;height:.75rem;background:var(--blue-600);border-radius:var(--radius-full)"></span> ITR Details
                    </h4>
                    <button type="button" onclick="addItrCard()" class="btn btn-sm jb-btn-soft d-inline-flex align-items-center gap-2">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Add ITR Details</span>
                    </button>
                </div>

                <div class="jb-inset-box">
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <x-input-label-bs for="itr_id" value="ITR User ID" />
                            <x-text-input-bs id="itr_id" type="text" name="itr_id" :value="old('itr_id', $lead?->itr_id)" placeholder="ITR User ID" />
                            <x-input-error-bs :messages="$errors->get('itr_id')" />
                        </div>
                        <div class="col-12 col-md-6">
                            <x-input-label-bs for="itr_password" value="ITR Password" />
                            <x-text-input-bs id="itr_password" type="text" name="itr_password" :value="old('itr_password', $lead?->itr_password)" placeholder="ITR Password" />
                            <x-input-error-bs :messages="$errors->get('itr_password')" />
                        </div>
                    </div>
                </div>

                <div id="itr-cards-container" class="d-flex flex-column gap-3">
                    @php
                        $itrs = old('itr_details', $lead?->formatted_itr_details ?? []);
                        if (empty($itrs)) {
                            $itrs = [['assessment_year' => '', 'itr_audited' => '']];
                        }
                    @endphp
                    @foreach ($itrs as $index => $itr)
                        @php $audited = $itr['itr_audited'] ?? ''; @endphp
                        <div class="itr-card jb-subcard d-flex flex-column gap-3">
                            <div class="d-flex align-items-center justify-content-between pb-2 border-bottom">
                                <span class="d-flex align-items-center gap-2 fw-bold text-uppercase" style="font-size:var(--text-xs);letter-spacing:.05em;color:var(--slate-700)">
                                    <span style="width:.5rem;height:.5rem;border-radius:var(--radius-full);background:var(--primary-500)"></span>
                                    ITR Profile #<span class="itr-sr">{{ $index + 1 }}</span>
                                </span>
                                <button type="button" onclick="removeItrCard(this)" class="btn btn-sm btn-danger">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Remove</span>
                                </button>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <x-input-label-bs value="Assessment Year (A.Y.)" />
                                    <select name="itr_details[{{ $index }}][assessment_year]" class="form-select select2 jb-input">
                                        <option value="">Select Assessment Year</option>
                                        @foreach ($assessmentYears as $ay)
                                            <option value="{{ $ay->name }}" @selected(($itr['assessment_year'] ?? '') === $ay->name)>{{ $ay->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <x-input-label-bs value="ITR Audited" />
                                    <select name="itr_details[{{ $index }}][itr_audited]" onchange="toggleItrFiles(this)" class="itr-audited-select form-select jb-input">
                                        <option value="">Select Option</option>
                                        <option value="Yes" @selected($audited === 'Yes')>Yes</option>
                                        <option value="No" @selected($audited === 'No')>No</option>
                                    </select>
                                </div>
                            </div>

                            <div class="itr-files-wrapper pt-3 border-top">
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6 col-lg-3 audit-report-field {{ $audited === 'Yes' ? '' : 'jb-hidden' }}">
                                        <x-input-label-bs value="Audit Report" />
                                        <input type="file" name="itr_details[{{ $index }}][audit_report]" class="jb-file-input jb-file-input-sm" />
                                        @if (!empty($itr['audit_report']))
                                            <input type="hidden" name="itr_details[{{ $index }}][existing_audit_report]" value="{{ $itr['audit_report'] }}">
                                            <div class="jb-file-confirm mt-1" style="font-size:var(--text-xs)">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <a href="{{ Storage::url($itr['audit_report']) }}" target="_blank" class="fw-medium" style="color:inherit">View Existing File</a>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-3 itr-file-field {{ in_array($audited, ['Yes', 'No']) ? '' : 'jb-hidden' }}">
                                        <x-input-label-bs value="ITR" />
                                        <input type="file" name="itr_details[{{ $index }}][itr_file]" class="jb-file-input jb-file-input-sm" />
                                        @if (!empty($itr['itr_file']))
                                            <input type="hidden" name="itr_details[{{ $index }}][existing_itr_file]" value="{{ $itr['itr_file'] }}">
                                            <div class="jb-file-confirm mt-1" style="font-size:var(--text-xs)">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <a href="{{ Storage::url($itr['itr_file']) }}" target="_blank" class="fw-medium" style="color:inherit">View Existing File</a>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-3 computation-field {{ in_array($audited, ['Yes', 'No']) ? '' : 'jb-hidden' }}">
                                        <x-input-label-bs value="Computation" />
                                        <input type="file" name="itr_details[{{ $index }}][computation]" class="jb-file-input jb-file-input-sm" />
                                        @if (!empty($itr['computation']))
                                            <input type="hidden" name="itr_details[{{ $index }}][existing_computation]" value="{{ $itr['computation'] }}">
                                            <div class="jb-file-confirm mt-1" style="font-size:var(--text-xs)">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <a href="{{ Storage::url($itr['computation']) }}" target="_blank" class="fw-medium" style="color:inherit">View Existing File</a>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-12 col-sm-6 col-lg-3 itr-form-field {{ in_array($audited, ['Yes', 'No']) ? '' : 'jb-hidden' }}">
                                        <x-input-label-bs value="ITR Form" />
                                        <input type="file" name="itr_details[{{ $index }}][itr_form]" class="jb-file-input jb-file-input-sm" />
                                        @if (!empty($itr['itr_form']))
                                            <input type="hidden" name="itr_details[{{ $index }}][existing_itr_form]" value="{{ $itr['itr_form'] }}">
                                            <div class="jb-file-confirm mt-1" style="font-size:var(--text-xs)">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <a href="{{ Storage::url($itr['itr_form']) }}" target="_blank" class="fw-medium" style="color:inherit">View Existing File</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="bank_id" value="Bank" />
                    <select id="bank_id" name="bank_id" class="form-select select2 jb-input">
                        <option value="">Select Bank</option>
                        @foreach ($masterBanks as $bank)
                            <option value="{{ $bank->id }}" @selected(old('bank_id', $lead?->bank_id) == $bank->id)>{{ $bank->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error-bs :messages="$errors->get('bank_id')" />
                </div>
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="loan_product_id" value="Loan Product" :required="true" />
                    <select id="loan_product_id" name="loan_product_id" required class="form-select jb-input">
                        <option value="">Select Loan Product</option>
                        @foreach ($loanProducts as $product)
                            <option value="{{ $product->id }}" @selected(old('loan_product_id', $lead?->loan_product_id) == $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error-bs :messages="$errors->get('loan_product_id')" />
                </div>
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="constitution_id" value="Customer Constitution" />
                    <select id="constitution_id" name="constitution_id" class="form-select jb-input">
                        <option value="">Select Constitution</option>
                        @foreach ($constitutions as $const)
                            <option value="{{ $const->id }}" @selected(old('constitution_id', $lead?->constitution_id) == $const->id)>{{ $const->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error-bs :messages="$errors->get('constitution_id')" />
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="source" value="Lead Source" />
                    <x-text-input-bs id="source" type="text" name="source" :value="old('source', $lead?->source)" placeholder="e.g. Website, Reference" />
                    <x-input-error-bs :messages="$errors->get('source')" />
                </div>
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="city_id" value="City" />
                    <select id="city_id" name="city_id" class="form-select jb-input">
                        <option value="">Select City</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" @selected(old('city_id', $lead?->city_id) == $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error-bs :messages="$errors->get('city_id')" />
                </div>
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="agent_id" value="Assigned Agent" />
                    <select id="agent_id" name="agent_id" class="form-select jb-input">
                        <option value="">Select Agent</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}" @selected(old('agent_id', $lead?->agent_id) == $agent->id)>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error-bs :messages="$errors->get('agent_id')" />
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <x-input-label-bs for="status" value="Status" :required="true" />
                    <select id="status" name="status" class="form-select jb-input">
                        @foreach(['new', 'contacted', 'in_progress', 'converted', 'lost', 'visit_pending', 'visit_completed', 'documentation_pending', 'documentation_in_progress', 'documentation_completed', 'under_process', 'approved', 'rejected', 'completed', 'cancelled'] as $st)
                            <option value="{{ $st }}" @selected(old('status', $lead?->status ?? 'new') === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                        @endforeach
                    </select>
                    <x-input-error-bs :messages="$errors->get('status')" />
                </div>
                <div class="col-12 col-md-6">
                    <x-input-label-bs for="notes" value="Notes / Requirements" />
                    <textarea id="notes" name="notes" rows="2" class="jb-textarea" placeholder="Describe requirements, follow-ups, or notes...">{{ old('notes', $lead?->notes) }}</textarea>
                    <x-input-error-bs :messages="$errors->get('notes')" />
                </div>
            </div>

            <div class="d-flex justify-content-end pt-3 border-top">
                <button type="button" onclick="switchTab('bank-loan-tab', 'bank-loan-panel')" class="btn btn-secondary jb-btn-tall">
                    Next: Bank Loan
                </button>
            </div>
        </div>

        <!-- 1.5 Bank Loan Verification Details Panel -->
        @php
            $bld = old('bank_loan_details', $lead?->bank_loan_details ?? []);
            $orgTypes = $bld['type_of_organization'] ?? [];
            if (!is_array($orgTypes)) $orgTypes = [];
            $natureBiz = $bld['nature_of_business'] ?? [];
            if (!is_array($natureBiz)) $natureBiz = [];
            $officeOwnership = $bld['office_ownership'] ?? [];
            if (!is_array($officeOwnership)) $officeOwnership = [];

            // Smart Defaults from Lead data
            $bizEntityName = $lead?->business_name ?: $lead?->firm_name ?: '';
            $defaultApplicant = $lead?->name ? trim($lead->name . ($bizEntityName ? ' - Prop. Of ' . $bizEntityName : '')) : '';
            $defaultOfficeAddr = trim(($bizEntityName ? $bizEntityName . "\n" : '') . ($lead?->business_address ?: ''));
            $defaultYears = $lead?->business_experience ?: '';

            // Auto-check organization type if empty
            if (empty($orgTypes) && $lead) {
                $constStr = strtolower($lead->constitution_of_business ?: $lead->constitution?->name ?: '');
                if (str_contains($constStr, 'proprietor')) $orgTypes[] = 'Proprietorship';
                elseif (str_contains($constStr, 'partner')) $orgTypes[] = 'Partnership';
                elseif (str_contains($constStr, 'pvt') || str_contains($constStr, 'private')) $orgTypes[] = 'Pvt. Ltd.';
                elseif (str_contains($constStr, 'public')) $orgTypes[] = 'Public Ltd';
            }

            // Auto-check nature of business if empty
            if (empty($natureBiz) && $lead) {
                $actStr = strtolower($lead->business_activity ?: '');
                if (str_contains($actStr, 'trading')) $natureBiz[] = 'TRADING';
                elseif (str_contains($actStr, 'manufacturing')) $natureBiz[] = 'Manufacturing';
                elseif (str_contains($actStr, 'service')) $natureBiz[] = 'Consultancy';
            }
        @endphp
        <div id="bank-loan-panel" class="tab-panel jb-hidden">
            <div class="jb-card p-4">
                <div class="border-bottom pb-3 mb-3">
                    <h3 class="d-flex align-items-center gap-2 fw-bold m-0" style="font-size:var(--text-base);color:var(--slate-900)">
                        <span style="width:.5rem;height:1rem;background:var(--amber-500);border-radius:var(--radius-full)"></span>
                        Bank Loan Verification Report Form
                    </h3>
                    <p class="mt-1 mb-0" style="font-size:var(--text-xs);color:var(--slate-500)">Verification and workplace report details for Bank Loan processing.</p>
                </div>

                <div class="table-responsive rounded-3" style="border:1px solid var(--slate-200)">
                    <table class="jb-report-table">
                        <tbody>
                            <tr>
                                <td class="jb-report-num">1</td>
                                <td class="jb-report-label">Name of the Applicant / Co-Applicant / Guarantor</td>
                                <td>
                                    <input type="text" id="bld_applicant" name="bank_loan_details[applicant_coapplicant_guarantor_name]" value="{{ old('bank_loan_details.applicant_coapplicant_guarantor_name', $bld['applicant_coapplicant_guarantor_name'] ?? $defaultApplicant) }}" class="jb-report-input" placeholder="Enter Applicant / Co-Applicant / Guarantor name">
                                </td>
                            </tr>

                            <tr>
                                <td class="jb-report-num">2</td>
                                <td class="jb-report-label">Visit to the Office / Work Place of the Borrower</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-semibold flex-shrink-0" style="font-size:var(--text-xs);color:var(--slate-500)">Date of Visit:</span>
                                        <input type="date" name="bank_loan_details[visit_office_date]" value="{{ old('bank_loan_details.visit_office_date', $bld['visit_office_date'] ?? '') }}" class="jb-report-input" style="width:auto">
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="jb-report-num">3</td>
                                <td class="jb-report-label">Name of the Office / Organization, address & Office Phone No.</td>
                                <td>
                                    <textarea id="bld_office_address" name="bank_loan_details[office_organization_address]" rows="2" class="jb-report-input mb-2" placeholder="Enter organization name and complete address...">{{ old('bank_loan_details.office_organization_address', $bld['office_organization_address'] ?? $defaultOfficeAddr) }}</textarea>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-semibold flex-shrink-0" style="font-size:var(--text-xs);color:var(--slate-500)">Office Phone No:</span>
                                        <input type="text" name="bank_loan_details[office_phone_no]" value="{{ old('bank_loan_details.office_phone_no', $bld['office_phone_no'] ?? '') }}" class="jb-report-input" style="max-width:16rem" placeholder="Enter office phone number">
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="jb-report-num">4</td>
                                <td class="jb-report-label">For Self Employed:</td>
                                <td>
                                    <div class="mb-3">
                                        <span class="d-block fw-bold text-uppercase mb-2" style="font-size:var(--text-xs);color:var(--slate-700)">A. Type of Organization</span>
                                        <div class="d-flex flex-wrap gap-3" style="font-size:var(--text-xs)">
                                            @foreach(['Partnership', 'Proprietorship', 'Pvt. Ltd.', 'Public Ltd', 'Others'] as $opt)
                                                <label class="d-inline-flex align-items-center gap-2" style="cursor:pointer">
                                                    <input type="checkbox" name="bank_loan_details[type_of_organization][]" value="{{ $opt }}" @checked(in_array($opt, $orgTypes)) class="form-check-input m-0">
                                                    <span>{{ $opt }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="mb-3 pt-2 border-top">
                                        <span class="d-block fw-bold text-uppercase mb-2" style="font-size:var(--text-xs);color:var(--slate-700)">B. Nature of Business</span>
                                        <div class="row g-2" style="font-size:var(--text-xs)">
                                            @foreach(['TRADING', 'Contractor', 'Processing', 'Builder', 'Manufacturing', 'Brokerage', 'Consultancy', 'Professional', 'Others'] as $opt)
                                                <div class="col-6 col-sm-4 col-md-3">
                                                    <label class="d-inline-flex align-items-center gap-2" style="cursor:pointer">
                                                        <input type="checkbox" name="bank_loan_details[nature_of_business][]" value="{{ $opt }}" @checked(in_array($opt, $natureBiz)) class="form-check-input m-0">
                                                        <span>{{ $opt }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="pt-2 border-top">
                                        <span class="d-block fw-bold text-uppercase mb-2" style="font-size:var(--text-xs);color:var(--slate-700)">C. Whether own office / rented / leased</span>
                                        <div class="d-flex flex-wrap gap-4" style="font-size:var(--text-xs)">
                                            @foreach(['OWNED', 'RENTED', 'LEASED'] as $opt)
                                                <label class="d-inline-flex align-items-center gap-2" style="cursor:pointer">
                                                    <input type="checkbox" name="bank_loan_details[office_ownership][]" value="{{ $opt }}" @checked(in_array($opt, $officeOwnership)) class="form-check-input m-0">
                                                    <span class="fw-semibold">{{ $opt }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="jb-report-num">5</td>
                                <td class="jb-report-label">Land mark for Place of work</td>
                                <td>
                                    <input type="text" name="bank_loan_details[workplace_landmark]" value="{{ old('bank_loan_details.workplace_landmark', $bld['workplace_landmark'] ?? '') }}" class="jb-report-input" placeholder="Enter workplace landmark or nearby location">
                                </td>
                            </tr>

                            <tr>
                                <td class="jb-report-num">6</td>
                                <td class="jb-report-label">Number of year Service / Business</td>
                                <td>
                                    <input type="text" id="bld_years" name="bank_loan_details[years_in_business]" value="{{ old('bank_loan_details.years_in_business', $bld['years_in_business'] ?? $defaultYears) }}" class="jb-report-input" style="max-width:16rem" placeholder="Enter total service or business experience">
                                </td>
                            </tr>

                            <tr>
                                <td class="jb-report-num">7</td>
                                <td class="jb-report-label">Designation of the APPLICANT / GUARANTOR:</td>
                                <td>
                                    <input type="text" name="bank_loan_details[designation_applicant_guarantor]" value="{{ old('bank_loan_details.designation_applicant_guarantor', $bld['designation_applicant_guarantor'] ?? '') }}" class="jb-report-input" placeholder="Enter designation (e.g. Proprietor, Partner, Director)">
                                </td>
                            </tr>

                            <tr>
                                <td class="jb-report-num">8</td>
                                <td class="jb-report-label">Whom met (Name of the Person contacted & designation) Pl give telephone nos.</td>
                                <td>
                                    <input type="text" name="bank_loan_details[whom_met_details]" value="{{ old('bank_loan_details.whom_met_details', $bld['whom_met_details'] ?? '') }}" class="jb-report-input" placeholder="Enter contacted person name, designation & phone number">
                                </td>
                            </tr>

                            <tr>
                                <td class="jb-report-num">9</td>
                                <td class="jb-report-label">Office TVR Result (Positive / Negative)</td>
                                <td>
                                    <div class="d-flex align-items-center gap-4" style="font-size:var(--text-sm)">
                                        <label class="d-inline-flex align-items-center gap-2" style="cursor:pointer">
                                            <input type="radio" name="bank_loan_details[office_tvr_result]" value="Positive" @checked(old('bank_loan_details.office_tvr_result', $bld['office_tvr_result'] ?? '') === 'Positive') class="form-check-input">
                                            <span class="fw-semibold" style="color:var(--emerald-700)">Positive</span>
                                        </label>
                                        <label class="d-inline-flex align-items-center gap-2" style="cursor:pointer">
                                            <input type="radio" name="bank_loan_details[office_tvr_result]" value="Negative" @checked(old('bank_loan_details.office_tvr_result', $bld['office_tvr_result'] ?? '') === 'Negative') class="form-check-input">
                                            <span class="fw-semibold" style="color:var(--rose-700)">Negative</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="jb-inset-box mt-4">
                    <h4 class="fw-bold mb-3" style="font-size:var(--text-sm);color:var(--slate-800)">Branch &amp; Sign-off Details <span class="jb-muted fw-normal">(printed on the Inspection Sheet PDF)</span></h4>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="jb-field-label" for="bld_file_no">File No.</label>
                            <input type="text" id="bld_file_no" name="bank_loan_details[file_no]" value="{{ old('bank_loan_details.file_no', $bld['file_no'] ?? '') }}" class="jb-report-input" placeholder="Bank file number">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="jb-field-label" for="bld_branch_name">Branch Name</label>
                            <input type="text" id="bld_branch_name" name="bank_loan_details[branch_name]" value="{{ old('bank_loan_details.branch_name', $bld['branch_name'] ?? '') }}" class="jb-report-input" placeholder="e.g. AMCC BARDOLI Branch">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="jb-field-label" for="bld_branch_code">Branch Code</label>
                            <input type="text" id="bld_branch_code" name="bank_loan_details[branch_code]" value="{{ old('bank_loan_details.branch_code', $bld['branch_code'] ?? '') }}" class="jb-report-input" placeholder="e.g. 0062744">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="jb-field-label" for="bld_manager_name">Manager Name</label>
                            <input type="text" id="bld_manager_name" name="bank_loan_details[manager_name]" value="{{ old('bank_loan_details.manager_name', $bld['manager_name'] ?? '') }}" class="jb-report-input" placeholder="Branch manager name">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="jb-field-label" for="bld_manager_ss_no">Manager S.S. No.</label>
                            <input type="text" id="bld_manager_ss_no" name="bank_loan_details[manager_ss_no]" value="{{ old('bank_loan_details.manager_ss_no', $bld['manager_ss_no'] ?? '') }}" class="jb-report-input" placeholder="Staff number">
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="jb-field-label m-0" for="bld_remarks">Inspection Remarks / Observations</label>
                                <button type="button" id="bld_remarks_regenerate" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Regenerate from Lead Data
                                </button>
                            </div>
                            <textarea id="bld_remarks" name="bank_loan_details[inspection_remarks]" rows="6" class="jb-report-input" data-has-saved-value="{{ !empty($bld['inspection_remarks'] ?? '') ? '1' : '0' }}" placeholder="Narrative printed below the table on the PDF (visit summary, machinery, staff, location, overall finding...). Auto-updates as you fill in the Lead's name/address/activity — edit freely to override.">{{ old('bank_loan_details.inspection_remarks', $bld['inspection_remarks'] ?? \App\Support\InspectionSheetRemarks::generate($lead, $bld)) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between pt-3 mt-3 border-top">
                    <button type="button" onclick="switchTab('kyc-tab', 'kyc-panel')" class="btn btn-secondary jb-btn-tall">
                        Previous
                    </button>
                    <button type="button" onclick="switchTab('application-form-tab', 'application-form-panel')" class="btn btn-secondary jb-btn-tall">
                        Next: Application Form
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. Bank Details Panel -->
        <div id="bank-panel" class="tab-panel jb-hidden">
            <div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h3 class="jb-section-title m-0">Current Bank Details</h3>
                        <p class="jb-muted mt-1 mb-0">Manage bank account profiles for this lead.</p>
                    </div>
                    <button type="button" onclick="addBankRow()" class="btn btn-sm jb-btn-soft d-inline-flex align-items-center gap-2">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Bank</span>
                    </button>
                </div>

                <datalist id="master-banks-list">
                    @foreach ($masterBanks ?? [] as $mb)
                        <option value="{{ $mb->name }}">
                    @endforeach
                </datalist>

                <div class="table-responsive rounded-3" style="border:1px solid var(--slate-200)">
                    <table class="jb-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:3rem">SR</th>
                                <th>Bank Name</th>
                                <th>A/C Number</th>
                                <th>A/C Type</th>
                                <th>IFSC Code</th>
                                <th class="text-center" style="width:4rem">Action</th>
                            </tr>
                        </thead>
                        <tbody id="bank-rows-container">
                            @php
                                $banks = old('bank_details', $lead?->bank_details ?? []);
                                if (empty($banks)) {
                                    $banks = [['bank_name' => '', 'account_number' => '', 'account_type' => '', 'ifsc_code' => '']];
                                }
                            @endphp
                            @foreach ($banks as $index => $bank)
                                <tr class="bank-row">
                                    <td class="text-center fw-semibold bank-sr" style="color:var(--slate-500)">{{ $index + 1 }}</td>
                                    <td>
                                        <input type="text" name="bank_details[{{ $index }}][bank_name]" list="master-banks-list" value="{{ $bank['bank_name'] ?? '' }}" class="jb-inline-input" style="border:1px solid var(--slate-200)" placeholder="Select or type Bank name">
                                    </td>
                                    <td>
                                        <input type="text" name="bank_details[{{ $index }}][account_number]" value="{{ $bank['account_number'] ?? '' }}" class="jb-inline-input" placeholder="A/C Number">
                                    </td>
                                    <td>
                                        <input type="text" name="bank_details[{{ $index }}][account_type]" value="{{ $bank['account_type'] ?? '' }}" class="jb-inline-input" placeholder="e.g. Savings, Current">
                                    </td>
                                    <td>
                                        <input type="text" name="bank_details[{{ $index }}][ifsc_code]" value="{{ $bank['ifsc_code'] ?? '' }}" class="jb-inline-input" placeholder="IFSC Code">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" onclick="removeBankRow(this)" class="jb-icon-btn" style="color:var(--red-500)">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-between pt-3 mt-3 border-top">
                @can('dpr.view')
                    <button type="button" onclick="switchTab('dpr-tab', 'dpr-panel')" class="btn btn-secondary jb-btn-tall">
                        Previous: DPR
                    </button>
                @elsecan('cma.view')
                    <button type="button" onclick="switchTab('cma-tab', 'cma-panel')" class="btn btn-secondary jb-btn-tall">
                        Previous: CMA
                    </button>
                @elsecan('or.view')
                    <button type="button" onclick="switchTab('opinion-report-tab', 'opinion-report-panel')" class="btn btn-secondary jb-btn-tall">
                        Previous: Opinion Report
                    </button>
                @elsecan('sbi_stock_statement.view')
                    <button type="button" onclick="switchTab('stock-statement-tab', 'stock-statement-panel')" class="btn btn-secondary jb-btn-tall">
                        Previous: Stock Statement
                    </button>
                @else
                    <button type="button" onclick="switchTab('documents-tab', 'documents-panel')" class="btn btn-secondary jb-btn-tall">
                        Previous: Documents
                    </button>
                @endcan
                <button type="button" onclick="switchTab('business-tab', 'business-panel')" class="btn btn-secondary jb-btn-tall">
                    Next: Business Details
                </button>
            </div>
        </div>

        <!-- 3. Business Details Panel -->
        <div id="business-panel" class="tab-panel jb-hidden">
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <x-input-label-bs for="business_name" value="Business Name" />
                    <x-text-input-bs id="business_name" type="text" name="business_name" :value="old('business_name', $lead?->business_name)" placeholder="Enter official business / enterprise name" />
                    <x-input-error-bs :messages="$errors->get('business_name')" />
                </div>
                <div class="col-12 col-md-6">
                    <x-input-label-bs for="constitution_of_business" value="Constitution of Business" />
                    <x-text-input-bs id="constitution_of_business" type="text" name="constitution_of_business" :value="old('constitution_of_business', $lead?->constitution_of_business)" placeholder="e.g. Proprietorship, Partnership, Pvt. Ltd." />
                    <x-input-error-bs :messages="$errors->get('constitution_of_business')" />
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <x-input-label-bs for="introduction" value="Introduction / Business Activity Details" />
                    <textarea id="introduction" name="introduction" rows="3" class="jb-textarea" placeholder="Brief introduction of the business operations...">{{ old('introduction', $lead?->introduction) }}</textarea>
                    <x-input-error-bs :messages="$errors->get('introduction')" />
                </div>
                <div class="col-12 col-md-6">
                    <x-input-label-bs for="business_address" value="Business Address" />
                    <textarea id="business_address" name="business_address" rows="3" class="jb-textarea" placeholder="Enter complete business unit address...">{{ old('business_address', $lead?->business_address) }}</textarea>
                    <x-input-error-bs :messages="$errors->get('business_address')" />
                </div>
            </div>

            <div class="p-3 rounded-4 mb-4" style="background:var(--slate-50);border:1px solid var(--slate-100)">
                <h3 class="fw-bold mb-3" style="font-size:var(--text-sm);color:var(--slate-800)">GST Details</h3>
                <div class="row g-4">
                    <div class="col-6 col-md-3">
                        <x-input-label-bs for="gst_applicable" value="GST Registered" />
                        <select id="gst_applicable" name="gst_applicable" class="form-select jb-input">
                            <option value="">Select Option</option>
                            <option value="Yes" @selected(old('gst_applicable', $lead?->gst_applicable) === 'Yes')>Yes</option>
                            <option value="No" @selected(old('gst_applicable', $lead?->gst_applicable) === 'No')>No</option>
                        </select>
                        <x-input-error-bs :messages="$errors->get('gst_applicable')" />
                    </div>
                    <div class="col-6 col-md-3">
                        <x-input-label-bs for="gst_number" value="GST Number" />
                        <x-text-input-bs id="gst_number" type="text" name="gst_number" :value="old('gst_number', $lead?->gst_number)" placeholder="Enter 15-character GSTIN" />
                        <x-input-error-bs :messages="$errors->get('gst_number')" />
                    </div>
                    <div class="col-6 col-md-3">
                        <x-input-label-bs for="gst_id" value="GST Portal ID" />
                        <x-text-input-bs id="gst_id" type="text" name="gst_id" :value="old('gst_id', $lead?->gst_id)" placeholder="Enter GST Portal User ID" />
                        <x-input-error-bs :messages="$errors->get('gst_id')" />
                    </div>
                    <div class="col-6 col-md-3">
                        <x-input-label-bs for="gst_password" value="GST Portal Password" />
                        <x-text-input-bs id="gst_password" type="text" name="gst_password" :value="old('gst_password', $lead?->gst_password)" placeholder="Enter GST Portal Password" />
                        <x-input-error-bs :messages="$errors->get('gst_password')" />
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="firm_name" value="Firm Name" />
                    <x-text-input-bs id="firm_name" type="text" name="firm_name" :value="old('firm_name', $lead?->firm_name)" placeholder="Enter firm name" />
                    <x-input-error-bs :messages="$errors->get('firm_name')" />
                </div>
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="business_activity" value="Business Activity" />
                    <select id="business_activity" name="business_activity" class="form-select jb-input">
                        <option value="">Select Activity</option>
                        <option value="Manufacturing" @selected(old('business_activity', $lead?->business_activity) === 'Manufacturing')>Manufacturing</option>
                        <option value="Trading" @selected(old('business_activity', $lead?->business_activity) === 'Trading')>Trading</option>
                        <option value="Services" @selected(old('business_activity', $lead?->business_activity) === 'Services')>Services</option>
                    </select>
                    <x-input-error-bs :messages="$errors->get('business_activity')" />
                </div>
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="business_experience" value="Business Experience" />
                    <x-text-input-bs id="business_experience" type="text" name="business_experience" :value="old('business_experience', $lead?->business_experience)" placeholder="e.g. 5 Years" />
                    <x-input-error-bs :messages="$errors->get('business_experience')" />
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="no_of_manpower" value="Number of Manpower" />
                    <x-text-input-bs id="no_of_manpower" type="text" name="no_of_manpower" :value="old('no_of_manpower', $lead?->no_of_manpower)" placeholder="e.g. 15 Employees" />
                    <x-input-error-bs :messages="$errors->get('no_of_manpower')" />
                </div>
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="business_location" value="Business Location" />
                    <x-text-input-bs id="business_location" type="text" name="business_location" :value="old('business_location', $lead?->business_location)" placeholder="e.g. Surat, Gujarat" />
                    <x-input-error-bs :messages="$errors->get('business_location')" />
                </div>
                <div class="col-12 col-md-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <x-input-label-bs for="area_of_premises" value="Area of Premises" />
                            <x-text-input-bs id="area_of_premises" type="text" name="area_of_premises" :value="old('area_of_premises', $lead?->area_of_premises)" placeholder="e.g. 2000 sq ft" />
                            <x-input-error-bs :messages="$errors->get('area_of_premises')" />
                        </div>
                        <div class="col-6">
                            <x-input-label-bs for="land_and_factory_building" value="Land and Factory Building Details" />
                            <x-text-input-bs id="land_and_factory_building" type="text" name="land_and_factory_building" :value="old('land_and_factory_building', $lead?->land_and_factory_building)" placeholder="e.g. Owned / Rented 5000 sq ft RCC structure" />
                            <x-input-error-bs :messages="$errors->get('land_and_factory_building')" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <x-input-label-bs for="connectivity" value="Connectivity Details" />
                <x-text-input-bs id="connectivity" type="text" name="connectivity" :value="old('connectivity', $lead?->connectivity)" placeholder="e.g. Main Highway Road, Near Station" />
                <x-input-error-bs :messages="$errors->get('connectivity')" />
            </div>

            <div class="d-flex justify-content-between pt-3 border-top">
                <button type="button" onclick="switchTab('bank-tab', 'bank-panel')" class="btn btn-secondary jb-btn-tall">
                    Previous
                </button>
                <button type="button" onclick="switchTab('loan-tab', 'loan-panel')" class="btn btn-secondary jb-btn-tall">
                    Next: Loan Details
                </button>
            </div>
        </div>

        <!-- 4. Loan Details Panel -->
        <div id="loan-panel" class="tab-panel jb-hidden">
            <div class="mb-4">
                <x-input-label-bs for="required_loan_amount" value="Required Loan Amount" />
                <x-text-input-bs id="required_loan_amount" type="text" name="required_loan_amount" :value="old('required_loan_amount', $lead?->required_loan_amount)" placeholder="Enter required loan amount (in ₹)" />
                <x-input-error-bs :messages="$errors->get('required_loan_amount')" />
            </div>

            <div class="jb-inset-box mb-4">
              <div class="row g-4">
                <div class="col-12 col-md-6">
                    <h4 class="fw-bold mb-3" style="font-size:var(--text-sm);color:var(--slate-800)">1) Cash Credit (CC) Loan</h4>
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <x-input-label-bs for="cc_amount" value="CC Amount" />
                            <x-text-input-bs id="cc_amount" type="text" name="cc_amount" :value="old('cc_amount', $lead?->cc_amount)" placeholder="Enter CC loan amount (in ₹)" />
                            <x-input-error-bs :messages="$errors->get('cc_amount')" />
                        </div>
                        <div>
                            <x-input-label-bs for="cc_details" value="CC Details" />
                            <textarea id="cc_details" name="cc_details" rows="3" class="jb-textarea" placeholder="Describe CC requirements, stock & debtors details...">{{ old('cc_details', $lead?->cc_details) }}</textarea>
                            <x-input-error-bs :messages="$errors->get('cc_details')" />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <h4 class="fw-bold mb-3" style="font-size:var(--text-sm);color:var(--slate-800)">2) Term Loan</h4>
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <x-input-label-bs for="term_loan_amount" value="Term Loan Amount" />
                            <x-text-input-bs id="term_loan_amount" type="text" name="term_loan_amount" :value="old('term_loan_amount', $lead?->term_loan_amount)" placeholder="Enter term loan amount (in ₹)" />
                            <x-input-error-bs :messages="$errors->get('term_loan_amount')" />
                        </div>
                        <div>
                            <x-input-label-bs for="term_loan_machinery_details" value="Machinery / Term Details" />
                            <textarea id="term_loan_machinery_details" name="term_loan_machinery_details" rows="3" class="jb-textarea" placeholder="List machinery specs, quotations, construction scope...">{{ old('term_loan_machinery_details', $lead?->term_loan_machinery_details) }}</textarea>
                            <x-input-error-bs :messages="$errors->get('term_loan_machinery_details')" />
                        </div>
                    </div>
                </div>
              </div>
            </div>

            <div>
                <h3 class="jb-section-title mb-1">Current Loan Details</h3>
                <p class="jb-muted mb-3">Provide details for up to 6 outstanding loans.</p>

                <div class="table-responsive rounded-3" style="border:1px solid var(--slate-200)">
                    <table class="jb-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:3rem">SR</th>
                                <th>Bank Name</th>
                                <th>Loan Type</th>
                                <th>Loan Amount</th>
                                <th>Disburse Date</th>
                                <th>EMI</th>
                                <th>Outstanding</th>
                                <th>Tenure</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 6; $i++)
                                @php
                                    $loan = $lead?->current_loans[$i] ?? null;
                                @endphp
                                <tr>
                                    <td class="text-center fw-semibold" style="color:var(--slate-500)">{{ $i + 1 }}</td>
                                    <td>
                                        <input type="text" name="current_loans[{{ $i }}][bank_name]" value="{{ old("current_loans.{$i}.bank_name", $loan['bank_name'] ?? '') }}" class="jb-inline-input" placeholder="Bank Name">
                                    </td>
                                    <td>
                                        <input type="text" name="current_loans[{{ $i }}][loan_type]" value="{{ old("current_loans.{$i}.loan_type", $loan['loan_type'] ?? '') }}" class="jb-inline-input" placeholder="e.g. Business, Home">
                                    </td>
                                    <td>
                                        <input type="text" name="current_loans[{{ $i }}][loan_amount]" value="{{ old("current_loans.{$i}.loan_amount", $loan['loan_amount'] ?? '') }}" class="jb-inline-input" placeholder="Amount (₹)">
                                    </td>
                                    <td>
                                        <input type="text" name="current_loans[{{ $i }}][disburse_date]" value="{{ old("current_loans.{$i}.disburse_date", $loan['disburse_date'] ?? '') }}" class="jb-inline-input" placeholder="DD/MM/YYYY">
                                    </td>
                                    <td>
                                        <input type="text" name="current_loans[{{ $i }}][emi]" value="{{ old("current_loans.{$i}.emi", $loan['emi'] ?? '') }}" class="jb-inline-input" placeholder="EMI (₹)">
                                    </td>
                                    <td>
                                        <input type="text" name="current_loans[{{ $i }}][outstanding_amount]" value="{{ old("current_loans.{$i}.outstanding_amount", $loan['outstanding_amount'] ?? '') }}" class="jb-inline-input" placeholder="Outstanding (₹)">
                                    </td>
                                    <td>
                                        <input type="text" name="current_loans[{{ $i }}][tenure]" value="{{ old("current_loans.{$i}.tenure", $loan['tenure'] ?? '') }}" class="jb-inline-input" placeholder="Tenure (Months)">
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-start pt-3 mt-3 border-top">
                <button type="button" onclick="switchTab('business-tab', 'business-panel')" class="btn btn-secondary jb-btn-tall">
                    Previous
                </button>
            </div>
        </div>

        <!-- 5. Documents Panel -->
        @php
            $docTypesList = $documentTypes ?? \App\Models\DocumentType::where('status', 'active')->ordered()->get();
            $existingLeadDocs = $lead ? $lead->leadDocuments->groupBy('document_type_id') : collect();
        @endphp
        <div id="documents-panel" class="tab-panel jb-hidden">
            <div class="border-bottom pb-3 mb-3">
                <h3 class="fw-bold m-0" style="font-size:var(--text-base);color:var(--slate-900)">Lead Documents Checklist &amp; Upload</h3>
                <p class="mt-1 mb-0" style="font-size:var(--text-xs);color:var(--slate-500)">Upload required KYC, financial, and project documents for this lead. New files are saved when you save the lead.</p>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" style="font-size:var(--text-sm)">
                    <thead class="table-light">
                        <tr>
                            <th>Document Name</th>
                            <th class="text-center" style="width:22%">Upload</th>
                            <th class="text-center">Download</th>
                            <th class="text-center">Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($docTypesList as $docType)
                            @php
                                $code = $docType->code;
                                $uploadedDocs = $existingLeadDocs->get($docType->id, collect())->sortBy(fn ($d) => $d->side === 'back' ? 1 : 0)->values();
                                $acceptAttr = implode(',', array_map(fn($e) => '.' . trim($e), $docType->allowed_file_types ?? ['pdf', 'jpg', 'jpeg', 'png']));
                                $rowCount = max(1, $uploadedDocs->count());
                            @endphp

                            @for ($i = 0; $i < $rowCount; $i++)
                                @php $docItem = $uploadedDocs->get($i); @endphp
                                <tr>
                                    @if ($i === 0)
                                        <td rowspan="{{ $rowCount }}">{{ $docType->name }}</td>

                                        <td rowspan="{{ $rowCount }}" class="doc-item-wrapper">
                                            @can('documents.upload')
                                                <div class="d-flex flex-column align-items-center gap-2">
                                                    @if ($docType->has_front_back)
                                                        <div class="d-flex flex-wrap justify-content-center gap-2">
                                                            <label class="btn btn-outline-secondary btn-sm mb-0">
                                                                <input type="file" name="documents[{{ $code }}][front]" accept="{{ $acceptAttr }}" class="jb-hidden" onchange="handleFileSelect(this, '{{ $code }}-front-selected')">
                                                                Upload Front
                                                            </label>
                                                            <label class="btn btn-outline-secondary btn-sm mb-0">
                                                                <input type="file" name="documents[{{ $code }}][back]" accept="{{ $acceptAttr }}" class="jb-hidden" onchange="handleFileSelect(this, '{{ $code }}-back-selected')">
                                                                Upload Back
                                                            </label>
                                                        </div>
                                                        <div id="{{ $code }}-front-selected" class="jb-hidden text-muted" style="font-size:var(--text-xs)">Front: <span class="file-name"></span></div>
                                                        <div id="{{ $code }}-back-selected" class="jb-hidden text-muted" style="font-size:var(--text-xs)">Back: <span class="file-name"></span></div>
                                                        <x-input-error-bs :messages="$errors->get('documents.' . $code . '.front')" />
                                                        <x-input-error-bs :messages="$errors->get('documents.' . $code . '.back')" />
                                                    @elseif ($docType->allow_multiple)
                                                        <div id="multiple-files-container-{{ $code }}" class="d-flex flex-wrap justify-content-center gap-2">
                                                            <label class="btn btn-outline-secondary btn-sm mb-0">
                                                                <input type="file" name="documents[{{ $code }}][files][]" accept="{{ $acceptAttr }}" class="jb-hidden" data-row-id="row-initial-{{ $code }}" onchange="handleMultipleFileSelect(this)">
                                                                Upload File
                                                            </label>
                                                            <button type="button" onclick="addMultipleFileInput('{{ $code }}', '{{ $acceptAttr }}')" class="btn btn-outline-secondary btn-sm">Add Another</button>
                                                        </div>
                                                        <div class="multiple-selected-list d-flex flex-column gap-1 text-muted" style="font-size:var(--text-xs)"></div>
                                                        <x-input-error-bs :messages="$errors->get('documents.' . $code)" />
                                                    @else
                                                        <label class="btn btn-outline-secondary btn-sm mb-0">
                                                            <input type="file" name="documents[{{ $code }}][file]" accept="{{ $acceptAttr }}" class="jb-hidden" onchange="handleFileSelect(this, '{{ $code }}-single-selected')">
                                                            Upload File
                                                        </label>
                                                        <div id="{{ $code }}-single-selected" class="jb-hidden text-muted" style="font-size:var(--text-xs)"><span class="file-name"></span></div>
                                                        <x-input-error-bs :messages="$errors->get('documents.' . $code)" />
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endcan
                                        </td>
                                    @endif

                                    @if ($docItem && $lead)
                                        <td class="text-center">
                                            @can('documents.download')
                                                <a href="{{ route('admin.leads.documents.download', [$lead, $docItem]) }}" class="text-decoration-none" title="{{ $docItem->original_name }}">Download</a>
                                            @endcan
                                        </td>
                                        <td class="text-center">
                                            @can('documents.delete')
                                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteLeadDocument('{{ route('admin.leads.documents.destroy', [$lead, $docItem]) }}')">Delete</button>
                                            @endcan
                                        </td>
                                    @else
                                        <td></td>
                                        <td></td>
                                    @endif
                                </tr>
                            @endfor
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between pt-3 mt-4 border-top">
                <button type="button" onclick="switchTab('application-form-tab', 'application-form-panel')" class="btn btn-secondary jb-btn-tall">
                    Previous: Application Form
                </button>
                @can('sbi_stock_statement.view')
                    <button type="button" onclick="switchTab('stock-statement-tab', 'stock-statement-panel')" class="btn btn-secondary jb-btn-tall">
                        Next: Stock Statement
                    </button>
                @else
                    <button type="button" onclick="switchTab('bank-tab', 'bank-panel')" class="btn btn-secondary jb-btn-tall">
                        Next: Bank Details
                    </button>
                @endcan
            </div>
        </div>

        @can('application_form.view')
            @include('admin.leads.partials.application-form', ['lead' => $lead])
        @endcan

        @can('sbi_stock_statement.view')
            @include('admin.leads.partials.stock-statement', ['lead' => $lead])
        @endcan

        @can('or.view')
            @include('admin.leads.partials.opinion-report', ['lead' => $lead])
        @endcan

        @can('cma.view')
            @include('admin.leads.partials.cma', ['lead' => $lead])
        @endcan

        @can('dpr.view')
            @include('admin.leads.partials.dpr', ['lead' => $lead])
        @endcan
    </div>
</div>

<script>
let itrCardIndex = {{ count($itrs) }};

function addItrCard() {
    const container = document.getElementById('itr-cards-container');
    const card = document.createElement('div');
    card.className = 'itr-card jb-subcard d-flex flex-column gap-3';
    card.innerHTML = `
        <div class="d-flex align-items-center justify-content-between pb-2 border-bottom">
            <span class="d-flex align-items-center gap-2 fw-bold text-uppercase" style="font-size:var(--text-xs);letter-spacing:.05em;color:var(--slate-700)">
                <span style="width:.5rem;height:.5rem;border-radius:var(--radius-full);background:var(--primary-500)"></span>
                ITR Profile #<span class="itr-sr"></span>
            </span>
            <button type="button" onclick="removeItrCard(this)" class="btn btn-sm btn-danger">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Remove</span>
            </button>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="jb-field-label">Assessment Year (A.Y.)</label>
                <select name="itr_details[\${itrCardIndex}][assessment_year]" class="form-select select2 jb-input">
                    <option value="">Select Assessment Year</option>
                    @foreach ($assessmentYears as $ay)
                        <option value="{{ $ay->name }}">{{ $ay->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="jb-field-label">ITR Audited</label>
                <select name="itr_details[\${itrCardIndex}][itr_audited]" onchange="toggleItrFiles(this)" class="itr-audited-select form-select jb-input">
                    <option value="">Select Option</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </div>
        </div>

        <div class="itr-files-wrapper pt-3 border-top">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-lg-3 audit-report-field jb-hidden">
                    <label class="jb-field-label">Audit Report</label>
                    <input type="file" name="itr_details[\${itrCardIndex}][audit_report]" class="jb-file-input jb-file-input-sm" />
                </div>
                <div class="col-12 col-sm-6 col-lg-3 itr-file-field jb-hidden">
                    <label class="jb-field-label">ITR</label>
                    <input type="file" name="itr_details[\${itrCardIndex}][itr_file]" class="jb-file-input jb-file-input-sm" />
                </div>
                <div class="col-12 col-sm-6 col-lg-3 computation-field jb-hidden">
                    <label class="jb-field-label">Computation</label>
                    <input type="file" name="itr_details[\${itrCardIndex}][computation]" class="jb-file-input jb-file-input-sm" />
                </div>
                <div class="col-12 col-sm-6 col-lg-3 itr-form-field jb-hidden">
                    <label class="jb-field-label">ITR Form</label>
                    <input type="file" name="itr_details[\${itrCardIndex}][itr_form]" class="jb-file-input jb-file-input-sm" />
                </div>
            </div>
        </div>
    `;
    container.appendChild(card);
    itrCardIndex++;
    recalculateItrSr();
    if (window.initSelect2) {
        window.initSelect2();
    }
}

function removeItrCard(button) {
    const card = button.closest('.itr-card');
    card.remove();
    recalculateItrSr();
}

function recalculateItrSr() {
    document.querySelectorAll('.itr-sr').forEach((span, i) => {
        span.textContent = i + 1;
    });
}

function toggleItrFiles(selectElement) {
    const card = selectElement.closest('.itr-card');
    const val = selectElement.value;

    const auditField = card.querySelector('.audit-report-field');
    const itrField = card.querySelector('.itr-file-field');
    const compField = card.querySelector('.computation-field');
    const formField = card.querySelector('.itr-form-field');

    if (val === 'Yes') {
        auditField.classList.remove('jb-hidden');
        itrField.classList.remove('jb-hidden');
        compField.classList.remove('jb-hidden');
        formField.classList.remove('jb-hidden');
    } else if (val === 'No') {
        auditField.classList.add('jb-hidden');
        itrField.classList.remove('jb-hidden');
        compField.classList.remove('jb-hidden');
        formField.classList.remove('jb-hidden');
    } else {
        auditField.classList.add('jb-hidden');
        itrField.classList.add('jb-hidden');
        compField.classList.add('jb-hidden');
        formField.classList.add('jb-hidden');
    }
}

function addItrRow() { addItrCard(); }
function removeItrRow(btn) { removeItrCard(btn); }

let bankRowIndex = {{ count($banks) }};

function addBankRow() {
    const container = document.getElementById('bank-rows-container');
    const tr = document.createElement('tr');
    tr.className = 'bank-row';
    tr.innerHTML = `
        <td class="text-center fw-semibold bank-sr" style="color:var(--slate-500)"></td>
        <td>
            <input type="text" name="bank_details[\${bankRowIndex}][bank_name]" list="master-banks-list" value="" class="jb-inline-input" style="border:1px solid var(--slate-200)" placeholder="Select or type Bank name">
        </td>
        <td>
            <input type="text" name="bank_details[\${bankRowIndex}][account_number]" value="" class="jb-inline-input" placeholder="A/C Number">
        </td>
        <td>
            <input type="text" name="bank_details[\${bankRowIndex}][account_type]" value="" class="jb-inline-input" placeholder="e.g. Savings, Current">
        </td>
        <td>
            <input type="text" name="bank_details[\${bankRowIndex}][ifsc_code]" value="" class="jb-inline-input" placeholder="IFSC Code">
        </td>
        <td class="text-center">
            <button type="button" onclick="removeBankRow(this)" class="jb-icon-btn" style="color:var(--red-500)">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    `;
    container.appendChild(tr);
    bankRowIndex++;
    recalculateBankSr();
}

function removeBankRow(button) {
    const row = button.closest('.bank-row');
    row.remove();
    recalculateBankSr();
}

function recalculateBankSr() {
    document.querySelectorAll('.bank-sr').forEach((td, i) => {
        td.textContent = i + 1;
    });
}

function switchTab(tabId, panelId) {
    // Hide all panels
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.add('jb-hidden');
    });
    // Show target panel
    document.getElementById(panelId).classList.remove('jb-hidden');

    // Reset tab buttons — only the active/inactive state.
    document.querySelectorAll('.jb-tab-btn').forEach(tab => tab.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
    @if (session('open_tab') === 'dpr' || collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'dpr.')))
        if (document.getElementById('dpr-tab')) switchTab('dpr-tab', 'dpr-panel');
    @endif
    @if (session('open_tab') === 'cma' || collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'cma.')))
        if (document.getElementById('cma-tab')) switchTab('cma-tab', 'cma-panel');
    @endif
    @if (session('open_tab') === 'opinion-report' || collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'opr.')))
        if (document.getElementById('opinion-report-tab')) switchTab('opinion-report-tab', 'opinion-report-panel');
    @endif
    @if (session('open_tab') === 'stock-statement' || collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'ss.')))
        if (document.getElementById('stock-statement-tab')) switchTab('stock-statement-tab', 'stock-statement-panel');
    @endif
    @if (session('open_tab') === 'application-form' || collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'af')))
        if (document.getElementById('application-form-tab')) switchTab('application-form-tab', 'application-form-panel');
    @endif

    // Track manual edits in Bank Loan fields
    ['bld_applicant', 'bld_office_address', 'bld_years'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', () => el.dataset.manuallyEdited = 'true');
        }
    });

    function syncBankLoanLive() {
        const kycName = document.getElementById('name')?.value || '';
        const bizName = document.getElementById('business_name')?.value || document.getElementById('firm_name')?.value || '';
        const bizAddress = document.getElementById('business_address')?.value || '';
        const bizExp = document.getElementById('business_experience')?.value || '';

        const bldApplicant = document.getElementById('bld_applicant');
        const bldAddress = document.getElementById('bld_office_address');
        const bldYears = document.getElementById('bld_years');

        if (bldApplicant && !bldApplicant.dataset.manuallyEdited && !bldApplicant.value) {
            let text = kycName;
            if (bizName) text += (text ? ' - Prop. Of ' : '') + bizName;
            bldApplicant.value = text;
        }

        if (bldAddress && !bldAddress.dataset.manuallyEdited && !bldAddress.value) {
            let text = '';
            if (bizName) text += bizName + '\n';
            if (bizAddress) text += bizAddress;
            bldAddress.value = text.trim();
        }

        if (bldYears && !bldYears.dataset.manuallyEdited && !bldYears.value) {
            bldYears.value = bizExp;
        }
    }

    ['name', 'business_name', 'firm_name', 'business_address', 'business_experience'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', syncBankLoanLive);
    });

    // Realtime "Inspection Remarks / Observations" paragraph.
    // Regenerates from the fields below as the user types, until the user
    // edits the textarea directly (or it already holds a previously saved
    // paragraph) — at that point it's left alone unless "Regenerate" is clicked.
    const bldRemarks = document.getElementById('bld_remarks');
    if (bldRemarks) {
        if (bldRemarks.dataset.hasSavedValue === '1') {
            bldRemarks.dataset.manuallyEdited = 'true';
        }
        bldRemarks.addEventListener('input', () => bldRemarks.dataset.manuallyEdited = 'true');

        const remarksSourceIds = [
            'name', 'business_name', 'firm_name', 'business_address',
            'business_activity', 'business_experience', 'no_of_manpower',
            'constitution_id', 'city_id', 'term_loan_machinery_details', 'term_loan_amount',
        ];

        let remarksDebounceTimer = null;

        function fetchRemarksPreview(force) {
            const payload = {};
            remarksSourceIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) payload[id] = el.value;
            });
            const visitDateEl = document.querySelector('[name="bank_loan_details[visit_office_date]"]');
            if (visitDateEl) payload.visit_office_date = visitDateEl.value;
            const tvrEl = document.querySelector('input[name="bank_loan_details[office_tvr_result]"]:checked');
            if (tvrEl) payload.office_tvr_result = tvrEl.value;

            fetch('{{ route('admin.leads.inspection-remarks.preview') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify(payload),
            })
                .then(res => (res.ok ? res.json() : Promise.reject()))
                .then(data => {
                    if (force || bldRemarks.dataset.manuallyEdited !== 'true') {
                        bldRemarks.value = data.remarks || '';
                        if (force) bldRemarks.dataset.manuallyEdited = 'false';
                    }
                })
                .catch(() => {});
        }

        function scheduleRemarksPreview() {
            if (bldRemarks.dataset.manuallyEdited === 'true') return;
            clearTimeout(remarksDebounceTimer);
            remarksDebounceTimer = setTimeout(() => fetchRemarksPreview(false), 400);
        }

        remarksSourceIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', scheduleRemarksPreview);
        });

        const regenerateBtn = document.getElementById('bld_remarks_regenerate');
        if (regenerateBtn) {
            regenerateBtn.addEventListener('click', () => fetchRemarksPreview(true));
        }
    }
});

// The delete button sits inside the lead form, so it posts through a throwaway form instead of nesting one.
function deleteLeadDocument(url) {
    if (!confirm('Remove this document?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    const token = document.querySelector('input[name="_token"]');
    [['_token', token ? token.value : ''], ['_method', 'DELETE']].forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    });
    document.body.appendChild(form);
    form.submit();
}

function handleFileSelect(input, targetId) {
    const el = document.getElementById(targetId);
    if (!el) return;
    if (input.files && input.files[0]) {
        const fileNameSpan = el.querySelector('.file-name');
        if (fileNameSpan) fileNameSpan.textContent = input.files[0].name;
        el.classList.remove('jb-hidden');
    } else {
        el.classList.add('jb-hidden');
    }
}

function handleMultipleFileSelect(input) {
    const container = input.closest('.doc-item-wrapper');
    if (!container) return;
    const selectedList = container.querySelector('.multiple-selected-list');
    if (!selectedList) return;
    if (input.files && input.files[0]) {
        let rowId = input.getAttribute('data-row-id');
        if (!rowId) {
            rowId = 'row-' + Math.random().toString(36).substr(2, 9);
            input.setAttribute('data-row-id', rowId);
        }
        let existingRow = selectedList.querySelector(`[data-for="${rowId}"]`);
        if (!existingRow) {
            existingRow = document.createElement('div');
            existingRow.setAttribute('data-for', rowId);
            existingRow.className = 'jb-file-selected-note';
            existingRow.innerHTML = `<span>Selected:</span> <span class="fw-semibold text-truncate" style="max-width:180px">${input.files[0].name}</span>`;
            selectedList.appendChild(existingRow);
        } else {
            const nameSpan = existingRow.querySelector('span.fw-semibold');
            if (nameSpan) nameSpan.textContent = input.files[0].name;
        }
    }
}

function addMultipleFileInput(code, acceptAttr) {
    const container = document.getElementById('multiple-files-container-' + code);
    if (!container) return;
    const addBtn = container.querySelector('button');

    const label = document.createElement('label');
    label.className = 'btn btn-outline-secondary btn-sm mb-0';

    const rowId = 'row-' + Math.random().toString(36).substr(2, 9);
    const input = document.createElement('input');
    input.type = 'file';
    input.name = `documents[${code}][files][]`;
    input.accept = acceptAttr;
    input.className = 'jb-hidden';
    input.setAttribute('data-row-id', rowId);
    input.onchange = function() { handleMultipleFileSelect(this); };

    label.appendChild(input);
    label.appendChild(document.createTextNode('Upload File'));
    container.insertBefore(label, addBtn);
    input.click();
}
</script>
