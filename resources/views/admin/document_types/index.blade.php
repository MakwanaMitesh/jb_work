@extends('layouts.admin-bootstrap')

@section('title', 'Document Master')

@section('content')
<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between gap-3">
        <div>
            <h1 class="jb-page-title">Document Master</h1>
            <p class="m-0 mt-1" style="font-size:var(--text-sm);color:var(--slate-500)">Configure required document types, formats, file size limits and front/back requirements for Leads.</p>
        </div>
        @can('document_types.create')
            <a href="{{ route('admin.document-types.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Document Type
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="jb-alert jb-alert-success fw-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="jb-card overflow-hidden">
        <div class="jb-card-header d-flex flex-column flex-sm-row gap-3 justify-content-between align-items-center">
            <form action="{{ route('admin.document-types.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-3 w-100 w-sm-auto">
                <div class="jb-search">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--slate-400);flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search document types...">
                </div>
                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('admin.document-types.index') }}" class="jb-file-link-muted fw-medium">Clear</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="jb-table jb-table-compact">
                <thead>
                    <tr>
                        <th class="text-center" style="width:3rem">#</th>
                        <th>Document Name</th>
                        <th>Required</th>
                        <th>Upload Mode</th>
                        <th>Formats</th>
                        <th>Max Size</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documentTypes as $index => $type)
                        <tr>
                            <td class="text-center fw-medium" style="color:var(--slate-400)">{{ $documentTypes->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-bold" style="color:var(--slate-900)">{{ $type->name }}</div>
                                <div class="jb-muted font-monospace mt-1">{{ $type->code }}</div>
                            </td>
                            <td>
                                @if ($type->is_required)
                                    <span class="jb-badge jb-badge-sm jb-badge-rose fw-bold">Required</span>
                                @else
                                    <span class="jb-badge jb-badge-sm jb-badge-slate">Optional</span>
                                @endif
                            </td>
                            <td>
                                @if ($type->has_front_back)
                                    <span class="d-inline-flex align-items-center gap-1 fw-semibold" style="color:var(--amber-600);font-size:var(--text-xs)">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                        Front &amp; Back
                                    </span>
                                @elseif ($type->allow_multiple)
                                    <span class="d-inline-flex align-items-center gap-1 fw-semibold" style="color:var(--indigo-600);font-size:var(--text-xs)">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        Multiple Files
                                    </span>
                                @else
                                    <span class="fw-medium" style="font-size:var(--text-xs)">Single File</span>
                                @endif
                            </td>
                            <td class="font-monospace" style="font-size:var(--text-xs)">
                                {{ strtoupper($type->allowed_extensions_string) }}
                            </td>
                            <td class="fw-semibold" style="font-size:var(--text-xs)">
                                {{ $type->formatted_max_size }}
                            </td>
                            <td>
                                <form action="{{ route('admin.document-types.toggle-status', $type) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="jb-badge jb-badge-sm border-0 {{ $type->status === 'active' ? 'jb-badge-emerald' : 'jb-badge-slate' }}">
                                        {{ ucfirst($type->status) }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                @can('document_types.edit')
                                    <a href="{{ route('admin.document-types.edit', $type) }}" class="jb-icon-btn">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5" style="color:var(--slate-400)">No document types configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documentTypes->hasPages())
            <div class="p-3 border-top">
                {{ $documentTypes->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
