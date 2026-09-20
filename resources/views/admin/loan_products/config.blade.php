<x-admin-bootstrap-layout :title="'Configure: ' . $loanProduct->name" :white-bg="true">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.loan-products.index') }}">Loan Products</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <a href="{{ route('admin.loan-products.show', $loanProduct) }}">{{ $loanProduct->name }}</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">Configure</span>
        </nav>
        <h1 class="jb-page-title">Configure Supported Constitutions</h1>
        <p class="m-0 mt-1" style="font-size:var(--text-sm);color:var(--slate-500)">Select which customer constitutions are valid for <strong>{{ $loanProduct->name }}</strong>.</p>
    </div>

    <form method="POST" action="{{ route('admin.loan-products.config.update', $loanProduct) }}">
        @csrf

        <div class="jb-panel" style="max-width:42rem">
            <h3 class="jb-panel-title" style="font-size:var(--text-sm);color:var(--slate-800)">Customer Constitution Checklist</h3>

            <div class="d-flex flex-column gap-3">
                @foreach ($constitutions as $const)
                    <label class="jb-check-tile">
                        <input type="checkbox" name="constitutions[]" value="{{ $const->id }}"
                            @checked(in_array($const->id, $assignedConstitutionIds))
                            class="form-check-input flex-shrink-0 mt-1">
                        <div>
                            <span class="d-block fw-semibold" style="font-size:var(--text-sm);color:var(--slate-900)">{{ $const->name }}</span>
                            <span class="jb-muted d-block mt-1">Code: <span class="font-monospace">{{ $const->code }}</span> @if($const->description) | {{ $const->description }} @endif</span>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="mt-4 d-flex align-items-center justify-content-end gap-3" style="max-width:42rem">
            <a href="{{ route('admin.loan-products.show', $loanProduct) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Configurations</button>
        </div>
    </form>
</x-admin-bootstrap-layout>
