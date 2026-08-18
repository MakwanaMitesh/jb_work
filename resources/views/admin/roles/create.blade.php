<x-admin-layout title="New Role">
    <div class="mb-4">
        <h1 class="h4 fw-bold mb-1">New Role</h1>
        <p class="text-muted small mb-0">Define a role and assign its permissions.</p>
    </div>

    <form method="POST" action="{{ route('admin.roles.store') }}">
        @csrf

        <div class="jb-card p-4 mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <x-input-label for="name" value="Role Name" />
                    <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus placeholder="e.g. Accountant" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div class="col-md-6">
                    <x-input-label for="description" value="Description" />
                    <x-text-input id="description" type="text" name="description" :value="old('description')" placeholder="Optional short description" />
                    <x-input-error :messages="$errors->get('description')" />
                </div>
            </div>
        </div>

        <div class="jb-card p-4 mb-4">
            <h6 class="fw-semibold mb-3">Permissions</h6>
            @include('admin.partials.permission-checkboxes', ['selectedIds' => old('permissions', [])])
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Create Role</button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</x-admin-layout>
