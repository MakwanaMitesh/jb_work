{{-- Expects: $constitution (nullable) --}}
@php $constitution = $constitution ?? null; @endphp

<div class="jb-form-card">
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <x-input-label-bs for="name" value="Constitution Name" :required="true" />
            <x-text-input-bs id="name" type="text" name="name" :value="old('name', $constitution?->name)" required autofocus placeholder="e.g. Proprietorship" />
            <x-input-error-bs :messages="$errors->get('name')" />
        </div>
        <div class="col-12 col-md-6">
            <x-input-label-bs for="code" value="Unique Code" :required="true" />
            <x-text-input-bs id="code" type="text" name="code" :value="old('code', $constitution?->code)" required placeholder="e.g. PROPRIETORSHIP" />
            <x-input-error-bs :messages="$errors->get('code')" />
        </div>
        <div class="col-12 col-md-6">
            <x-input-label-bs for="status" value="Status" :required="true" />
            <select id="status" name="status" class="form-select jb-input">
                <option value="active" @selected(old('status', $constitution?->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $constitution?->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
            <x-input-error-bs :messages="$errors->get('status')" />
        </div>
        <div class="col-12 col-md-6">
            <x-input-label-bs for="sort_order" value="Sort Order" :required="true" />
            <x-text-input-bs id="sort_order" type="number" name="sort_order" :value="old('sort_order', $constitution?->sort_order ?? 0)" required min="0" />
            <x-input-error-bs :messages="$errors->get('sort_order')" />
        </div>
        <div class="col-12">
            <x-input-label-bs for="description" value="Description" />
            <textarea id="description" name="description" rows="3" class="jb-textarea" placeholder="Description of the constitution type...">{{ old('description', $constitution?->description) }}</textarea>
            <x-input-error-bs :messages="$errors->get('description')" />
        </div>
    </div>
</div>
