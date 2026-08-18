<x-admin-layout :title="'Edit Role — ' . $role->name">
    <div class="mb-6">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500 text-xs font-medium mb-1.5">
            <a href="{{ route('admin.roles.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300">Roles</a>
            <svg class="w-3.5 h-3.5 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="text-slate-900 dark:text-slate-200">Edit</span>
        </nav>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Edit Role</h1>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Update role details and its assigned permissions.</p>
    </div>

    @if ($role->isProtected())
        <div class="bg-blue-50 border border-blue-200/50 dark:bg-blue-950/20 dark:border-blue-900/30 rounded-lg p-4 mb-6 text-sm text-blue-800 dark:text-blue-400">
            This is a protected system role. Its name and status cannot be changed, but you can review or modify its permissions below.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 sm:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="name" value="Role Name" :required="true" />
                    <x-text-input id="name" type="text" name="name" :value="old('name', $role->name)" required :readonly="$role->isProtected()" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="description" value="Description" />
                    <x-text-input id="description" type="text" name="description" :value="old('description', $role->description)" />
                    <x-input-error :messages="$errors->get('description')" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 sm:p-8">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Permissions</h3>
            @include('admin.partials.permission-checkboxes', ['selectedIds' => old('permissions', $rolePermissionIds)])
        </div>

        <div class="flex items-center gap-3">
            <x-primary-button>Save changes</x-primary-button>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center justify-center h-10 px-5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                Cancel
            </a>
        </div>
    </form>
</x-admin-layout>
