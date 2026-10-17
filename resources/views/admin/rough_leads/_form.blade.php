@php
    $statuses = \App\Models\RoughLead::STATUSES;
    if (! ($roughLead?->isConverted())) {
        // "Converted" is a result of the Convert action only, never a
        // manually-chosen value, so it's hidden from the dropdown unless
        // this record already carries it.
        unset($statuses['converted']);
    }
@endphp

<div class="jb-panel d-flex flex-column gap-4 mb-4">
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <x-input-label-bs for="full_name" value="Full Name" :required="true" />
            <x-text-input-bs id="full_name" type="text" name="full_name" :value="old('full_name', $roughLead?->full_name)" required />
            <x-input-error-bs :messages="$errors->get('full_name')" />
        </div>

        <div class="col-12 col-md-6">
            <x-input-label-bs for="mobile_number" value="Mobile Number" :required="true" />
            <x-text-input-bs id="mobile_number" type="text" name="mobile_number" :value="old('mobile_number', $roughLead?->mobile_number)" placeholder="+919876543210" required />
            <x-input-error-bs :messages="$errors->get('mobile_number')" />
        </div>

        <div class="col-12 col-md-6">
            <x-input-label-bs for="alternate_mobile_number" value="Alternate Mobile Number" />
            <x-text-input-bs id="alternate_mobile_number" type="text" name="alternate_mobile_number" :value="old('alternate_mobile_number', $roughLead?->alternate_mobile_number)" placeholder="+919876543210" />
            <x-input-error-bs :messages="$errors->get('alternate_mobile_number')" />
        </div>

        <div class="col-12 col-md-6">
            <x-input-label-bs for="email" value="Email" />
            <x-text-input-bs id="email" type="email" name="email" :value="old('email', $roughLead?->email)" />
            <x-input-error-bs :messages="$errors->get('email')" />
        </div>

        <div class="col-12 col-md-6">
            <x-input-label-bs for="city_id" value="City / Location" />
            <select id="city_id" name="city_id" class="form-select jb-input">
                <option value="">Select City</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}" @selected(old('city_id', $roughLead?->city_id) == $city->id)>{{ $city->name }}</option>
                @endforeach
            </select>
            <x-input-error-bs :messages="$errors->get('city_id')" />
        </div>

        <div class="col-12 col-md-6">
            <x-input-label-bs for="loan_requirement" value="Loan Product / Requirement" />
            <x-text-input-bs id="loan_requirement" type="text" name="loan_requirement" :value="old('loan_requirement', $roughLead?->loan_requirement)" placeholder="e.g. Business Loan, CC Limit..." />
            <x-input-error-bs :messages="$errors->get('loan_requirement')" />
        </div>

        <div class="col-12 col-md-6">
            <x-input-label-bs for="source" value="Source / Referral" />
            <x-text-input-bs id="source" type="text" name="source" :value="old('source', $roughLead?->source)" placeholder="e.g. Walk-in, Referral, Website..." />
            <x-input-error-bs :messages="$errors->get('source')" />
        </div>

        <div class="col-12 col-md-6">
            <x-input-label-bs for="assigned_employee_id" value="Assigned Employee" />
            <select id="assigned_employee_id" name="assigned_employee_id" class="form-select jb-input">
                <option value="">Unassigned</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('assigned_employee_id', $roughLead?->assigned_employee_id) == $employee->id)>{{ $employee->name }}</option>
                @endforeach
            </select>
            <x-input-error-bs :messages="$errors->get('assigned_employee_id')" />
        </div>

        <div class="col-12 col-md-6">
            <x-input-label-bs for="status" value="Status" :required="true" />
            <select id="status" name="status" class="form-select jb-input" required>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $roughLead?->status ?? 'new') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error-bs :messages="$errors->get('status')" />
        </div>

        <div class="col-12">
            <x-input-label-bs for="description" value="Description / Enquiry Notes" />
            <textarea id="description" name="description" rows="4" class="jb-textarea">{{ old('description', $roughLead?->description) }}</textarea>
            <x-input-error-bs :messages="$errors->get('description')" />
        </div>
    </div>
</div>
