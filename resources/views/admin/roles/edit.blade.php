<x-admin-layout :title="'Edit ' . $role->name">
    <div class="mb-4">
        <h1 class="h4 fw-bold mb-1">Edit Role — {{ $role->name }}</h1>
        <p class="text-muted small mb-0">Update role details and its assigned permissions.</p>
    </div>

    @if ($role->isProtected())
        <div class="alert alert-info">
            This is a protected system role. Its name and status cannot be changed, but you can review its permissions below.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')

        <div class="jb-card p-4 mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <x-input-label for="name" value="Role Name" />
                    <x-text-input id="name" type="text" name="name" :value="old('name', $role->name)" required :readonly="$role->isProtected()" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div class="col-md-6">
                    <x-input-label for="description" value="Description" />
                    <x-text-input id="description" type="text" name="description" :value="old('description', $role->description)" />
                    <x-input-error :messages="$errors->get('description')" />
                </div>
            </div>
        </div>

        <div class="jb-card p-4 mb-4">
            <h6 class="fw-semibold mb-3">Permissions</h6>
            @include('admin.partials.permission-checkboxes', ['selectedIds' => old('permissions', $rolePermissionIds)])
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</x-admin-layout>
