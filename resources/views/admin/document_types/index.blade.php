@extends('layouts.admin')

@section('title', 'Document Master')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Document Master</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Configure required document types, formats, file size limits and front/back requirements for Leads.</p>
        </div>
        @can('document_types.create')
            <a href="{{ route('admin.document-types.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Document Type
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row gap-3 justify-between items-center bg-slate-50/50 dark:bg-slate-950/25">
            <form action="{{ route('admin.document-types.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search document types..." class="w-full pl-9 pr-4 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <select name="status" onchange="this.form.submit()" class="py-2 pl-3 pr-8 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:border-primary-500">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('admin.document-types.index') }}" class="text-xs text-slate-500 hover:text-slate-700 font-medium">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="bg-slate-50 dark:bg-slate-950/50 text-slate-700 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-4 w-12 text-center">#</th>
                        <th class="p-4">Document Name</th>
                        <th class="p-4">Required</th>
                        <th class="p-4">Upload Mode</th>
                        <th class="p-4">Formats</th>
                        <th class="p-4">Max Size</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse ($documentTypes as $index => $type)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10">
                            <td class="p-4 text-center font-medium text-slate-400">{{ $documentTypes->firstItem() + $index }}</td>
                            <td class="p-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $type->name }}</div>
                                <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $type->code }}</div>
                            </td>
                            <td class="p-4">
                                @if ($type->is_required)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border border-rose-200/50 dark:border-rose-900/50">Required</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-500">Optional</span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-600 dark:text-slate-300 text-xs">
                                @if ($type->has_front_back)
                                    <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400 font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                        Front & Back
                                    </span>
                                @elseif ($type->allow_multiple)
                                    <span class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        Multiple Files
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-400 font-medium">
                                        Single File
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-xs font-mono text-slate-600 dark:text-slate-300">
                                {{ strtoupper($type->allowed_extensions_string) }}
                            </td>
                            <td class="p-4 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                {{ $type->formatted_max_size }}
                            </td>
                            <td class="p-4">
                                <form action="{{ route('admin.document-types.toggle-status', $type) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $type->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ ucfirst($type->status) }}
                                    </button>
                                </form>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                @can('document_types.edit')
                                    <a href="{{ route('admin.document-types.edit', $type) }}" class="inline-flex items-center p-1.5 text-slate-500 hover:text-primary-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">No document types configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documentTypes->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $documentTypes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
