{{-- Expects: $lead (nullable, for edit), $cities, $agents --}}
@php $lead = $lead ?? null; @endphp

<div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
    
    <!-- Tab Navigation Header -->
    <div class="bg-slate-50/50 dark:bg-slate-950/25 border-b border-slate-200/80 dark:border-slate-800 px-6 py-4 flex flex-wrap gap-2">
        <button type="button" onclick="switchTab('kyc-tab', 'kyc-panel')" id="kyc-tab" class="tab-btn flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 font-semibold text-sm rounded-xl shadow-sm border border-slate-200/50 dark:border-slate-700 focus:outline-none transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>KYC Details</span>
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
    </div>

    <!-- Active Panel Content -->
    <div class="p-6 sm:p-8">
        
        <!-- 1. KYC Details Panel -->
        <div id="kyc-panel" class="tab-panel space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="name" value="Customer Name" :required="true" />
                    <x-text-input id="name" type="text" name="name" :value="old('name', $lead?->name)" required autofocus />
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
                    <x-text-input id="education" type="text" name="education" :value="old('education', $lead?->education)" />
                    <x-input-error :messages="$errors->get('education')" />
                </div>
                <div>
                    <x-input-label for="mother_name" value="Mother Name" />
                    <x-text-input id="mother_name" type="text" name="mother_name" :value="old('mother_name', $lead?->mother_name)" />
                    <x-input-error :messages="$errors->get('mother_name')" />
                </div>
            </div>

            <div>
                <x-input-label for="address" value="Address" />
                <textarea id="address" name="address" rows="3" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 text-sm px-3 py-2" placeholder="Customer full address...">{{ old('address', $lead?->address) }}</textarea>
                <x-input-error :messages="$errors->get('address')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-input-label for="aadhar_card" value="Aadhaar Card" />
                    <x-text-input id="aadhar_card" type="text" name="aadhar_card" :value="old('aadhar_card', $lead?->aadhar_card)" />
                    <x-input-error :messages="$errors->get('aadhar_card')" />
                </div>
                <div>
                    <x-input-label for="pan_card" value="PAN Card" />
                    <x-text-input id="pan_card" type="text" name="pan_card" :value="old('pan_card', $lead?->pan_card)" />
                    <x-input-error :messages="$errors->get('pan_card')" />
                </div>
                <div>
                    <x-input-label for="udyam_registration" value="Udyam Registration" />
                    <x-text-input id="udyam_registration" type="text" name="udyam_registration" :value="old('udyam_registration', $lead?->udyam_registration)" />
                    <x-input-error :messages="$errors->get('udyam_registration')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-input-label for="mobile_number" value="Mobile Number" :required="true" />
                    <x-text-input id="mobile_number" type="tel" name="mobile_number" :value="old('mobile_number', $lead?->mobile_number)" required />
                    <x-input-error :messages="$errors->get('mobile_number')" />
                </div>
                <div>
                    <x-input-label for="alternate_mobile_number" value="Alternate Mobile Number" />
                    <x-text-input id="alternate_mobile_number" type="tel" name="alternate_mobile_number" :value="old('alternate_mobile_number', $lead?->alternate_mobile_number)" />
                    <x-input-error :messages="$errors->get('alternate_mobile_number')" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" type="email" name="email" :value="old('email', $lead?->email)" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>
            </div>

            <div class="p-5 bg-slate-50/50 dark:bg-slate-800/20 rounded-2xl border border-slate-100 dark:border-slate-800">
                <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-3 bg-blue-600 rounded-full"></span> ITR Details
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <x-input-label for="itr_id" value="ITR ID" />
                        <x-text-input id="itr_id" type="text" name="itr_id" :value="old('itr_id', $lead?->itr_id)" />
                        <x-input-error :messages="$errors->get('itr_id')" />
                    </div>
                    <div>
                        <x-input-label for="itr_password" value="ITR Password" />
                        <x-text-input id="itr_password" type="text" name="itr_password" :value="old('itr_password', $lead?->itr_password)" />
                        <x-input-error :messages="$errors->get('itr_password')" />
                    </div>
                    <div>
                        <x-input-label for="itr_audited" value="ITR Audited" />
                        <select id="itr_audited" name="itr_audited" class="form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                            <option value="">Select Option</option>
                            <option value="Yes" @selected(old('itr_audited', $lead?->itr_audited) === 'Yes')>Yes</option>
                            <option value="No" @selected(old('itr_audited', $lead?->itr_audited) === 'No')>No</option>
                        </select>
                        <x-input-error :messages="$errors->get('itr_audited')" />
                    </div>
                </div>
                
                <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800/80 space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 dark:text-slate-400">Assessment Years (A.Y.)</span>
                    <div class="flex items-center gap-6 mt-2">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="hidden" name="itr_ay_2026_27" value="0">
                            <input type="checkbox" name="itr_ay_2026_27" value="1" @checked(old('itr_ay_2026_27', $lead?->itr_ay_2026_27)) class="rounded border-slate-300 dark:border-slate-700 text-primary-600 focus:ring-primary-500">
                            A.Y. 2026-27
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="hidden" name="itr_ay_2025_26" value="0">
                            <input type="checkbox" name="itr_ay_2025_26" value="1" @checked(old('itr_ay_2025_26', $lead?->itr_ay_2025_26)) class="rounded border-slate-300 dark:border-slate-700 text-primary-600 focus:ring-primary-500">
                            A.Y. 2025-26
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="hidden" name="itr_ay_2024_25" value="0">
                            <input type="checkbox" name="itr_ay_2024_25" value="1" @checked(old('itr_ay_2024_25', $lead?->itr_ay_2024_25)) class="rounded border-slate-300 dark:border-slate-700 text-primary-600 focus:ring-primary-500">
                            A.Y. 2024-25
                        </label>
                    </div>
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
                        <option value="new" @selected(old('status', $lead?->status ?? 'new') === 'new')>New</option>
                        <option value="contacted" @selected(old('status', $lead?->status) === 'contacted')>Contacted</option>
                        <option value="in_progress" @selected(old('status', $lead?->status) === 'in_progress')>In Progress</option>
                        <option value="converted" @selected(old('status', $lead?->status) === 'converted')>Converted</option>
                        <option value="lost" @selected(old('status', $lead?->status) === 'lost')>Lost</option>
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

        <!-- 2. Bank Details Panel -->
        <div id="bank-panel" class="tab-panel space-y-6 hidden">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2">Current Bank Details</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Provide details for up to 4 bank accounts.</p>
                
                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            <tr>
                                <th class="p-4 border-b border-slate-200 dark:border-slate-800 w-12 text-center">SR</th>
                                <th class="p-4 border-b border-slate-200 dark:border-slate-800">Bank Name</th>
                                <th class="p-4 border-b border-slate-200 dark:border-slate-800">A/C Number</th>
                                <th class="p-4 border-b border-slate-200 dark:border-slate-800">A/C Type</th>
                                <th class="p-4 border-b border-slate-200 dark:border-slate-800">IFSC Code</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @for ($i = 0; $i < 4; $i++)
                                @php
                                    $bank = $lead?->bank_details[$i] ?? null;
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10">
                                    <td class="p-4 text-center font-semibold text-slate-500">{{ $i + 1 }}</td>
                                    <td class="p-2">
                                        <input type="text" name="bank_details[{{ $i }}][bank_name]" value="{{ old("bank_details.{$i}.bank_name", $bank['bank_name'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="Bank name">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="bank_details[{{ $i }}][account_number]" value="{{ old("bank_details.{$i}.account_number", $bank['account_number'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="A/C Number">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="bank_details[{{ $i }}][account_type]" value="{{ old("bank_details.{$i}.account_type", $bank['account_type'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="e.g. Savings, Current">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="bank_details[{{ $i }}][ifsc_code]" value="{{ old("bank_details.{$i}.ifsc_code", $bank['ifsc_code'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="IFSC Code">
                                    </td>
                                </tr>
                            @endfor
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
                    <x-text-input id="business_name" type="text" name="business_name" :value="old('business_name', $lead?->business_name)" />
                    <x-input-error :messages="$errors->get('business_name')" />
                </div>
                <div>
                    <x-input-label for="constitution_of_business" value="Constitution of Business" />
                    <x-text-input id="constitution_of_business" type="text" name="constitution_of_business" :value="old('constitution_of_business', $lead?->constitution_of_business)" placeholder="e.g. Proprietorship, Partnership, Pvt Ltd" />
                    <x-input-error :messages="$errors->get('constitution_of_business')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="introduction" value="Introduction / Business Activity Details" />
                    <textarea id="introduction" name="introduction" rows="3" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 text-sm px-3 py-2" placeholder="Brief introduction of the business...">{{ old('introduction', $lead?->introduction) }}</textarea>
                    <x-input-error :messages="$errors->get('introduction')" />
                </div>
                <div>
                    <x-input-label for="business_address" value="Business Address" />
                    <textarea id="business_address" name="business_address" rows="3" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 text-sm px-3 py-2" placeholder="Business address details...">{{ old('business_address', $lead?->business_address) }}</textarea>
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
                        <x-text-input id="gst_number" type="text" name="gst_number" :value="old('gst_number', $lead?->gst_number)" />
                        <x-input-error :messages="$errors->get('gst_number')" />
                    </div>
                    <div>
                        <x-input-label for="gst_id" value="GST Portal ID" />
                        <x-text-input id="gst_id" type="text" name="gst_id" :value="old('gst_id', $lead?->gst_id)" />
                        <x-input-error :messages="$errors->get('gst_id')" />
                    </div>
                    <div>
                        <x-input-label for="gst_password" value="GST Portal Password" />
                        <x-text-input id="gst_password" type="text" name="gst_password" :value="old('gst_password', $lead?->gst_password)" />
                        <x-input-error :messages="$errors->get('gst_password')" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-input-label for="firm_name" value="Firm Name" />
                    <x-text-input id="firm_name" type="text" name="firm_name" :value="old('firm_name', $lead?->firm_name)" />
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
                    <x-text-input id="no_of_manpower" type="text" name="no_of_manpower" :value="old('no_of_manpower', $lead?->no_of_manpower)" />
                    <x-input-error :messages="$errors->get('no_of_manpower')" />
                </div>
                <div>
                    <x-input-label for="business_location" value="Business Location" />
                    <x-text-input id="business_location" type="text" name="business_location" :value="old('business_location', $lead?->business_location)" />
                    <x-input-error :messages="$errors->get('business_location')" />
                </div>
                <div>
                    <x-input-label for="area_of_premises" value="Area of Premises" />
                    <x-text-input id="area_of_premises" type="text" name="area_of_premises" :value="old('area_of_premises', $lead?->area_of_premises)" placeholder="e.g. 2000 sq ft" />
                    <x-input-error :messages="$errors->get('area_of_premises')" />
                </div>
            </div>

            <div>
                <x-input-label for="connectivity" value="Connectivity Details" />
                <x-text-input id="connectivity" type="text" name="connectivity" :value="old('connectivity', $lead?->connectivity)" placeholder="e.g. Near highway, Railway station" />
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
                <x-text-input id="required_loan_amount" type="text" name="required_loan_amount" :value="old('required_loan_amount', $lead?->required_loan_amount)" placeholder="Total Required Loan Amount" />
                <x-input-error :messages="$errors->get('required_loan_amount')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800">
                <div>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4">1) Cash Credit (CC) Loan</h4>
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="cc_amount" value="CC Amount" />
                            <x-text-input id="cc_amount" type="text" name="cc_amount" :value="old('cc_amount', $lead?->cc_amount)" />
                            <x-input-error :messages="$errors->get('cc_amount')" />
                        </div>
                        <div>
                            <x-input-label for="cc_details" value="CC Details" />
                            <textarea id="cc_details" name="cc_details" rows="3" class="w-full rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 text-sm px-3 py-2" placeholder="Describe CC requirements, stocks, debtors details...">{{ old('cc_details', $lead?->cc_details) }}</textarea>
                            <x-input-error :messages="$errors->get('cc_details')" />
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4">2) Term Loan</h4>
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="term_loan_amount" value="Term Loan Amount" />
                            <x-text-input id="term_loan_amount" type="text" name="term_loan_amount" :value="old('term_loan_amount', $lead?->term_loan_amount)" />
                            <x-input-error :messages="$errors->get('term_loan_amount')" />
                        </div>
                        <div>
                            <x-input-label for="term_loan_machinery_details" value="Machinery / Term Details" />
                            <textarea id="term_loan_machinery_details" name="term_loan_machinery_details" rows="3" class="w-full rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 text-sm px-3 py-2" placeholder="List machine details, quotations, construction etc...">{{ old('term_loan_machinery_details', $lead?->term_loan_machinery_details) }}</textarea>
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
                                        <input type="text" name="current_loans[{{ $i }}][bank_name]" value="{{ old("current_loans.{$i}.bank_name", $loan['bank_name'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="Bank">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="current_loans[{{ $i }}][loan_type]" value="{{ old("current_loans.{$i}.loan_type", $loan['loan_type'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="e.g. Home, Personal">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="current_loans[{{ $i }}][loan_amount]" value="{{ old("current_loans.{$i}.loan_amount", $loan['loan_amount'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="Amount">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="current_loans[{{ $i }}][disburse_date]" value="{{ old("current_loans.{$i}.disburse_date", $loan['disburse_date'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="Date">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="current_loans[{{ $i }}][emi]" value="{{ old("current_loans.{$i}.emi", $loan['emi'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="EMI">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="current_loans[{{ $i }}][outstanding_amount]" value="{{ old("current_loans.{$i}.outstanding_amount", $loan['outstanding_amount'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="Outstanding">
                                    </td>
                                    <td class="p-2">
                                        <input type="text" name="current_loans[{{ $i }}][tenure]" value="{{ old("current_loans.{$i}.tenure", $loan['tenure'] ?? '') }}" class="w-full bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded-lg text-sm px-2 py-1 dark:text-white" placeholder="Tenure">
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="flex justify-start pt-4 border-t border-slate-200/80 dark:border-slate-800">
                <button type="button" onclick="switchTab('business-tab', 'business-panel')" class="inline-flex items-center justify-center h-10 px-5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                    Previous
                </button>
            </div>
        </div>
    </div>
</div>

<script>
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
    targetTab.className = "tab-btn flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 text-primary-600 dark:text-primary-400 font-semibold text-sm rounded-xl shadow-sm border border-slate-200/50 dark:border-slate-700 focus:outline-none transition";
    
    // Set active SVG icon color class
    const activeSvg = targetTab.querySelector('svg');
    if (activeSvg) {
        activeSvg.setAttribute('class', 'w-4 h-4 text-primary-600 dark:text-primary-400');
    }
}
</script>
