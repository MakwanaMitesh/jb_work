{{-- Expects: $bank (nullable, for edit) --}}
@php $bank = $bank ?? null; @endphp

<div class="jb-form-card">
    <div class="row g-4">
        <div class="col-12">
            <x-input-label-bs for="name" value="Bank Name" :required="true" />
            <x-text-input-bs id="name" type="text" name="name" :value="old('name', $bank?->name)" required autofocus placeholder="e.g. State Bank of India" />
            <x-input-error-bs :messages="$errors->get('name')" />
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12 col-md-6">
            <x-input-label-bs for="sort_order" value="Sort Order" :required="true" />
            <x-text-input-bs id="sort_order" type="number" name="sort_order" :value="old('sort_order', $bank?->sort_order ?? 0)" required min="0" />
            <x-input-error-bs :messages="$errors->get('sort_order')" />
        </div>
        <div class="col-12 col-md-6">
            <x-input-label-bs for="status" value="Status" :required="true" />
            <select id="status" name="status" class="form-select jb-input">
                <option value="active" @selected(old('status', $bank?->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $bank?->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
            <x-input-error-bs :messages="$errors->get('status')" />
        </div>
    </div>
</div>
