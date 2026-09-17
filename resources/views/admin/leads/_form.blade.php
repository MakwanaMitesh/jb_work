{{-- Expects: $lead (nullable, for edit), $cities, $agents --}}
@php $lead = $lead ?? null; @endphp

<div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
    
    <!-- Tab Navigation Header -->
    <div class="bg-slate-50/50 dark:bg-slate-950/25 border-b border-slate-200/80 dark:border-slate-800 px-6 py-4 flex flex-wrap gap-2">
        <button type="button" onclick="switchTab('kyc-tab', 'kyc-panel')" id="kyc-tab" class="tab-btn flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 font-semibold text-sm rounded-xl shadow-sm border border-primary-600 dark:border-primary-500 focus:outline-none transition">
            <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>KYC Details</span>
        </button>

        <button type="button" onclick="switchTab('bank-loan-tab', 'bank-loan-panel')" id="bank-loan-tab" class="tab-btn flex items-center gap-2 px-4 py-2 text-slate-500 hover:text-slate-750 dark:hover:text-slate-300 font-medium text-sm rounded-xl transition focus:outline-none hidden">
            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Bank Loan</span>
        </button>
        
        <button type="button" onclick="switchTab('bank-tab', 'bank-panel')" id="bank-tab" class="tab-btn flex items-center gap-2 px-4 py-2 text-slate-500 hover:text-slate-750 dark:hover:text-slate-300 font-medium text-sm rounded-xl transition focus:outline-none">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <span>Bank Details</span>
        </button>
        
        <button type="button" onclick="switchTab('business-tab', 'business-panel')" id="business-tab" class="tab-btn flex items-center gap-2 px-4 py-2 text-slate-500 hover:text-slate-750 dark:hover:text-slate-300 font-medium text-sm rounded-xl transition focus:outline-none">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <span>Business Details</span>
        </button>
        
        <button type="button" onclick="switchTab('loan-tab', 'loan-panel')" id="loan-tab" class="tab-btn flex items-center gap-2 px-4 py-2 text-slate-500 hover:text-slate-750 dark:hover:text-slate-300 font-medium text-sm rounded-xl transition focus:outline-none">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Loan Details</span>
        </button>

        <button type="button" onclick="switchTab('documents-tab', 'documents-panel')" id="documents-tab" class="tab-btn flex items-center gap-2 px-4 py-2 text-slate-500 hover:text-slate-750 dark:hover:text-slate-300 font-medium text-sm rounded-xl transition focus:outline-none">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Documents</span>
        </button>
    </div>

    <!-- Active Panel Content -->
    <div class="p-6 sm:p-8">
        
        <!-- 1. KYC Details Panel -->
        <div id="kyc-panel" class="tab-panel space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="name" value="Customer Name" :required="true" />
                    <x-text-input id="name" type="text" name="name" :value="old('name', $lead?->name)" placeholder="Enter full name of applicant" required autofocus />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="date_of_birth" value="Date of Birth" />
                    <x-text-input id="date_of_birth" type="date" name="date_of_birth" :value="old('date_of_birth', $lead?->date_of_birth?->format('Y-m-d'))" />
                    <x-input-error :messages="$errors->get('date_of_birth')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-input-label for="gender" value="Gender" />
                    <select id="gender" name="gender" class="form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                        <option value="">Select Gender</option>
                        <option value="Male" @selected(old('gender', $lead?->gender) === 'Male')>Male</option>
                        <option value="Female" @selected(old('gender', $lead?->gender) === 'Female')>Female</option>
                    </select>
                    <x-input-error :messages="$errors->get('gender')" />
                </div>
                <div>
                    <x-input-label for="education" value="Education" />
                    <x-text-input id="education" type="text" name="education" :value="old('education', $lead?->education)" placeholder="e.g. Graduate, Post Graduate" />
                    <x-input-error :messages="$errors->get('education')" />
                </div>
                <div>
                    <x-input-label for="mother_name" value="Mother Name" />
                    <x-text-input id="mother_name" type="text" name="mother_name" :value="old('mother_name', $lead?->mother_name)" placeholder="Enter mother's name" />
                    <x-input-error :messages="$errors->get('mother_name')" />
                </div>
            </div>

            <div>
                <x-input-label for="address" value="Address" />
                <textarea id="address" name="address" rows="3" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 text-sm px-3 py-2" placeholder="Enter complete residential address...">{{ old('address', $lead?->address) }}</textarea>
                <x-input-error :messages="$errors->get('address')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <x-input-label for="aadhar_card" value="Aadhaar Card" />
                    <x-text-input id="aadhar_card" type="text" name="aadhar_card" :value="old('aadhar_card', $lead?->aadhar_card)" placeholder="Enter 12-digit Aadhaar Number" />
                    <x-input-error :messages="$errors->get('aadhar_card')" />
                </div>
                <div>
                    <x-input-label for="pan_card" value="PAN Card" />
                    <x-text-input id="pan_card" type="text" name="pan_card" :value="old('pan_card', $lead?->pan_card)" placeholder="Enter 10-digit PAN (e.g. ABCDE1234F)" />
                    <x-input-error :messages="$errors->get('pan_card')" />
                </div>
                <div>
                    <x-input-label for="udyam_registration" value="Udyam Registration" />
                    <x-text-input id="udyam_registration" type="text" name="udyam_registration" :value="old('udyam_registration', $lead?->udyam_registration)" placeholder="e.g. UDYAM-GJ-00-0000000" />
                    <x-input-error :messages="$errors->get('udyam_registration')" />
                </div>
                <div>
                    <x-input-label for="fssai_license" value="FSSAI License No." />
                    <x-text-input id="fssai_license" type="text" name="fssai_license" :value="old('fssai_license', $lead?->fssai_license)" placeholder="e.g. 10012022000123" />
                    <x-input-error :messages="$errors->get('fssai_license')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-input-label for="mobile_number" value="Mobile Number" :required="true" />
                    <x-text-input id="mobile_number" type="tel" name="mobile_number" :value="old('mobile_number', $lead?->mobile_number)" placeholder="Enter 10-digit mobile number" required />
                    <x-input-error :messages="$errors->get('mobile_number')" />
                </div>
                <div>
                    <x-input-label for="alternate_mobile_number" value="Alternate Mobile Number" />
                    <x-text-input id="alternate_mobile_number" type="tel" name="alternate_mobile_number" :value="old('alternate_mobile_number', $lead?->alternate_mobile_number)" placeholder="Enter alternate contact number" />
                    <x-input-error :messages="$errors->get('alternate_mobile_number')" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" type="email" name="email" :value="old('email', $lead?->email)" placeholder="e.g. applicant@example.com" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-1.5 h-3 bg-blue-600 rounded-full"></span> ITR Details
                    </h4>
                    <button type="button" onclick="addItrCard()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-50 hover:bg-primary-100 dark:bg-primary-950/20 dark:hover:bg-primary-900/30 text-primary-700 dark:text-primary-400 text-xs font-semibold rounded-lg shadow-sm border border-primary-200/50 dark:border-primary-800 transition focus:outline-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Add ITR Details</span>
                    </button>
                </div>

                <!-- Top-Level Common ITR User ID & Password -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 rounded-2xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800">
                    <div>
                        <x-input-label for="itr_id" value="ITR User ID" />
                        <x-text-input id="itr_id" type="text" name="itr_id" :value="old('itr_id', $lead?->itr_id)" placeholder="ITR User ID" />
                        <x-input-error :messages="$errors->get('itr_id')" />
                    </div>
                    <div>
                        <x-input-label for="itr_password" value="ITR Password" />
                        <x-text-input id="itr_password" type="text" name="itr_password" :value="old('itr_password', $lead?->itr_password)" placeholder="ITR Password" />
                        <x-input-error :messages="$errors->get('itr_password')" />
                    </div>
                </div>

                <!-- Dynamic Cards Container -->
                <div id="itr-cards-container" class="space-y-4">
                    @php
                        $itrs = old('itr_details', $lead?->formatted_itr_details ?? []);
                        if (empty($itrs)) {
                            $itrs = [['assessment_year' => '', 'itr_audited' => '']];
                        }
                    @endphp
                    @foreach ($itrs as $index => $itr)
                        @php $audited = $itr['itr_audited'] ?? ''; @endphp
                        <div class="itr-card p-5 bg-slate-50/50 dark:bg-slate-800/20 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-4 relative">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-200/60 dark:border-slate-800/60">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                                    ITR Profile #<span class="itr-sr">{{ $index + 1 }}</span>
                                </span>
                                <button type="button" onclick="removeItrCard(this)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg border border-red-200/60 dark:border-red-900/40 transition focus:outline-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Remove</span>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                                <div>
                                    <x-input-label value="Assessment Year (A.Y.)" />
                                    <select name="itr_details[{{ $index }}][assessment_year]" class="form-select select2 w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                                        <option value="">Select Assessment Year</option>
                                        @foreach ($assessmentYears as $ay)
                                            <option value="{{ $ay->name }}" @selected(($itr['assessment_year'] ?? '') === $ay->name)>{{ $ay->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label value="ITR Audited" />
                                    <select name="itr_details[{{ $index }}][itr_audited]" onchange="toggleItrFiles(this)" class="itr-audited-select form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                                        <option value="">Select Option</option>
                                        <option value="Yes" @selected($audited === 'Yes')>Yes</option>
                                        <option value="No" @selected($audited === 'No')>No</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Dynamic File Upload Fields -->
                            <div class="itr-files-wrapper pt-3 border-t border-slate-200/40 dark:border-slate-800/40">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <!-- 1) Audit Report (Visible ONLY when ITR Audited === Yes) -->
                                    <div class="audit-report-field {{ $audited === 'Yes' ? '' : 'hidden' }}">
                                        <x-input-label value="Audit Report" />
                                        <input type="file" name="itr_details[{{ $index }}][audit_report]" class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-950/40 dark:file:text-primary-400 hover:file:bg-primary-100 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 p-1" />
                                        @if (!empty($itr['audit_report']))
                                            <input type="hidden" name="itr_details[{{ $index }}][existing_audit_report]" value="{{ $itr['audit_report'] }}">
                                            <div class="mt-1 flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <a href="{{ Storage::url($itr['audit_report']) }}" target="_blank" class="hover:underline font-medium">View Existing File</a>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- 2) ITR File -->
                                    <div class="itr-file-field {{ in_array($audited, ['Yes', 'No']) ? '' : 'hidden' }}">
                                        <x-input-label value="ITR" />
                                        <input type="file" name="itr_details[{{ $index }}][itr_file]" class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-950/40 dark:file:text-primary-400 hover:file:bg-primary-100 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 p-1" />
                                        @if (!empty($itr['itr_file']))
                                            <input type="hidden" name="itr_details[{{ $index }}][existing_itr_file]" value="{{ $itr['itr_file'] }}">
                                            <div class="mt-1 flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <a href="{{ Storage::url($itr['itr_file']) }}" target="_blank" class="hover:underline font-medium">View Existing File</a>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- 3) Computation File -->
                                    <div class="computation-field {{ in_array($audited, ['Yes', 'No']) ? '' : 'hidden' }}">
                                        <x-input-label value="Computation" />
                                        <input type="file" name="itr_details[{{ $index }}][computation]" class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-950/40 dark:file:text-primary-400 hover:file:bg-primary-100 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 p-1" />
                                        @if (!empty($itr['computation']))
                                            <input type="hidden" name="itr_details[{{ $index }}][existing_computation]" value="{{ $itr['computation'] }}">
                                            <div class="mt-1 flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <a href="{{ Storage::url($itr['computation']) }}" target="_blank" class="hover:underline font-medium">View Existing File</a>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- 4) ITR Form File -->
                                    <div class="itr-form-field {{ in_array($audited, ['Yes', 'No']) ? '' : 'hidden' }}">
                                        <x-input-label value="ITR Form" />
                                        <input type="file" name="itr_details[{{ $index }}][itr_form]" class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-950/40 dark:file:text-primary-400 hover:file:bg-primary-100 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 p-1" />
                                        @if (!empty($itr['itr_form']))
                                            <input type="hidden" name="itr_details[{{ $index }}][existing_itr_form]" value="{{ $itr['itr_form'] }}">
                                            <div class="mt-1 flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <a href="{{ Storage::url($itr['itr_form']) }}" target="_blank" class="hover:underline font-medium">View Existing File</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-input-label for="bank_id" value="Bank" />
                    <select id="bank_id" name="bank_id" class="form-select select2 w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                        <option value="">Select Bank</option>
                        @foreach ($masterBanks as $bank)
                            <option value="{{ $bank->id }}" @selected(old('bank_id', $lead?->bank_id) == $bank->id)>{{ $bank->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('bank_id')" />
                </div>
                <div>
                    <x-input-label for="loan_product_id" value="Loan Product" :required="true" />
                    <select id="loan_product_id" name="loan_product_id" required class="form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                        <option value="">Select Loan Product</option>
                        @foreach ($loanProducts as $product)
                            <option value="{{ $product->id }}" @selected(old('loan_product_id', $lead?->loan_product_id) == $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('loan_product_id')" />
                </div>
                <div>
                    <x-input-label for="constitution_id" value="Customer Constitution" :required="true" />
                    <select id="constitution_id" name="constitution_id" required class="form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                        <option value="">Select Constitution</option>
                        @foreach ($constitutions as $const)
                            <option value="{{ $const->id }}" @selected(old('constitution_id', $lead?->constitution_id) == $const->id)>{{ $const->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('constitution_id')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-input-label for="source" value="Lead Source" />
                    <x-text-input id="source" type="text" name="source" :value="old('source', $lead?->source)" placeholder="e.g. Website, Reference" />
                    <x-input-error :messages="$errors->get('source')" />
                </div>
                <div>
                    <x-input-label for="city_id" value="City" />
                    <select id="city_id" name="city_id" class="form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                        <option value="">Select City</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" @selected(old('city_id', $lead?->city_id) == $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('city_id')" />
                </div>
                <div>
                    <x-input-label for="agent_id" value="Assigned Agent" />
                    <select id="agent_id" name="agent_id" class="form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                        <option value="">Select Agent</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}" @selected(old('agent_id', $lead?->agent_id) == $agent->id)>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('agent_id')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="status" value="Status" :required="true" />
                    <select id="status" name="status" class="form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                        @foreach(['new', 'contacted', 'in_progress', 'converted', 'lost', 'visit_pending', 'visit_completed', 'documentation_pending', 'documentation_in_progress', 'documentation_completed', 'under_process', 'approved', 'rejected', 'completed', 'cancelled'] as $st)
                            <option value="{{ $st }}" @selected(old('status', $lead?->status ?? 'new') === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('status')" />
                </div>
                <div>
                    <x-input-label for="notes" value="Notes / Requirements" />
                    <textarea id="notes" name="notes" rows="2" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 text-sm px-3 py-2" placeholder="Describe requirements, follow-ups, or notes...">{{ old('notes', $lead?->notes) }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" />
                </div>
            </div>
            
            <div class="flex justify-end pt-4 border-t border-slate-200/80 dark:border-slate-800">
                <button type="button" onclick="switchTab('bank-tab', 'bank-panel')" class="inline-flex items-center justify-center h-10 px-5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-semibold text-sm transition focus:outline-none">
                    Next: Bank Details
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
        <div id="bank-loan-panel" class="tab-panel space-y-6 hidden">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden p-6 space-y-6">
                <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-4 bg-amber-500 rounded-full"></span>
                        Bank Loan Verification Report Form
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Verification and workplace report details for Bank Loan processing.</p>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full border-collapse text-sm">
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-800 dark:text-slate-200">
                            <!-- Row 1 -->
                            <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 w-12 text-center border-r border-slate-200 dark:border-slate-800">1</td>
                                <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 w-1/3 border-r border-slate-200 dark:border-slate-800">
                                    Name of the Applicant / Co-Applicant / Guarantor
                                </td>
                                <td class="p-3">
                                    <input type="text" id="bld_applicant" name="bank_loan_details[applicant_coapplicant_guarantor_name]" value="{{ old('bank_loan_details.applicant_coapplicant_guarantor_name', $bld['applicant_coapplicant_guarantor_name'] ?? $defaultApplicant) }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm px-3 py-2" placeholder="Enter Applicant / Co-Applicant / Guarantor name">
                                </td>
                            </tr>

                            <!-- Row 2 -->
                            <tr>
                                <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">2</td>
                                <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">
                                    Visit to the Office / Work Place of the Borrower
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-semibold text-slate-500 shrink-0">Date of Visit:</span>
                                        <input type="date" name="bank_loan_details[visit_office_date]" value="{{ old('bank_loan_details.visit_office_date', $bld['visit_office_date'] ?? '') }}" class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm px-3 py-2">
                                    </div>
                                </td>
                            </tr>

                            <!-- Row 3 -->
                            <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">3</td>
                                <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">
                                    Name of the Office / Organization, address & Office Phone No.
                                </td>
                                <td class="p-3 space-y-3">
                                    <textarea id="bld_office_address" name="bank_loan_details[office_organization_address]" rows="2" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm px-3 py-2" placeholder="Enter organization name and complete address...">{{ old('bank_loan_details.office_organization_address', $bld['office_organization_address'] ?? $defaultOfficeAddr) }}</textarea>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-semibold text-slate-500 shrink-0">Office Phone No:</span>
                                        <input type="text" name="bank_loan_details[office_phone_no]" value="{{ old('bank_loan_details.office_phone_no', $bld['office_phone_no'] ?? '') }}" class="w-full max-w-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm px-3 py-1.5" placeholder="Enter office phone number">
                                    </div>
                                </td>
                            </tr>

                            <!-- Row 4 -->
                            <tr>
                                <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">4</td>
                                <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">
                                    For Self Employed:
                                </td>
                                <td class="p-4 space-y-4">
                                    <!-- 4A -->
                                    <div class="space-y-2">
                                        <span class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide">A. Type of Organization</span>
                                        <div class="flex flex-wrap gap-4 text-xs">
                                            @foreach(['Partnership', 'Proprietorship', 'Pvt. Ltd.', 'Public Ltd', 'Others'] as $opt)
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" name="bank_loan_details[type_of_organization][]" value="{{ $opt }}" @checked(in_array($opt, $orgTypes)) class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                    <span>{{ $opt }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- 4B -->
                                    <div class="space-y-2 pt-2 border-t border-slate-200/60 dark:border-slate-800/60">
                                        <span class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide">B. Nature of Business</span>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 text-xs">
                                            @foreach(['TRADING', 'Contractor', 'Processing', 'Builder', 'Manufacturing', 'Brokerage', 'Consultancy', 'Professional', 'Others'] as $opt)
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" name="bank_loan_details[nature_of_business][]" value="{{ $opt }}" @checked(in_array($opt, $natureBiz)) class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                    <span>{{ $opt }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- 4C -->
                                    <div class="space-y-2 pt-2 border-t border-slate-200/60 dark:border-slate-800/60">
                                        <span class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide">C. Whether own office / rented / leased</span>
                                        <div class="flex flex-wrap gap-6 text-xs">
                                            @foreach(['OWNED', 'RENTED', 'LEASED'] as $opt)
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" name="bank_loan_details[office_ownership][]" value="{{ $opt }}" @checked(in_array($opt, $officeOwnership)) class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                    <span class="font-semibold">{{ $opt }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Row 5 -->
                            <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">5</td>
                                <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">
                                    Land mark for Place of work
                                </td>
                                <td class="p-3">
                                    <input type="text" name="bank_loan_details[workplace_landmark]" value="{{ old('bank_loan_details.workplace_landmark', $bld['workplace_landmark'] ?? '') }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm px-3 py-2" placeholder="Enter workplace landmark or nearby location">
                                </td>
                            </tr>

                            <!-- Row 6 -->
                            <tr>
                                <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">6</td>
                                <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">
                                    Number of year Service / Business
                                </td>
                                <td class="p-3">
                                    <input type="text" id="bld_years" name="bank_loan_details[years_in_business]" value="{{ old('bank_loan_details.years_in_business', $bld['years_in_business'] ?? $defaultYears) }}" class="w-full max-w-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm px-3 py-2" placeholder="Enter total service or business experience">
                                </td>
                            </tr>

                            <!-- Row 7 -->
                            <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">7</td>
                                <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">
                                    Designation of the APPLICANT / GUARANTOR:
                                </td>
                                <td class="p-3">
                                    <input type="text" name="bank_loan_details[designation_applicant_guarantor]" value="{{ old('bank_loan_details.designation_applicant_guarantor', $bld['designation_applicant_guarantor'] ?? '') }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm px-3 py-2" placeholder="Enter designation (e.g. Proprietor, Partner, Director)">
                                </td>
                            </tr>

                            <!-- Row 8 -->
                            <tr>
                                <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">8</td>
                                <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">
                                    Whom met (Name of the Person contacted & designation) Pl give telephone nos.
                                </td>
                                <td class="p-3">
                                    <input type="text" name="bank_loan_details[whom_met_details]" value="{{ old('bank_loan_details.whom_met_details', $bld['whom_met_details'] ?? '') }}" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm px-3 py-2" placeholder="Enter contacted person name, designation & phone number">
                                </td>
                            </tr>

                            <!-- Row 9 -->
                            <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">9</td>
                                <td class="p-4 font-semibold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">
                                    Office TVR Result (Positive / Negative)
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center gap-6 text-sm">
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="bank_loan_details[office_tvr_result]" value="Positive" @checked(old('bank_loan_details.office_tvr_result', $bld['office_tvr_result'] ?? '') === 'Positive') class="text-emerald-600 focus:ring-emerald-500">
                                            <span class="font-semibold text-emerald-700 dark:text-emerald-400">Positive</span>
                                        </label>
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="bank_loan_details[office_tvr_result]" value="Negative" @checked(old('bank_loan_details.office_tvr_result', $bld['office_tvr_result'] ?? '') === 'Negative') class="text-rose-600 focus:ring-rose-500">
                                            <span class="font-semibold text-rose-700 dark:text-rose-400">Negative</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-between pt-4 border-t border-slate-200/80 dark:border-slate-800">
                    <button type="button" onclick="switchTab('kyc-tab', 'kyc-panel')" class="inline-flex items-center justify-center h-10 px-5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                        Previous
                    </button>
                    <button type="button" onclick="switchTab('bank-tab', 'bank-panel')" class="inline-flex items-center justify-center h-10 px-5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-semibold text-sm transition focus:outline-none">
                        Next: Bank Details
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. Bank Details Panel -->
        <div id="bank-panel" class="tab-panel space-y-6 hidden">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Current Bank Details</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage bank account profiles for this lead.</p>
                    </div>
                    <button type="button" onclick="addBankRow()" class="inline-flex items-center gap-1.5 px-3 py-2 bg-primary-50 hover:bg-primary-100 dark:bg-primary-950/20 dark:hover:bg-primary-900/30 text-primary-700 dark:text-primary-400 text-xs font-semibold rounded-lg shadow-sm border border-primary-200/50 dark:border-primary-800 transition focus:outline-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Bank</span>
                    </button>
                </div>
                
                <datalist id="master-banks-list">
                    @foreach ($masterBanks ?? [] as $mb)
                        <option value="{{ $mb->name }}">
                    @endforeach
                </datalist>

                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            <tr>
                                <th class="p-4 border-b border-slate-200 dark:border-slate-800 w-12 text-center">SR</th>
                                <th class="p-4 border-b border-slate-200 dark:border-slate-800">Bank Name</th>
                                <th class="p-4 border-b border-slate-200 dark:border-slate-800">A/C Number</th>
                                <th class="p-4 border-b border-slate-200 dark:border-slate-800">A/C Type</th>
                                <th class="p-4 border-b border-slate-200 dark:border-slate-800">IFSC Code</th>
                                <th class="p-4 border-b border-slate-200 dark:border-slate-800 w-16 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="bank-rows-container" class="divide-y divide-slate-100 dark:divide-slate-800">
                            @php
                                $banks = old('bank_details', $lead?->bank_details ?? []);
                                if (empty($banks)) {
                                    $banks = [['bank_name' => '', 'account_number' => '', 'account_type' => '', 'ifsc_code' => '']];
                                }
                            @endphp
                            @foreach ($banks as $index => $bank)
                                <tr class="bank-row hover:bg-slate-50/50 dark:hover:bg-slate-800/10">
                                    <td class="p-4 text-center font-semibold text-slate-500 bank-sr">{{ $index + 1 }}</td>
                                    <td class="p-2">
                                        <input type="text" name="bank_details[{{ $index }}][bank_name]" list="master-banks-list" value="{{ $bank['bank_name'] ?? '' }}" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-3 py-2 text-slate-900 dark:text-slate-100" placeholder="Select or type Bank name">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="bank_details[{{ $index }}][account_number]" value="{{ $bank['account_number'] ?? '' }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="A/C Number">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="bank_details[{{ $index }}][account_type]" value="{{ $bank['account_type'] ?? '' }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="e.g. Savings, Current">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="bank_details[{{ $index }}][ifsc_code]" value="{{ $bank['ifsc_code'] ?? '' }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="IFSC Code">
                                    </td>
                                    <td class="p-2 text-center">
                                        <button type="button" onclick="removeBankRow(this)" class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-md transition focus:outline-none">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="flex justify-between pt-4 border-t border-slate-200/80 dark:border-slate-800">
                <button type="button" onclick="switchTab('kyc-tab', 'kyc-panel')" class="inline-flex items-center justify-center h-10 px-5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                    Previous
                </button>
                <button type="button" onclick="switchTab('business-tab', 'business-panel')" class="inline-flex items-center justify-center h-10 px-5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-semibold text-sm transition focus:outline-none">
                    Next: Business Details
                </button>
            </div>
        </div>

        <!-- 3. Business Details Panel -->
        <div id="business-panel" class="tab-panel space-y-6 hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="business_name" value="Business Name" />
                    <x-text-input id="business_name" type="text" name="business_name" :value="old('business_name', $lead?->business_name)" placeholder="Enter official business / enterprise name" />
                    <x-input-error :messages="$errors->get('business_name')" />
                </div>
                <div>
                    <x-input-label for="constitution_of_business" value="Constitution of Business" />
                    <x-text-input id="constitution_of_business" type="text" name="constitution_of_business" :value="old('constitution_of_business', $lead?->constitution_of_business)" placeholder="e.g. Proprietorship, Partnership, Pvt. Ltd." />
                    <x-input-error :messages="$errors->get('constitution_of_business')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="introduction" value="Introduction / Business Activity Details" />
                    <textarea id="introduction" name="introduction" rows="3" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 text-sm px-3 py-2" placeholder="Brief introduction of the business operations...">{{ old('introduction', $lead?->introduction) }}</textarea>
                    <x-input-error :messages="$errors->get('introduction')" />
                </div>
                <div>
                    <x-input-label for="business_address" value="Business Address" />
                    <textarea id="business_address" name="business_address" rows="3" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 text-sm px-3 py-2" placeholder="Enter complete business unit address...">{{ old('business_address', $lead?->business_address) }}</textarea>
                    <x-input-error :messages="$errors->get('business_address')" />
                </div>
            </div>

            <div class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4">GST Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <x-input-label for="gst_applicable" value="GST Registered" />
                        <select id="gst_applicable" name="gst_applicable" class="form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                            <option value="">Select Option</option>
                            <option value="Yes" @selected(old('gst_applicable', $lead?->gst_applicable) === 'Yes')>Yes</option>
                            <option value="No" @selected(old('gst_applicable', $lead?->gst_applicable) === 'No')>No</option>
                        </select>
                        <x-input-error :messages="$errors->get('gst_applicable')" />
                    </div>
                    <div>
                        <x-input-label for="gst_number" value="GST Number" />
                        <x-text-input id="gst_number" type="text" name="gst_number" :value="old('gst_number', $lead?->gst_number)" placeholder="Enter 15-character GSTIN" />
                        <x-input-error :messages="$errors->get('gst_number')" />
                    </div>
                    <div>
                        <x-input-label for="gst_id" value="GST Portal ID" />
                        <x-text-input id="gst_id" type="text" name="gst_id" :value="old('gst_id', $lead?->gst_id)" placeholder="Enter GST Portal User ID" />
                        <x-input-error :messages="$errors->get('gst_id')" />
                    </div>
                    <div>
                        <x-input-label for="gst_password" value="GST Portal Password" />
                        <x-text-input id="gst_password" type="text" name="gst_password" :value="old('gst_password', $lead?->gst_password)" placeholder="Enter GST Portal Password" />
                        <x-input-error :messages="$errors->get('gst_password')" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-input-label for="firm_name" value="Firm Name" />
                    <x-text-input id="firm_name" type="text" name="firm_name" :value="old('firm_name', $lead?->firm_name)" placeholder="Enter firm name" />
                    <x-input-error :messages="$errors->get('firm_name')" />
                </div>
                <div>
                    <x-input-label for="business_activity" value="Business Activity" />
                    <select id="business_activity" name="business_activity" class="form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                        <option value="">Select Activity</option>
                        <option value="Manufacturing" @selected(old('business_activity', $lead?->business_activity) === 'Manufacturing')>Manufacturing</option>
                        <option value="Trading" @selected(old('business_activity', $lead?->business_activity) === 'Trading')>Trading</option>
                        <option value="Services" @selected(old('business_activity', $lead?->business_activity) === 'Services')>Services</option>
                    </select>
                    <x-input-error :messages="$errors->get('business_activity')" />
                </div>
                <div>
                    <x-input-label for="business_experience" value="Business Experience" />
                    <x-text-input id="business_experience" type="text" name="business_experience" :value="old('business_experience', $lead?->business_experience)" placeholder="e.g. 5 Years" />
                    <x-input-error :messages="$errors->get('business_experience')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-input-label for="no_of_manpower" value="Number of Manpower" />
                    <x-text-input id="no_of_manpower" type="text" name="no_of_manpower" :value="old('no_of_manpower', $lead?->no_of_manpower)" placeholder="e.g. 15 Employees" />
                    <x-input-error :messages="$errors->get('no_of_manpower')" />
                </div>
                <div>
                    <x-input-label for="business_location" value="Business Location" />
                    <x-text-input id="business_location" type="text" name="business_location" :value="old('business_location', $lead?->business_location)" placeholder="e.g. Surat, Gujarat" />
                    <x-input-error :messages="$errors->get('business_location')" />
                </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="area_of_premises" value="Area of Premises" />
                    <x-text-input id="area_of_premises" type="text" name="area_of_premises" :value="old('area_of_premises', $lead?->area_of_premises)" placeholder="e.g. 2000 sq ft" />
                    <x-input-error :messages="$errors->get('area_of_premises')" />
                </div>
                <div>
                    <x-input-label for="land_and_factory_building" value="Land and Factory Building Details" />
                    <x-text-input id="land_and_factory_building" type="text" name="land_and_factory_building" :value="old('land_and_factory_building', $lead?->land_and_factory_building)" placeholder="e.g. Owned / Rented 5000 sq ft RCC structure" />
                    <x-input-error :messages="$errors->get('land_and_factory_building')" />
                </div>
            </div>
            </div>

            <div>
                <x-input-label for="connectivity" value="Connectivity Details" />
                <x-text-input id="connectivity" type="text" name="connectivity" :value="old('connectivity', $lead?->connectivity)" placeholder="e.g. Main Highway Road, Near Station" />
                <x-input-error :messages="$errors->get('connectivity')" />
            </div>
            
            <div class="flex justify-between pt-4 border-t border-slate-200/80 dark:border-slate-800">
                <button type="button" onclick="switchTab('bank-tab', 'bank-panel')" class="inline-flex items-center justify-center h-10 px-5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                    Previous
                </button>
                <button type="button" onclick="switchTab('loan-tab', 'loan-panel')" class="inline-flex items-center justify-center h-10 px-5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-semibold text-sm transition focus:outline-none">
                    Next: Loan Details
                </button>
            </div>
        </div>

        <!-- 4. Loan Details Panel -->
        <div id="loan-panel" class="tab-panel space-y-6 hidden">
            <div>
                <x-input-label for="required_loan_amount" value="Required Loan Amount" />
                <x-text-input id="required_loan_amount" type="text" name="required_loan_amount" :value="old('required_loan_amount', $lead?->required_loan_amount)" placeholder="Enter required loan amount (in ₹)" />
                <x-input-error :messages="$errors->get('required_loan_amount')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800">
                <div>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4">1) Cash Credit (CC) Loan</h4>
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="cc_amount" value="CC Amount" />
                            <x-text-input id="cc_amount" type="text" name="cc_amount" :value="old('cc_amount', $lead?->cc_amount)" placeholder="Enter CC loan amount (in ₹)" />
                            <x-input-error :messages="$errors->get('cc_amount')" />
                        </div>
                        <div>
                            <x-input-label for="cc_details" value="CC Details" />
                            <textarea id="cc_details" name="cc_details" rows="3" class="w-full rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 text-sm px-3 py-2" placeholder="Describe CC requirements, stock & debtors details...">{{ old('cc_details', $lead?->cc_details) }}</textarea>
                            <x-input-error :messages="$errors->get('cc_details')" />
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4">2) Term Loan</h4>
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="term_loan_amount" value="Term Loan Amount" />
                            <x-text-input id="term_loan_amount" type="text" name="term_loan_amount" :value="old('term_loan_amount', $lead?->term_loan_amount)" placeholder="Enter term loan amount (in ₹)" />
                            <x-input-error :messages="$errors->get('term_loan_amount')" />
                        </div>
                        <div>
                            <x-input-label for="term_loan_machinery_details" value="Machinery / Term Details" />
                            <textarea id="term_loan_machinery_details" name="term_loan_machinery_details" rows="3" class="w-full rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 text-sm px-3 py-2" placeholder="List machinery specs, quotations, construction scope...">{{ old('term_loan_machinery_details', $lead?->term_loan_machinery_details) }}</textarea>
                            <x-input-error :messages="$errors->get('term_loan_machinery_details')" />
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2">Current Loan Details</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Provide details for up to 6 outstanding loans.</p>
                
                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            <tr>
                                <th class="p-3 border-b border-slate-200 dark:border-slate-800 w-12 text-center">SR</th>
                                <th class="p-3 border-b border-slate-200 dark:border-slate-800">Bank Name</th>
                                <th class="p-3 border-b border-slate-200 dark:border-slate-800">Loan Type</th>
                                <th class="p-3 border-b border-slate-200 dark:border-slate-800">Loan Amount</th>
                                <th class="p-3 border-b border-slate-200 dark:border-slate-800">Disburse Date</th>
                                <th class="p-3 border-b border-slate-200 dark:border-slate-800">EMI</th>
                                <th class="p-3 border-b border-slate-200 dark:border-slate-800">Outstanding</th>
                                <th class="p-3 border-b border-slate-200 dark:border-slate-800">Tenure</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @for ($i = 0; $i < 6; $i++)
                                @php
                                    $loan = $lead?->current_loans[$i] ?? null;
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10">
                                    <td class="p-3 text-center font-semibold text-slate-500">{{ $i + 1 }}</td>
                                    <td class="p-2">
                                        <input type="text" name="current_loans[{{ $i }}][bank_name]" value="{{ old("current_loans.{$i}.bank_name", $loan['bank_name'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="Bank Name">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="current_loans[{{ $i }}][loan_type]" value="{{ old("current_loans.{$i}.loan_type", $loan['loan_type'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="e.g. Business, Home">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="current_loans[{{ $i }}][loan_amount]" value="{{ old("current_loans.{$i}.loan_amount", $loan['loan_amount'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="Amount (₹)">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="current_loans[{{ $i }}][disburse_date]" value="{{ old("current_loans.{$i}.disburse_date", $loan['disburse_date'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="DD/MM/YYYY">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="current_loans[{{ $i }}][emi]" value="{{ old("current_loans.{$i}.emi", $loan['emi'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="EMI (₹)">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="current_loans[{{ $i }}][outstanding_amount]" value="{{ old("current_loans.{$i}.outstanding_amount", $loan['outstanding_amount'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="Outstanding (₹)">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="current_loans[{{ $i }}][tenure]" value="{{ old("current_loans.{$i}.tenure", $loan['tenure'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="Tenure (Months)">
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="flex justify-between pt-4 border-t border-slate-200/80 dark:border-slate-800">
                <button type="button" onclick="switchTab('business-tab', 'business-panel')" class="inline-flex items-center justify-center h-10 px-5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                    Previous
                </button>
                <button type="button" onclick="switchTab('documents-tab', 'documents-panel')" class="inline-flex items-center justify-center h-10 px-5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-semibold text-sm transition focus:outline-none">
                    Next: Documents
                </button>
            </div>
        </div>

        <!-- 5. Documents Panel -->
        @php
            $docTypesList = $documentTypes ?? \App\Models\DocumentType::where('status', 'active')->ordered()->get();
            $existingLeadDocs = $lead ? $lead->leadDocuments->groupBy('document_type_id') : collect();
        @endphp
        <div id="documents-panel" class="tab-panel space-y-6 hidden">
            <div class="border-b border-slate-200/80 dark:border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-4 bg-primary-600 rounded-full"></span>
                        Lead Documents Checklist & Upload
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Upload required KYC, financial, and project documents for this lead based on configured Document Master rules.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($docTypesList as $docType)
                    @php
                        $code = $docType->code;
                        $id = $docType->id;
                        $uploadedDocs = $existingLeadDocs->get($docType->id, collect());
                        $acceptAttr = implode(',', array_map(fn($e) => '.' . trim($e), $docType->allowed_file_types ?? ['pdf', 'jpg', 'jpeg', 'png']));
                    @endphp

                    <div class="doc-item-wrapper p-4 bg-slate-50/60 dark:bg-slate-800/20 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-3">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                            {{ $docType->name }}
                        </h4>

                        {{-- UPLOAD CONTROLS BASED ON CONFIGURATION --}}
                        {{-- 1. FRONT & BACK DOCUMENT --}}
                        @if ($docType->has_front_back)
                            @php
                                $frontDoc = $uploadedDocs->firstWhere('side', 'front');
                                $backDoc = $uploadedDocs->firstWhere('side', 'back');
                            @endphp
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <label class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg shadow-sm cursor-pointer transition hover:border-primary-500">
                                        <input type="file" name="documents[{{ $code }}][front]" accept="{{ $acceptAttr }}" class="hidden" onchange="handleFileSelect(this, '{{ $code }}-front-selected')">
                                        <span>Upload Front</span>
                                    </label>

                                    <label class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg shadow-sm cursor-pointer transition hover:border-primary-500">
                                        <input type="file" name="documents[{{ $code }}][back]" accept="{{ $acceptAttr }}" class="hidden" onchange="handleFileSelect(this, '{{ $code }}-back-selected')">
                                        <span>Upload Back</span>
                                    </label>
                                </div>

                                <div class="space-y-1 text-xs">
                                    @if ($frontDoc)
                                        <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-medium">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="font-semibold">Front:</span>
                                            <span class="truncate max-w-[150px]" title="{{ $frontDoc->original_name }}">{{ $frontDoc->original_name }}</span>
                                            <a href="{{ route('admin.leads.documents.download', [$lead, $frontDoc]) }}" target="_blank" class="text-primary-600 dark:text-primary-400 font-bold hover:underline ml-1">View</a>
                                        </div>
                                    @endif
                                    <div id="{{ $code }}-front-selected" class="hidden flex items-center gap-1 text-primary-600 dark:text-primary-400 font-medium">
                                        <span>Front selected:</span>
                                        <span class="file-name font-semibold truncate max-w-[180px]"></span>
                                    </div>

                                    @if ($backDoc)
                                        <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-medium">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="font-semibold">Back:</span>
                                            <span class="truncate max-w-[150px]" title="{{ $backDoc->original_name }}">{{ $backDoc->original_name }}</span>
                                            <a href="{{ route('admin.leads.documents.download', [$lead, $backDoc]) }}" target="_blank" class="text-primary-600 dark:text-primary-400 font-bold hover:underline ml-1">View</a>
                                        </div>
                                    @endif
                                    <div id="{{ $code }}-back-selected" class="hidden flex items-center gap-1 text-primary-600 dark:text-primary-400 font-medium">
                                        <span>Back selected:</span>
                                        <span class="file-name font-semibold truncate max-w-[180px]"></span>
                                    </div>
                                </div>

                                <x-input-error :messages="$errors->get('documents.' . $code . '.front')" />
                                <x-input-error :messages="$errors->get('documents.' . $code . '.back')" />
                            </div>

                        {{-- 2. MULTIPLE FILES DOCUMENT --}}
                        @elseif ($docType->allow_multiple)
                            <div class="space-y-2">
                                <div id="multiple-files-container-{{ $code }}" class="flex flex-wrap items-center gap-2">
                                    <label class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg shadow-sm cursor-pointer transition hover:border-primary-500">
                                        <input type="file" name="documents[{{ $code }}][files][]" accept="{{ $acceptAttr }}" class="hidden" data-row-id="row-initial-{{ $code }}" onchange="handleMultipleFileSelect(this)">
                                        <span>Upload File</span>
                                    </label>

                                    <button type="button" onclick="addMultipleFileInput('{{ $code }}', '{{ $acceptAttr }}')" class="inline-flex items-center gap-1 px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        + Add Another
                                    </button>
                                </div>

                                <div class="space-y-1 text-xs">
                                    @if ($uploadedDocs->isNotEmpty())
                                        @foreach ($uploadedDocs as $docItem)
                                            <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-medium">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span class="truncate max-w-[180px]" title="{{ $docItem->original_name }}">{{ $docItem->original_name }}</span>
                                                <a href="{{ route('admin.leads.documents.download', [$lead, $docItem]) }}" target="_blank" class="text-primary-600 dark:text-primary-400 font-bold hover:underline ml-1">View</a>
                                            </div>
                                        @endforeach
                                    @endif
                                    <div class="multiple-selected-list space-y-1"></div>
                                </div>

                                <x-input-error :messages="$errors->get('documents.' . $code)" />
                            </div>

                        {{-- 3. SINGLE FILE DOCUMENT --}}
                        @else
                            @php $singleUploaded = $uploadedDocs->first(); @endphp
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <label class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg shadow-sm cursor-pointer transition hover:border-primary-500">
                                        <input type="file" name="documents[{{ $code }}][file]" accept="{{ $acceptAttr }}" class="hidden" onchange="handleFileSelect(this, '{{ $code }}-single-selected')">
                                        <span>Upload File</span>
                                    </label>
                                </div>

                                <div class="space-y-1 text-xs">
                                    @if ($singleUploaded)
                                        <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-medium">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="truncate max-w-[180px]" title="{{ $singleUploaded->original_name }}">{{ $singleUploaded->original_name }}</span>
                                            <a href="{{ route('admin.leads.documents.download', [$lead, $singleUploaded]) }}" target="_blank" class="text-primary-600 dark:text-primary-400 font-bold hover:underline ml-1">View</a>
                                        </div>
                                    @endif
                                    <div id="{{ $code }}-single-selected" class="hidden flex items-center gap-1 text-primary-600 dark:text-primary-400 font-medium">
                                        <span>Selected:</span>
                                        <span class="file-name font-semibold truncate max-w-[180px]"></span>
                                    </div>
                                </div>

                                <x-input-error :messages="$errors->get('documents.' . $code)" />
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex justify-start pt-4 border-t border-slate-200/80 dark:border-slate-800">
                <button type="button" onclick="switchTab('loan-tab', 'loan-panel')" class="inline-flex items-center justify-center h-10 px-5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                    Previous: Loan Details
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let itrCardIndex = {{ count($itrs) }};

function addItrCard() {
    const container = document.getElementById('itr-cards-container');
    const card = document.createElement('div');
    card.className = 'itr-card p-5 bg-slate-50/50 dark:bg-slate-800/20 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-4 relative';
    card.innerHTML = `
        <div class="flex items-center justify-between pb-3 border-b border-slate-200/60 dark:border-slate-800/60">
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                ITR Profile #<span class="itr-sr"></span>
            </span>
            <button type="button" onclick="removeItrCard(this)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg border border-red-200/60 dark:border-red-900/40 transition focus:outline-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Remove</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
            <div>
                <label class="block font-medium text-sm text-slate-700 dark:text-slate-300 mb-1">Assessment Year (A.Y.)</label>
                <select name="itr_details[\${itrCardIndex}][assessment_year]" class="form-select select2 w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                    <option value="">Select Assessment Year</option>
                    @foreach ($assessmentYears as $ay)
                        <option value="{{ $ay->name }}">{{ $ay->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-medium text-sm text-slate-700 dark:text-slate-300 mb-1">ITR Audited</label>
                <select name="itr_details[\${itrCardIndex}][itr_audited]" onchange="toggleItrFiles(this)" class="itr-audited-select form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                    <option value="">Select Option</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </div>
        </div>

        <div class="itr-files-wrapper pt-3 border-t border-slate-200/40 dark:border-slate-800/40">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="audit-report-field hidden">
                    <label class="block font-medium text-sm text-slate-700 dark:text-slate-300 mb-1">Audit Report</label>
                    <input type="file" name="itr_details[\${itrCardIndex}][audit_report]" class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-950/40 dark:file:text-primary-400 hover:file:bg-primary-100 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 p-1" />
                </div>
                <div class="itr-file-field hidden">
                    <label class="block font-medium text-sm text-slate-700 dark:text-slate-300 mb-1">ITR</label>
                    <input type="file" name="itr_details[\${itrCardIndex}][itr_file]" class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-950/40 dark:file:text-primary-400 hover:file:bg-primary-100 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 p-1" />
                </div>
                <div class="computation-field hidden">
                    <label class="block font-medium text-sm text-slate-700 dark:text-slate-300 mb-1">Computation</label>
                    <input type="file" name="itr_details[\${itrCardIndex}][computation]" class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-950/40 dark:file:text-primary-400 hover:file:bg-primary-100 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 p-1" />
                </div>
                <div class="itr-form-field hidden">
                    <label class="block font-medium text-sm text-slate-700 dark:text-slate-300 mb-1">ITR Form</label>
                    <input type="file" name="itr_details[\${itrCardIndex}][itr_form]" class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-950/40 dark:file:text-primary-400 hover:file:bg-primary-100 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 p-1" />
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
        auditField.classList.remove('hidden');
        itrField.classList.remove('hidden');
        compField.classList.remove('hidden');
        formField.classList.remove('hidden');
    } else if (val === 'No') {
        auditField.classList.add('hidden');
        itrField.classList.remove('hidden');
        compField.classList.remove('hidden');
        formField.classList.remove('hidden');
    } else {
        auditField.classList.add('hidden');
        itrField.classList.add('hidden');
        compField.classList.add('hidden');
        formField.classList.add('hidden');
    }
}

function addItrRow() { addItrCard(); }
function removeItrRow(btn) { removeItrCard(btn); }

let bankRowIndex = {{ count($banks) }};

function addBankRow() {
    const container = document.getElementById('bank-rows-container');
    const tr = document.createElement('tr');
    tr.className = 'bank-row hover:bg-slate-50/50 dark:hover:bg-slate-800/10';
    tr.innerHTML = `
        <td class="p-4 text-center font-semibold text-slate-500 bank-sr"></td>
        <td class="p-2">
            <input type="text" name="bank_details[\${bankRowIndex}][bank_name]" list="master-banks-list" value="" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-3 py-2 text-slate-900 dark:text-slate-100" placeholder="Select or type Bank name">
        </td>
        <td class="p-2">
            <input type="text" name="bank_details[\${bankRowIndex}][account_number]" value="" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="A/C Number">
        </td>
        <td class="p-2">
            <input type="text" name="bank_details[\${bankRowIndex}][account_type]" value="" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="e.g. Savings, Current">
        </td>
        <td class="p-2">
            <input type="text" name="bank_details[\${bankRowIndex}][ifsc_code]" value="" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="IFSC Code">
        </td>
        <td class="p-2 text-center">
            <button type="button" onclick="removeBankRow(this)" class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-md transition focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
        panel.classList.add('hidden');
    });
    // Show target panel
    document.getElementById(panelId).classList.remove('hidden');

    // Reset tab buttons style
    document.querySelectorAll('.tab-btn').forEach(tab => {
        // Set inactive styling classes
        tab.className = "tab-btn flex items-center gap-2 px-4 py-2 text-slate-500 hover:text-slate-750 dark:hover:text-slate-300 font-medium text-sm rounded-xl transition focus:outline-none";
        
        // Reset SVG icon color class
        const svg = tab.querySelector('svg');
        if (svg) {
            svg.setAttribute('class', 'w-4 h-4 text-slate-400');
        }
    });

    // Set target tab active styling classes
    const targetTab = document.getElementById(tabId);
    targetTab.className = "tab-btn flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 font-semibold text-sm rounded-xl shadow-sm border border-primary-600 dark:border-primary-500 focus:outline-none transition";
    
    // Set active SVG icon color class
    const activeSvg = targetTab.querySelector('svg');
    if (activeSvg) {
        activeSvg.setAttribute('class', 'w-4 h-4 text-primary-600 dark:text-primary-400');
    }
}

function checkBankLoanTabVisibility() {
    const bankSelect = document.getElementById('bank_id');
    const loanProductSelect = document.getElementById('loan_product_id');
    const bankLoanTab = document.getElementById('bank-loan-tab');
    
    if (!bankSelect || !loanProductSelect || !bankLoanTab) return;
    
    const bankVal = bankSelect.value;
    const productVal = loanProductSelect.value;
    
    const bankText = bankSelect.options[bankSelect.selectedIndex]?.text?.toLowerCase() || '';
    const productText = loanProductSelect.options[loanProductSelect.selectedIndex]?.text?.toLowerCase() || '';
    
    const hasExistingData = {{ !empty($lead?->bank_loan_details) ? 'true' : 'false' }};
    
    const isSbi = bankText.includes('sbi') || bankText.includes('state bank') || bankVal !== '';
    const isBusinessLoan = productText.includes('business loan') || productText.includes('business') || productVal !== '';
    
    if (hasExistingData || (bankVal !== '' && isBusinessLoan)) {
        bankLoanTab.classList.remove('hidden');
    } else {
        bankLoanTab.classList.add('hidden');
        const bankLoanPanel = document.getElementById('bank-loan-panel');
        if (bankLoanPanel && !bankLoanPanel.classList.contains('hidden')) {
            switchTab('kyc-tab', 'kyc-panel');
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const bankSelect = document.getElementById('bank_id');
    const loanProductSelect = document.getElementById('loan_product_id');
    
    if (bankSelect) bankSelect.addEventListener('change', checkBankLoanTabVisibility);
    if (loanProductSelect) loanProductSelect.addEventListener('change', checkBankLoanTabVisibility);
    
    if (window.jQuery) {
        window.jQuery('#bank_id, #loan_product_id').on('change', checkBankLoanTabVisibility);
    }
    
    checkBankLoanTabVisibility();

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
});

function handleFileSelect(input, targetId) {
    const el = document.getElementById(targetId);
    if (!el) return;
    if (input.files && input.files[0]) {
        const fileNameSpan = el.querySelector('.file-name');
        if (fileNameSpan) fileNameSpan.textContent = input.files[0].name;
        el.classList.remove('hidden');
    } else {
        el.classList.add('hidden');
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
            existingRow.className = 'flex items-center gap-1 text-primary-600 dark:text-primary-400 font-medium';
            existingRow.innerHTML = `<span>Selected:</span> <span class="font-semibold truncate max-w-[180px]">${input.files[0].name}</span>`;
            selectedList.appendChild(existingRow);
        } else {
            const nameSpan = existingRow.querySelector('span.font-semibold');
            if (nameSpan) nameSpan.textContent = input.files[0].name;
        }
    }
}

function addMultipleFileInput(code, acceptAttr) {
    const container = document.getElementById('multiple-files-container-' + code);
    if (!container) return;
    const addBtn = container.querySelector('button');
    
    const label = document.createElement('label');
    label.className = 'inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg shadow-sm cursor-pointer transition hover:border-primary-500';
    
    const rowId = 'row-' + Math.random().toString(36).substr(2, 9);
    const input = document.createElement('input');
    input.type = 'file';
    input.name = `documents[${code}][files][]`;
    input.accept = acceptAttr;
    input.className = 'hidden';
    input.setAttribute('data-row-id', rowId);
    input.onchange = function() { handleMultipleFileSelect(this); };

    const span = document.createElement('span');
    span.textContent = 'Upload File';

    label.appendChild(input);
    label.appendChild(span);
    container.insertBefore(label, addBtn);
    input.click();
}
</script>
