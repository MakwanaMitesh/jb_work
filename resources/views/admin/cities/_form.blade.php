{{-- Expects: $city (nullable, for edit) --}}
@php $city = $city ?? null; @endphp

<div class="jb-form-card">
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <x-input-label-bs for="name" value="City Name" :required="true" />
            <x-text-input-bs id="name" type="text" name="name" :value="old('name', $city?->name)" required autofocus placeholder="e.g. Austin" />
            <x-input-error-bs :messages="$errors->get('name')" />
        </div>
        <div class="col-12 col-md-6">
            <x-input-label-bs for="status" value="Status" :required="true" />
            <select id="status" name="status" class="form-select jb-input">
                <option value="active" @selected(old('status', $city?->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $city?->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
            <x-input-error-bs :messages="$errors->get('status')" />
        </div>
    </div>
</div>
