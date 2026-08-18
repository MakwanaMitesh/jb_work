<x-admin-layout :title="'Permissions — ' . $user->name">
    <div class="mb-6">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500 text-xs font-medium mb-1.5">
            <a href="{{ route('admin.users.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300">Users</a>
            <svg class="w-3.5 h-3.5 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="text-slate-900 dark:text-slate-200">Permissions</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Edit User Permissions</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            {{ $user->name }} ({{ $user->email }}) ·
            @forelse ($user->roles as $role)
                Role: <strong class="text-slate-900 dark:text-white">{{ $role->name }}</strong>{{ ! $loop->last ? ', ' : '' }}
            @empty
                No role assigned
            @endforelse
        </p>
    </div>

    <form method="POST" action="{{ route('admin.users.permissions.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 sm:p-8">
            <div class="bg-blue-50 border border-blue-200/50 dark:bg-blue-950/20 dark:border-blue-900/30 rounded-lg p-4 mb-6 text-sm text-blue-800 dark:text-blue-400">
                These permissions are granted directly to this user in addition to their assigned role permissions.
            </div>

            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Direct Permissions</h3>
            @include('admin.partials.permission-checkboxes', ['selectedIds' => old('permissions', $userPermissionIds)])
        </div>

        <div class="flex items-center gap-3">
            <x-primary-button>Save changes</x-primary-button>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                Cancel
            </a>
        </div>
    </form>
</x-admin-layout>
