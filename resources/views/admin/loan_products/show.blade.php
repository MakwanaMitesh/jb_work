<x-admin-bootstrap-layout :title="$loanProduct->name" :white-bg="true">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 mb-4">
        <div>
            <nav class="jb-breadcrumb">
                <a href="{{ route('admin.loan-products.index') }}">Loan Products</a>
                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
                <span class="current">Details</span>
            </nav>
            <h1 class="jb-page-title">{{ $loanProduct->name }}</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            @can('loan_products.edit')
                <a href="{{ route('admin.loan-products.edit', $loanProduct) }}" class="btn btn-primary">Edit Product</a>
            @endcan
            <a href="{{ route('admin.loan-products.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="jb-panel mb-4">
                <h3 class="jb-panel-title">Product Details</h3>
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <span class="jb-detail-label">Code</span>
                        <span class="jb-detail-value font-monospace">{{ $loanProduct->code }}</span>
                    </div>
                    <div class="col-12 col-md-6">
                        <span class="jb-detail-label">Status</span>
                        <div class="mt-1">
                            @if ($loanProduct->status === 'active')
                                <span class="jb-badge jb-badge-sm jb-badge-emerald">Active</span>
                            @else
                                <span class="jb-badge jb-badge-sm jb-badge-slate">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <span class="jb-detail-label">Sort Order</span>
                        <span class="jb-detail-value">{{ $loanProduct->sort_order }}</span>
                    </div>
                    <div class="col-12">
                        <span class="jb-detail-label">Description</span>
                        <p class="jb-detail-text">{{ $loanProduct->description ?: 'No description provided.' }}</p>
                    </div>
                </div>
            </div>

            <div class="jb-panel">
                <div class="d-flex align-items-center justify-content-between jb-panel-title">
                    <h3 class="m-0" style="font-size:var(--text-base);font-weight:var(--font-weight-bold)">Supported Customer Constitutions</h3>
                    @can('loan_product_config.edit')
                        <a href="{{ route('admin.loan-products.config.edit', $loanProduct) }}" class="btn btn-sm jb-btn-soft">Manage Config</a>
                    @endcan
                </div>

                @if ($loanProduct->constitutions->isNotEmpty())
                    <div class="row g-3">
                        @foreach ($loanProduct->constitutions as $const)
                            <div class="col-12 col-md-6">
                                <div class="jb-subcard d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="d-block fw-semibold" style="color:var(--slate-900)">{{ $const->name }}</span>
                                        <span class="jb-muted d-block mt-1">Code: {{ $const->code }}</span>
                                    </div>
                                    <span class="jb-dot jb-dot-emerald" style="width:.625rem;height:.625rem"></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="m-0" style="color:var(--slate-400)">No supported constitutions configured yet. Please configure which constitutions this product accepts.</p>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="jb-panel jb-panel-muted">
                <h4 class="fw-bold mb-3" style="font-size:var(--text-sm);color:var(--slate-800)">Workflow Architecture</h4>
                <p class="jb-muted m-0" style="line-height:var(--leading-relaxed)">
                    Workflows and mandatory checklist templates are bound to the specific combinations of Loan Product and Customer Constitution. Configure accepted constitutions on this page to prepare validation constraints.
                </p>
            </div>
        </div>
    </div>
</x-admin-bootstrap-layout>
