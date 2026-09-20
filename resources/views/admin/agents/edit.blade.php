<x-admin-bootstrap-layout :title="'Edit Agent — ' . $agent->name">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.agents.index') }}">Agents</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">Edit</span>
        </nav>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.agents.index') }}" class="jb-back-btn">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="jb-page-title">Edit Agent</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.agents.update', $agent) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin.agents._form', ['agent' => $agent])

        <div class="d-flex align-items-center gap-3 mt-4">
            <x-primary-button-bs>Save changes</x-primary-button-bs>
            <a href="{{ route('admin.agents.index') }}" class="btn btn-secondary jb-btn-tall">Cancel</a>
        </div>
    </form>
</x-admin-bootstrap-layout>
