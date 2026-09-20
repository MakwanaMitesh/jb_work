<x-admin-bootstrap-layout :title="$constitution->name" :white-bg="true">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 mb-4">
        <div>
            <nav class="jb-breadcrumb">
                <a href="{{ route('admin.constitutions.index') }}">Constitutions</a>
                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
                <span class="current">Details</span>
            </nav>
            <h1 class="jb-page-title">{{ $constitution->name }}</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            @can('constitutions.edit')
                <a href="{{ route('admin.constitutions.edit', $constitution) }}" class="btn btn-primary">Edit Constitution</a>
            @endcan
            <a href="{{ route('admin.constitutions.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="jb-panel">
                <h3 class="jb-panel-title">Constitution Details</h3>
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <span class="jb-detail-label">Code</span>
                        <span class="jb-detail-value font-monospace">{{ $constitution->code }}</span>
                    </div>
                    <div class="col-12 col-md-6">
                        <span class="jb-detail-label">Status</span>
                        <div class="mt-1">
                            @if ($constitution->status === 'active')
                                <span class="jb-badge jb-badge-sm jb-badge-emerald">Active</span>
                            @else
                                <span class="jb-badge jb-badge-sm jb-badge-slate">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <span class="jb-detail-label">Sort Order</span>
                        <span class="jb-detail-value">{{ $constitution->sort_order }}</span>
                    </div>
                    <div class="col-12">
                        <span class="jb-detail-label">Description</span>
                        <p class="jb-detail-text">{{ $constitution->description ?: 'No description provided.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-bootstrap-layout>
