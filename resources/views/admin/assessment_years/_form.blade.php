{{-- Expects: $assessmentYear (nullable, for edit) --}}
@php $assessmentYear = $assessmentYear ?? null; @endphp

<div class="jb-form-card">
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <x-input-label-bs for="name" value="Assessment Year Name" :required="true" />
            <x-text-input-bs id="name" type="text" name="name" :value="old('name', $assessmentYear?->name)" required autofocus placeholder="e.g. A.Y. 2026-27" />
            <x-input-error-bs :messages="$errors->get('name')" />
        </div>
        <div class="col-12 col-md-6">
            <x-input-label-bs for="code" value="Code / Short Year" />
            <x-text-input-bs id="code" type="text" name="code" :value="old('code', $assessmentYear?->code)" placeholder="e.g. 2026-27" />
            <x-input-error-bs :messages="$errors->get('code')" />
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12 col-md-6">
            <x-input-label-bs for="sort_order" value="Sort Order" :required="true" />
            <x-text-input-bs id="sort_order" type="number" name="sort_order" :value="old('sort_order', $assessmentYear?->sort_order ?? 0)" required min="0" />
            <x-input-error-bs :messages="$errors->get('sort_order')" />
        </div>
        <div class="col-12 col-md-6">
            <x-input-label-bs for="status" value="Status" :required="true" />
            <select id="status" name="status" class="form-select jb-input">
                <option value="active" @selected(old('status', $assessmentYear?->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $assessmentYear?->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
            <x-input-error-bs :messages="$errors->get('status')" />
        </div>
    </div>
</div>
