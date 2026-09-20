<x-admin-bootstrap-layout title="Edit Bank" :white-bg="true">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.banks.index') }}">Banks</a>
            <svg width="14" height="14" style="opacity:.6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">Edit</span>
        </nav>

        <h1 class="jb-page-title">Edit Bank: {{ $bank->name }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.banks.update', $bank) }}">
        @csrf
        @method('PUT')
        @include('admin.banks._form', ['bank' => $bank])

        <div class="mt-4 d-flex align-items-center justify-content-end gap-3">
            <a href="{{ route('admin.banks.index') }}" class="btn btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                Update Bank
            </button>
        </div>
    </form>
</x-admin-bootstrap-layout>
