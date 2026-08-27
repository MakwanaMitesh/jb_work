<x-admin-layout title="Edit Customer Constitution" :white-bg="true">
    <div class="mb-6">
        <nav class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500 text-xs font-medium mb-1.5">
            <a href="{{ route('admin.constitutions.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300">Constitutions</a>
            <svg class="w-3.5 h-3.5 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="text-slate-900 dark:text-slate-200">Edit</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Edit: {{ $constitution->name }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.constitutions.update', $constitution) }}">
        @csrf
        @method('PUT')
        @include('admin.constitutions._form')

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.constitutions.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                Save Changes
            </button>
        </div>
    </form>
</x-admin-layout>
