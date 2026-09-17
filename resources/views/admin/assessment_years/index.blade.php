<x-admin-layout title="Assessment Years" :white-bg="true">
    <div class="mb-6">
        <nav class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500 text-xs font-medium mb-1.5">
            <a href="{{ route('admin.assessment-years.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300">Assessment Years</a>
            <svg class="w-3.5 h-3.5 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="text-slate-900 dark:text-slate-200">List</span>
        </nav>

        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Assessment Years</h1>
            <a href="{{ route('admin.assessment-years.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <span>New Assessment Year</span>
            </a>
        </div>
    </div>

    <x-datatable-card tableId="assessment-years-table" searchPlaceholder="Search">
        <x-slot:filters>
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Status</label>
                <select data-filter-column="2" data-filter-type="regex" class="form-select w-full border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                    <option value="">All</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
        </x-slot:filters>

        <x-slot:thead>
            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Assessment Year</th>
            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Code</th>
            <th data-col="status" class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Order</th>
            <th class="px-6 py-3.5 text-right w-16"></th>
        </x-slot:thead>

        @forelse ($assessmentYears as $ay)
            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition duration-150">
                <td class="px-6 py-4.5 whitespace-nowrap font-semibold text-slate-900 dark:text-white text-sm">
                    {{ $ay->name }}
                </td>
                <td class="px-6 py-4.5 whitespace-nowrap">
                    <span class="text-sm font-mono text-slate-600 dark:text-slate-400">{{ $ay->code ?: '—' }}</span>
                </td>
                <td class="px-6 py-4.5 whitespace-nowrap" data-col="status">
                    @if ($ay->status === 'active')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-500 border border-emerald-200/30 dark:border-emerald-900/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                            Inactive
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4.5 whitespace-nowrap text-sm font-semibold text-slate-600 dark:text-slate-400">
                    {{ $ay->sort_order }}
                </td>
                <td class="px-6 py-4.5 whitespace-nowrap text-right">
                    <div class="relative inline-block text-left" data-kebab-container>
                        <button type="button" data-kebab-trigger class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                        </button>
                        <div data-kebab-menu class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-slate-200 dark:border-slate-800 py-1 z-50">
                            <a href="{{ route('admin.assessment-years.edit', $ay) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.assessment-years.toggle-status', $ay) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    <span>Toggle Status</span>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.assessment-years.destroy', $ay) }}" onsubmit="return confirm('Are you sure you want to delete this Assessment Year?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                    No assessment years found.
                </td>
            </tr>
        @endforelse
        
        <x-slot:pagination>
            {{ $assessmentYears->links() }}
        </x-slot:pagination>
    </x-datatable-card>
</x-admin-layout>
