{{-- Expects: $bank (nullable, for edit) --}}
@php $bank = $bank ?? null; @endphp

<div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm p-6 sm:p-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-3">
            <x-input-label for="name" value="Bank Name" :required="true" />
            <x-text-input id="name" type="text" name="name" :value="old('name', $bank?->name)" required autofocus placeholder="e.g. State Bank of India" />
            <x-input-error :messages="$errors->get('name')" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div>
            <x-input-label for="sort_order" value="Sort Order" :required="true" />
            <x-text-input id="sort_order" type="number" name="sort_order" :value="old('sort_order', $bank?->sort_order ?? 0)" required min="0" />
            <x-input-error :messages="$errors->get('sort_order')" />
        </div>
        <div>
            <x-input-label for="status" value="Status" :required="true" />
            <select id="status" name="status" class="form-select w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                <option value="active" @selected(old('status', $bank?->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $bank?->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
            <x-input-error :messages="$errors->get('status')" />
        </div>
    </div>
</div>
