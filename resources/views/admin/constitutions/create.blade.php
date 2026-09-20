<x-admin-bootstrap-layout title="Create Customer Constitution" :white-bg="true">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.constitutions.index') }}">Constitutions</a>
            <svg width="14" height="14" style="opacity:.6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">New</span>
        </nav>
        <h1 class="jb-page-title">New Customer Constitution</h1>
    </div>

    <form method="POST" action="{{ route('admin.constitutions.store') }}">
        @csrf
        @include('admin.constitutions._form')

        <div class="mt-4 d-flex align-items-center justify-content-end gap-3">
            <a href="{{ route('admin.constitutions.index') }}" class="btn btn-secondary">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                Create Constitution
            </button>
        </div>
    </form>
</x-admin-bootstrap-layout>
