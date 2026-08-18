<x-admin-layout title="Create Role">
    <div class="mb-6">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500 text-xs font-medium mb-1.5">
            <a href="{{ route('admin.roles.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300">Roles</a>
            <svg class="w-3.5 h-3.5 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="text-slate-900 dark:text-slate-200">Create</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Create Role</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Define a new role and configure its access permissions.</p>
    </div>

    <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 sm:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="name" value="Role Name" :required="true" />
                    <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus placeholder="e.g. Accountant" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="description" value="Description" />
                    <x-text-input id="description" type="text" name="description" :value="old('description')" placeholder="Optional short description" />
                    <x-input-error :messages="$errors->get('description')" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 sm:p-8">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Permissions</h3>
            @include('admin.partials.permission-checkboxes', ['selectedIds' => old('permissions', [])])
        </div>

        <div class="flex items-center gap-3">
            <x-primary-button>Create Role</x-primary-button>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                Cancel
            </a>
        </div>
    </form>
</x-admin-layout>
