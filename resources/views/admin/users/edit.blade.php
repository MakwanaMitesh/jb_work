<x-admin-bootstrap-layout :title="'Permissions — ' . $user->name">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.users.index') }}">Users</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">Permissions</span>
        </nav>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.users.index') }}" class="jb-back-btn">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="jb-page-title">Edit User Permissions</h1>
        </div>
        <p class="m-0 mt-1" style="font-size:var(--text-sm);color:var(--slate-500)">
            {{ $user->name }} ({{ $user->email }}) ·
            @forelse ($user->roles as $role)
                Role: <strong style="color:var(--slate-900)">{{ $role->name }}</strong>{{ ! $loop->last ? ', ' : '' }}
            @empty
                No role assigned
            @endforelse
        </p>
    </div>

    <form method="POST" action="{{ route('admin.users.permissions.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="jb-card p-4 p-sm-5 mb-4">
            <div class="jb-alert jb-alert-info mb-4">
                These permissions are granted directly to this user in addition to their assigned role permissions.
            </div>

            <h3 class="jb-section-title mb-3">Direct Permissions</h3>
            @include('admin.partials.permission-checkboxes', ['selectedIds' => old('permissions', $userPermissionIds)])
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button-bs>Save changes</x-primary-button-bs>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary jb-btn-tall">Cancel</a>
        </div>
    </form>
</x-admin-bootstrap-layout>
