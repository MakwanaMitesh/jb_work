<x-admin-bootstrap-layout :title="'Edit Role — ' . $role->name">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.roles.index') }}">Roles</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">Edit</span>
        </nav>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.roles.index') }}" class="jb-back-btn">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="jb-page-title">Edit Role</h1>
        </div>
    </div>

    @if ($role->isProtected())
        <div class="jb-alert jb-alert-info mb-4">
            This is a protected system role. Its name and status cannot be changed, but you can review or modify its permissions below.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')

        <div class="jb-card p-4 p-sm-5 mb-4">
            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <x-input-label-bs for="name" value="Role Name" :required="true" />
                    <x-text-input-bs id="name" type="text" name="name" :value="old('name', $role->name)" required :readonly="$role->isProtected()" />
                    <x-input-error-bs :messages="$errors->get('name')" />
                </div>
                <div class="col-12 col-md-6">
                    <x-input-label-bs for="description" value="Description" />
                    <x-text-input-bs id="description" type="text" name="description" :value="old('description', $role->description)" />
                    <x-input-error-bs :messages="$errors->get('description')" />
                </div>
            </div>
        </div>

        <div class="jb-card p-4 p-sm-5 mb-4">
            <h3 class="jb-section-title mb-3">Permissions</h3>
            @include('admin.partials.permission-checkboxes', ['selectedIds' => old('permissions', $rolePermissionIds)])
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button-bs>Save changes</x-primary-button-bs>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary jb-btn-tall">Cancel</a>
        </div>
    </form>
</x-admin-bootstrap-layout>
