<x-admin-layout :title="$constitution->name" :white-bg="true">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500 text-xs font-medium mb-1.5">
                <a href="{{ route('admin.constitutions.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300">Constitutions</a>
                <svg class="w-3.5 h-3.5 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
                <span class="text-slate-900 dark:text-slate-200">Details</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $constitution->name }}</h1>
        </div>
        <div class="flex items-center gap-3">
            @can('constitutions.edit')
                <a href="{{ route('admin.constitutions.edit', $constitution) }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                    Edit Constitution
                </a>
            @endcan
            <a href="{{ route('admin.constitutions.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">Constitution Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <span class="block text-xs font-medium text-slate-400">Code</span>
                        <span class="block font-semibold text-slate-900 dark:text-white font-mono mt-0.5">{{ $constitution->code }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-400">Status</span>
                        @if ($constitution->status === 'active')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-500 border border-emerald-200/30 mt-1">Active</span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-650 mt-1">Inactive</span>
                        @endif
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-400">Sort Order</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $constitution->sort_order }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-xs font-medium text-slate-400">Description</span>
                        <p class="text-slate-700 dark:text-slate-300 mt-1">{{ $constitution->description ?: 'No description provided.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
