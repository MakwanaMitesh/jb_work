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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div>
            <x-input-label for="email" value="Email" :required="true" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $agent?->email)" required />
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <div>
            <x-input-label for="mobile_number" value="Mobile Number" :required="true" />
            <x-text-input id="mobile_number" type="text" name="mobile_number" :value="old('mobile_number', $agent?->mobile_number)" required />
            <x-input-error :messages="$errors->get('mobile_number')" />
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
</div>
