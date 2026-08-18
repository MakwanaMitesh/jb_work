{{-- Expects: $employee (nullable, for edit), $roles --}}
@php $employee = $employee ?? null; @endphp

<div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm p-6 sm:p-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <x-input-label for="first_name" value="First Name" :required="true" />
            <x-text-input id="first_name" type="text" name="first_name" :value="old('first_name', $employee?->first_name)" required autofocus />
            <x-input-error :messages="$errors->get('first_name')" />
        </div>
        <div>
            <x-input-label for="middle_name" value="Middle Name" />
            <x-text-input id="middle_name" type="text" name="middle_name" :value="old('middle_name', $employee?->middle_name)" />
            <x-input-error :messages="$errors->get('middle_name')" />
        </div>
        <div>
            <x-input-label for="last_name" value="Last Name" :required="true" />
            <x-text-input id="last_name" type="text" name="last_name" :value="old('last_name', $employee?->last_name)" required />
            <x-input-error :messages="$errors->get('last_name')" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div>
            <x-input-label for="email" value="Email" :required="true" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $employee?->email)" required />
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <div>
            <x-input-label for="mobile_number" value="Mobile Number" />
            <x-text-input id="mobile_number" type="text" name="mobile_number" :value="old('mobile_number', $employee?->mobile_number)" />
            <x-input-error :messages="$errors->get('mobile_number')" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div>
            <x-input-label for="city" value="City" />
            <x-text-input id="city" type="text" name="city" :value="old('city', $employee?->city)" />
            <x-input-error :messages="$errors->get('city')" />
        </div>
        <div>
            <x-input-label for="joining_date" value="Joining Date" />
            <x-text-input id="joining_date" type="date" name="joining_date" :value="old('joining_date', $employee?->joining_date?->format('Y-m-d'))" />
            <x-input-error :messages="$errors->get('joining_date')" />
        </div>
    </div>

    <div class="mt-6">
        <x-input-label for="address" value="Address" />
        <textarea id="address" name="address" rows="3" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 shadow-sm text-sm px-3 py-2">{{ old('address', $employee?->address) }}</textarea>
        <x-input-error :messages="$errors->get('address')" />
    </div>

    @php $currentRole = old('role', $employee?->roles->first()->name ?? 'Employee'); @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div>
            <x-input-label for="role" value="Role" :required="true" />
            <select id="role" name="role" class="form-select w-full rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 shadow-sm text-sm h-10 px-3" @disabled(! auth()->user()->can('employees.assign_role'))>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected($currentRole === $role->name)>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
            @if (! auth()->user()->can('employees.assign_role'))
                <div class="text-xs text-slate-400 mt-1">You don't have permission to change the assigned role.</div>
                <input type="hidden" name="role" value="{{ $currentRole }}">
            @endif
            <x-input-error :messages="$errors->get('role')" />
        </div>
        <div>
            <x-input-label for="status" value="Status" :required="true" />
            <select id="status" name="status" class="form-select w-full rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 shadow-sm text-sm h-10 px-3">
                <option value="active" @selected(old('status', $employee?->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $employee?->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
            <x-input-error :messages="$errors->get('status')" />
        </div>
    </div>

    <div class="mt-6">
        <x-input-label for="profile_photo" value="Profile Photo" />
        <input id="profile_photo" type="file" name="profile_photo" class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 dark:file:bg-slate-800 dark:file:text-slate-200 hover:file:bg-slate-100 dark:hover:file:bg-slate-700 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 shadow-sm" accept="image/*">
        <x-input-error :messages="$errors->get('profile_photo')" />
        @if ($employee?->profilePhotoUrl())
            <img src="{{ $employee->profilePhotoUrl() }}" alt="" class="rounded-full mt-3 w-14 h-14 object-cover">
        @endif
    </div>
</div>
