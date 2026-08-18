{{-- Expects: $city (nullable, for edit) --}}
@php $city = $city ?? null; @endphp

<div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm p-6 sm:p-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <x-input-label for="name" value="City Name" :required="true" />
            <x-text-input id="name" type="text" name="name" :value="old('name', $city?->name)" required autofocus placeholder="e.g. Austin" />
            <x-input-error :messages="$errors->get('name')" />
        </div>
        <div>
            <x-input-label for="status" value="Status" :required="true" />
            <select id="status" name="status" class="form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                <option value="active" @selected(old('status', $city?->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $city?->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
            <x-input-error :messages="$errors->get('status')" />
        </div>
    </div>
</div>
