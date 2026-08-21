{{-- Expects: $agent (nullable, for edit) --}}
@php $agent = $agent ?? null; @endphp

<div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm p-6 sm:p-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <x-input-label for="first_name" value="First Name" :required="true" />
            <x-text-input id="first_name" type="text" name="first_name" :value="old('first_name', $agent?->first_name)" required autofocus />
            <x-input-error :messages="$errors->get('first_name')" />
        </div>
        <div>
            <x-input-label for="last_name" value="Last Name" :required="true" />
            <x-text-input id="last_name" type="text" name="last_name" :value="old('last_name', $agent?->last_name)" required />
            <x-input-error :messages="$errors->get('last_name')" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div>
            <x-input-label for="email" value="Email" :required="true" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $agent?->email)" required />
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <div>
            <x-input-label for="mobile_number" value="Mobile Number" :required="true" />
            <x-text-input id="mobile_number" type="tel" name="mobile_number" :value="old('mobile_number', $agent?->mobile_number)" required />
            <x-input-error :messages="$errors->get('mobile_number')" />
        </div>
        <div>
            <x-input-label for="alternate_mobile_number" value="Alternate Mobile Number" />
            <x-text-input id="alternate_mobile_number" type="tel" name="alternate_mobile_number" :value="old('alternate_mobile_number', $agent?->alternate_mobile_number)" />
            <x-input-error :messages="$errors->get('alternate_mobile_number')" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div>
            <x-input-label for="city_id" value="City" />
            <select id="city_id" name="city_id" class="form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3 select2">
                <option value="">Select City</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}" @selected(old('city_id', $agent?->city_id) == $city->id)>{{ $city->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('city_id')" />
        </div>
        <div>
            <x-input-label for="status" value="Status" :required="true" />
            <select id="status" name="status" class="form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                <option value="active" @selected(old('status', $agent?->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $agent?->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
            <x-input-error :messages="$errors->get('status')" />
        </div>
    </div>

    <div class="mt-6">
        <x-input-label for="address" value="Address" />
        <textarea id="address" name="address" rows="3" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 text-sm px-3 py-2">{{ old('address', $agent?->address) }}</textarea>
        <x-input-error :messages="$errors->get('address')" />
    </div>

    <div class="mt-6 flex flex-col sm:flex-row items-center gap-6 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800/60">
        <input type="hidden" name="remove_profile_photo" value="0">
        <div class="relative group w-20 h-20 rounded-full overflow-hidden border-4 border-white dark:border-slate-800 shadow-md bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0">
            @if ($agent?->profilePhotoUrl())
                <img src="{{ $agent->profilePhotoUrl() }}" alt="Profile preview" class="w-full h-full object-cover profile-preview-img">
            @else
                <div class="text-slate-400 dark:text-slate-500 profile-placeholder-icon">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </div>
                <img class="w-full h-full object-cover profile-preview-img hidden">
            @endif
        </div>
        <div class="flex-1 space-y-2">
            <x-input-label for="profile_photo" value="Profile Photo" class="font-semibold text-slate-900 dark:text-white" />
            <div class="flex items-center gap-3">
                <input id="profile_photo" type="file" name="profile_photo" class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-950/20 dark:file:text-primary-400 hover:file:bg-primary-100 dark:hover:file:bg-primary-900/30 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900" accept="image/*" onchange="window.previewImage(this)">
                
                <button type="button" id="remove_photo_btn" onclick="window.clearImage(this)" class="px-4 py-2 border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/10 text-red-600 hover:bg-red-100 dark:hover:bg-red-950/20 rounded-xl text-sm font-semibold transition shrink-0 {{ ($agent?->profilePhotoUrl()) ? '' : 'hidden' }}">
                    Remove
                </button>
            </div>
            <p class="text-xs text-slate-400">PNG, JPG or WEBP. Max 2MB.</p>
            <x-input-error :messages="$errors->get('profile_photo')" />
        </div>
    </div>

    <!-- Section: Qualifications -->
    <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6">Qualifications</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="qualification" value="Qualification" />
                <x-text-input id="qualification" type="text" name="qualification" :value="old('qualification', $agent?->qualification)" placeholder="e.g. Bachelor of Science" />
                <x-input-error :messages="$errors->get('qualification')" />
            </div>
            
            <div>
                <x-input-label for="resume" value="Resume Upload (PDF/Word/Image)" />
                <input type="hidden" name="remove_resume" value="0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-center shrink-0 shadow-sm">
                        @if ($agent?->resume_path)
                            @if (preg_match('/\.(pdf|doc|docx)$/i', $agent->resume_path))
                                <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $agent->resumeUrl() }}')" class="download-link flex items-center justify-center w-full h-full text-slate-500 bg-slate-50 text-xs font-semibold">DOC</a>
                                <img class="w-full h-full object-cover resume-preview-img hidden cursor-pointer" onclick="window.previewFilePopup(this.src)">
                            @else
                                <img src="{{ $agent->resumeUrl() }}" alt="Resume preview" class="w-full h-full object-cover resume-preview-img cursor-pointer" onclick="window.previewFilePopup(this.src)">
                            @endif
                        @else
                            <div class="text-slate-400 resume-placeholder-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            </div>
                            <img class="w-full h-full object-cover resume-preview-img hidden cursor-pointer" onclick="window.previewFilePopup(this.src)">
                        @endif
                    </div>
                    <input id="resume" type="file" name="resume" class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-950/20 dark:file:text-primary-400 hover:file:bg-primary-100 dark:hover:file:bg-primary-900/30 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900" accept="image/*,.pdf,.doc,.docx" onchange="window.previewImage(this, '#remove_resume_btn', '.resume-preview-img', '.resume-placeholder-icon')">
                    @if ($agent?->resume_path)
                        <a href="{{ $agent->resumeUrl() }}" target="_blank" class="download-link inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:bg-slate-50 shrink-0" title="Download Resume">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        </a>
                    @endif
                    <button type="button" id="remove_resume_btn" onclick="window.clearFormFile(this, 'remove_resume', '.resume-preview-img', '.resume-placeholder-icon')" class="px-4 py-2 border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/10 text-red-600 hover:bg-red-100 dark:hover:bg-red-950/20 rounded-xl text-sm font-semibold transition shrink-0 {{ ($agent?->resume_path) ? '' : 'hidden' }}">
                        Remove
                    </button>
                </div>
                <x-input-error :messages="$errors->get('resume')" />
            </div>
        </div>
    </div>

    <!-- Section: KYC Documents -->
    <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6">KYC Documents</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Aadhaar -->
            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/20 space-y-4">
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">Aadhaar Card Details</h4>
                <div>
                    <x-input-label for="aadhaar_card_number" value="Aadhaar Card Number" />
                    <x-text-input id="aadhaar_card_number" type="text" name="aadhaar_card_number" :value="old('aadhaar_card_number', $agent?->aadhaar_card_number)" placeholder="e.g. 1234 5678 9012" />
                    <x-input-error :messages="$errors->get('aadhaar_card_number')" />
                </div>
                <div class="flex items-center gap-4">
                    <input type="hidden" name="remove_aadhaar_photo" value="0">
                    <div class="w-14 h-14 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-center shrink-0 shadow-sm">
                        @if ($agent?->aadhaar_photo_path)
                            @if (str_ends_with(strtolower($agent->aadhaar_photo_path), '.pdf'))
                                <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $agent->aadhaarPhotoUrl() }}')" class="download-link flex items-center justify-center w-full h-full text-slate-500 bg-slate-50 text-xs font-semibold">PDF</a>
                                <img class="w-full h-full object-cover aadhaar-preview-img hidden cursor-pointer" onclick="window.previewFilePopup(this.src)">
                            @else
                                <img src="{{ $agent->aadhaarPhotoUrl() }}" alt="Aadhaar preview" class="w-full h-full object-cover aadhaar-preview-img cursor-pointer" onclick="window.previewFilePopup(this.src)">
                            @endif
                        @else
                            <div class="text-slate-400 aadhaar-placeholder-icon">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            </div>
                            <img class="w-full h-full object-cover aadhaar-preview-img hidden cursor-pointer" onclick="window.previewFilePopup(this.src)">
                        @endif
                    </div>
                    <div class="flex-1 space-y-1">
                        <input id="aadhaar_photo" type="file" name="aadhaar_photo" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" accept="image/*,.pdf" onchange="window.previewImage(this, '#remove_aadhaar_btn', '.aadhaar-preview-img', '.aadhaar-placeholder-icon')">
                        @if ($agent?->aadhaar_photo_path)
                            <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $agent->aadhaarPhotoUrl() }}')" class="download-link text-xs text-primary-600 font-semibold hover:underline block">Download Document</a>
                        @endif
                        <button type="button" id="remove_aadhaar_btn" onclick="window.clearFormFile(this, 'remove_aadhaar_photo', '.aadhaar-preview-img', '.aadhaar-placeholder-icon')" class="text-xs text-red-600 font-semibold hover:underline mt-1 {{ ($agent?->aadhaar_photo_path) ? '' : 'hidden' }}">Remove Photo/PDF</button>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('aadhaar_photo')" />
            </div>

            <!-- PAN -->
            <div class="p-5 rounded-2xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/20 space-y-4">
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">PAN Card Details</h4>
                <div>
                    <x-input-label for="pan_card_number" value="PAN Card Number" />
                    <x-text-input id="pan_card_number" type="text" name="pan_card_number" :value="old('pan_card_number', $agent?->pan_card_number)" placeholder="e.g. ABCDE1234F" style="text-transform: uppercase;" />
                    <x-input-error :messages="$errors->get('pan_card_number')" />
                </div>
                <div class="flex items-center gap-4">
                    <input type="hidden" name="remove_pan_photo" value="0">
                    <div class="w-14 h-14 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-center shrink-0 shadow-sm">
                        @if ($agent?->pan_photo_path)
                            @if (str_ends_with(strtolower($agent->pan_photo_path), '.pdf'))
                                <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $agent->panPhotoUrl() }}')" class="download-link flex items-center justify-center w-full h-full text-slate-500 bg-slate-50 text-xs font-semibold">PDF</a>
                                <img class="w-full h-full object-cover pan-preview-img hidden cursor-pointer" onclick="window.previewFilePopup(this.src)">
                            @else
                                <img src="{{ $agent->panPhotoUrl() }}" alt="PAN preview" class="w-full h-full object-cover pan-preview-img cursor-pointer" onclick="window.previewFilePopup(this.src)">
                            @endif
                        @else
                            <div class="text-slate-400 pan-placeholder-icon">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            </div>
                            <img class="w-full h-full object-cover pan-preview-img hidden cursor-pointer" onclick="window.previewFilePopup(this.src)">
                        @endif
                    </div>
                    <div class="flex-1 space-y-1">
                        <input id="pan_photo" type="file" name="pan_photo" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" accept="image/*,.pdf" onchange="window.previewImage(this, '#remove_pan_btn', '.pan-preview-img', '.pan-placeholder-icon')">
                        @if ($agent?->pan_photo_path)
                            <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $agent->panPhotoUrl() }}')" class="download-link text-xs text-primary-600 font-semibold hover:underline block">Download Document</a>
                        @endif
                        <button type="button" id="remove_pan_btn" onclick="window.clearFormFile(this, 'remove_pan_photo', '.pan-preview-img', '.pan-placeholder-icon')" class="text-xs text-red-600 font-semibold hover:underline mt-1 {{ ($agent?->pan_photo_path) ? '' : 'hidden' }}">Remove Photo/PDF</button>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('pan_photo')" />
            </div>
        </div>
    </div>

    <!-- Section: Bank Details -->
    <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6">Bank Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="bank_name" value="Bank Name" />
                <x-text-input id="bank_name" type="text" name="bank_name" :value="old('bank_name', $agent?->bank_name)" placeholder="e.g. HDFC Bank" />
                <x-input-error :messages="$errors->get('bank_name')" />
            </div>
            <div>
                <x-input-label for="bank_account_holder_name" value="Account Holder Name" />
                <x-text-input id="bank_account_holder_name" type="text" name="bank_account_holder_name" :value="old('bank_account_holder_name', $agent?->bank_account_holder_name)" placeholder="e.g. John Doe" />
                <x-input-error :messages="$errors->get('bank_account_holder_name')" />
            </div>
            <div>
                <x-input-label for="bank_account_number" value="Bank Account Number" />
                <x-text-input id="bank_account_number" type="text" name="bank_account_number" :value="old('bank_account_number', $agent?->bank_account_number)" placeholder="e.g. 50100012345678" />
                <x-input-error :messages="$errors->get('bank_account_number')" />
            </div>
            <div>
                <x-input-label for="bank_ifsc_code" value="IFSC Code" />
                <x-text-input id="bank_ifsc_code" type="text" name="bank_ifsc_code" :value="old('bank_ifsc_code', $agent?->bank_ifsc_code)" placeholder="e.g. HDFC0000123" style="text-transform: uppercase;" />
                <x-input-error :messages="$errors->get('bank_ifsc_code')" />
            </div>
            <div class="col-span-1 md:col-span-2">
                <x-input-label for="bank_cheque_photo" value="Cancelled Cheque Photo / PDF" />
                <input type="hidden" name="remove_bank_cheque_photo" value="0">
                <div class="flex items-center gap-4 mt-2">
                    <div class="w-14 h-14 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-center shrink-0 shadow-sm">
                        @if ($agent?->bank_cheque_photo_path)
                            @if (str_ends_with(strtolower($agent->bank_cheque_photo_path), '.pdf'))
                                <div class="text-slate-400 bank-cheque-placeholder-icon hidden">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                </div>
                                <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $agent->bankChequePhotoUrl() }}')" class="download-link flex items-center justify-center w-full h-full text-slate-500 bg-slate-50 text-xs font-semibold">PDF</a>
                                <img class="w-full h-full object-cover bank-cheque-preview-img hidden cursor-pointer" onclick="window.previewFilePopup(this.src)">
                            @else
                                <img src="{{ $agent->bankChequePhotoUrl() }}" alt="Cheque preview" class="w-full h-full object-cover bank-cheque-preview-img cursor-pointer" onclick="window.previewFilePopup(this.src)">
                                <div class="text-slate-400 bank-cheque-placeholder-icon hidden">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                </div>
                            @endif
                        @else
                            <div class="text-slate-400 bank-cheque-placeholder-icon">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            </div>
                            <img class="w-full h-full object-cover bank-cheque-preview-img hidden cursor-pointer" onclick="window.previewFilePopup(this.src)">
                        @endif
                    </div>
                    <div class="flex-1 space-y-1">
                        <input id="bank_cheque_photo" type="file" name="bank_cheque_photo" class="block w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" accept="image/*,.pdf" onchange="window.previewImage(this, '#remove_cheque_btn', '.bank-cheque-preview-img', '.bank-cheque-placeholder-icon')">
                        @if ($agent?->bank_cheque_photo_path)
                            <a href="javascript:void(0)" onclick="window.previewFilePopup('{{ $agent->bankChequePhotoUrl() }}')" class="download-link text-xs text-primary-600 font-semibold hover:underline block">Download Document</a>
                        @endif
                        <button type="button" id="remove_cheque_btn" onclick="window.clearFormFile(this, 'remove_bank_cheque_photo', '.bank-cheque-preview-img', '.bank-cheque-placeholder-icon')" class="text-xs text-red-600 font-semibold hover:underline mt-1 {{ ($agent?->bank_cheque_photo_path) ? '' : 'hidden' }}">Remove Attachment</button>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('bank_cheque_photo')" />
            </div>
        </div>
    </div>
</div>
