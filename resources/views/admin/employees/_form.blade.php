{{-- Expects: $employee (nullable, for edit), $roles --}}
@php $employee = $employee ?? null; @endphp

<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff; border: 1px solid #e5e7eb !important; padding: 1.75rem 2rem;">
    <div class="row g-4">
        <div class="col-md-4">
            <x-input-label for="first_name" value="First Name" :required="true" />
            <x-text-input id="first_name" type="text" name="first_name" :value="old('first_name', $employee?->first_name)" required autofocus style="border-radius: 8px; border-color: #d1d5db; height: 40px; font-size: 0.875rem;" />
            <x-input-error :messages="$errors->get('first_name')" />
        </div>
        <div class="col-md-4">
            <x-input-label for="middle_name" value="Middle Name" />
            <x-text-input id="middle_name" type="text" name="middle_name" :value="old('middle_name', $employee?->middle_name)" style="border-radius: 8px; border-color: #d1d5db; height: 40px; font-size: 0.875rem;" />
            <x-input-error :messages="$errors->get('middle_name')" />
        </div>
        <div class="col-md-4">
            <x-input-label for="last_name" value="Last Name" :required="true" />
            <x-text-input id="last_name" type="text" name="last_name" :value="old('last_name', $employee?->last_name)" required style="border-radius: 8px; border-color: #d1d5db; height: 40px; font-size: 0.875rem;" />
            <x-input-error :messages="$errors->get('last_name')" />
        </div>

        <div class="col-md-6">
            <x-input-label for="email" value="Email" :required="true" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $employee?->email)" required style="border-radius: 8px; border-color: #d1d5db; height: 40px; font-size: 0.875rem;" />
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <div class="col-md-6">
            <x-input-label for="mobile_number" value="Mobile Number" />
            <x-text-input id="mobile_number" type="text" name="mobile_number" :value="old('mobile_number', $employee?->mobile_number)" style="border-radius: 8px; border-color: #d1d5db; height: 40px; font-size: 0.875rem;" />
            <x-input-error :messages="$errors->get('mobile_number')" />
        </div>

        <div class="col-md-6">
            <x-input-label for="city" value="City" />
            <x-text-input id="city" type="text" name="city" :value="old('city', $employee?->city)" style="border-radius: 8px; border-color: #d1d5db; height: 40px; font-size: 0.875rem;" />
            <x-input-error :messages="$errors->get('city')" />
        </div>
        <div class="col-md-6">
            <x-input-label for="joining_date" value="Joining Date" />
            <x-text-input id="joining_date" type="date" name="joining_date" :value="old('joining_date', $employee?->joining_date?->format('Y-m-d'))" style="border-radius: 8px; border-color: #d1d5db; height: 40px; font-size: 0.875rem;" />
            <x-input-error :messages="$errors->get('joining_date')" />
        </div>

        <div class="col-12">
            <x-input-label for="address" value="Address" />
            <textarea id="address" name="address" class="form-control" rows="2" style="border-radius: 8px; border-color: #d1d5db; font-size: 0.875rem;">{{ old('address', $employee?->address) }}</textarea>
            <x-input-error :messages="$errors->get('address')" />
        </div>

        @php $currentRole = old('role', $employee?->roles->first()->name ?? 'Employee'); @endphp

        <div class="col-md-6">
            <x-input-label for="role" value="Role" :required="true" />
            <select id="role" name="role" class="form-select" @disabled(! auth()->user()->can('employees.assign_role')) style="border-radius: 8px; border-color: #d1d5db; height: 40px; font-size: 0.875rem;">
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected($currentRole === $role->name)>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
            @if (! auth()->user()->can('employees.assign_role'))
                <div class="form-text small text-muted">You don't have permission to change the assigned role.</div>
                <input type="hidden" name="role" value="{{ $currentRole }}">
            @endif
            <x-input-error :messages="$errors->get('role')" />
        </div>
        <div class="col-md-6">
            <x-input-label for="status" value="Status" :required="true" />
            <select id="status" name="status" class="form-select" style="border-radius: 8px; border-color: #d1d5db; height: 40px; font-size: 0.875rem;">
                <option value="active" @selected(old('status', $employee?->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $employee?->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
            <x-input-error :messages="$errors->get('status')" />
        </div>

        <div class="col-md-6">
            <x-input-label for="profile_photo" value="Profile Photo" />
            <input id="profile_photo" type="file" name="profile_photo" class="form-control" accept="image/*" style="border-radius: 8px; border-color: #d1d5db; font-size: 0.875rem;">
            <x-input-error :messages="$errors->get('profile_photo')" />
            @if ($employee?->profilePhotoUrl())
                <img src="{{ $employee->profilePhotoUrl() }}" alt="" class="rounded-circle mt-2" width="56" height="56" style="object-fit: cover;">
            @endif
        </div>
    </div>
</div>
