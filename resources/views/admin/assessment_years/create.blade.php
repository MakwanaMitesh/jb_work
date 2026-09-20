{{--
    Was <x-app-layout>, a component that does not exist in this project, so
    this page returned a 500. Now on the standard admin layout like every
    other create screen.
--}}
<x-admin-bootstrap-layout title="Create Assessment Year">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.assessment-years.index') }}">Assessment Years</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">Create</span>
        </nav>

        <div class="d-flex align-items-center justify-content-between">
            <h1 class="jb-page-title">Add New Assessment Year</h1>
            <a href="{{ route('admin.assessment-years.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.assessment-years.store') }}">
        @csrf
        @include('admin.assessment_years._form')

        <div class="mt-4 d-flex justify-content-end gap-3">
            <x-primary-button-bs>Save Assessment Year</x-primary-button-bs>
        </div>
    </form>
</x-admin-bootstrap-layout>
