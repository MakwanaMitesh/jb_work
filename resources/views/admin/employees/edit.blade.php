<x-admin-layout :title="'Edit Employee — ' . $employee->name">
    <div class="mb-4">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0 small" style="font-size: 0.82rem; font-weight: 500;">
                <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}" class="text-decoration-none text-secondary">Employees</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">Edit</li>
            </ol>
        </nav>
        <h1 class="fw-bold mb-0" style="font-size: 1.85rem; color: #111827; letter-spacing: -0.02em;">Edit Employee</h1>
    </div>

    <form method="POST" action="{{ route('admin.employees.update', $employee) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin.employees._form', ['employee' => $employee, 'roles' => $roles])

        <div class="d-flex align-items-center gap-2 mb-5">
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm" style="background-color: #2563eb; border-color: #2563eb; border-radius: 8px; font-size: 0.875rem;">
                Save changes
            </button>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-white border px-4 py-2 fw-medium shadow-sm" style="background-color: #ffffff; border-color: #d1d5db !important; color: #374151; border-radius: 8px; font-size: 0.875rem;">
                Cancel
            </a>
        </div>
    </form>
</x-admin-layout>
