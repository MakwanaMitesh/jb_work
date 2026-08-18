<x-admin-layout :title="'Permissions — ' . $user->name">
    <div class="mb-4">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0 small" style="font-size: 0.82rem; font-weight: 500;">
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="text-decoration-none text-secondary">Users</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">Permissions</li>
            </ol>
        </nav>
        <h1 class="fw-bold mb-1" style="font-size: 1.85rem; color: #111827; letter-spacing: -0.02em;">Edit User Permissions</h1>
        <p class="text-muted small mb-0">
            {{ $user->name }} ({{ $user->email }}) ·
            @forelse ($user->roles as $role)
                Role: <strong>{{ $role->name }}</strong>{{ ! $loop->last ? ', ' : '' }}
            @empty
                No role assigned
            @endforelse
        </p>
    </div>

    <form method="POST" action="{{ route('admin.users.permissions.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff; border: 1px solid #e5e7eb !important; padding: 1.75rem 2rem;">
            <div class="alert alert-info border-0 bg-primary-subtle text-primary-emphasis rounded-3 p-3 mb-4 small" style="border-radius: 8px;">
                These permissions are granted directly to this user in addition to their assigned role permissions.
            </div>

            <h6 class="fw-semibold mb-3 text-dark">Direct Permissions</h6>
            @include('admin.partials.permission-checkboxes', ['selectedIds' => old('permissions', $userPermissionIds)])
        </div>

        <div class="d-flex align-items-center gap-2 mb-5">
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm" style="background-color: #2563eb; border-color: #2563eb; border-radius: 8px; font-size: 0.875rem;">
                Save changes
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-white border px-4 py-2 fw-medium shadow-sm" style="background-color: #ffffff; border-color: #d1d5db !important; color: #374151; border-radius: 8px; font-size: 0.875rem;">
                Cancel
            </a>
        </div>
    </form>
</x-admin-layout>
