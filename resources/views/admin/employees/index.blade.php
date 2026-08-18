@php
    $activeFilters = collect(['status', 'role', 'city'])->filter(fn ($key) => filled(request($key)))->count();
@endphp

<x-admin-layout title="Employees" :white-bg="true">
    <div class="mb-4">
        <!-- Filament v3 Breadcrumb Navigation -->
        <div class="d-flex align-items-center gap-1.5 text-muted small mb-1" style="font-size: 0.825rem; font-weight: 500;">
            <span>Employees</span>
            <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor" class="opacity-40"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="text-secondary">List</span>
        </div>

        <div class="d-flex align-items-center justify-content-between">
            <h1 class="h3 fw-bold mb-0" style="color: #111827; font-weight: 700; letter-spacing: -0.025em;">Employees</h1>
            @can('employees.create')
                <a href="{{ route('admin.employees.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm" style="border-radius: 0.5rem; font-weight: 600; padding: 0.48rem 0.95rem; font-size: 0.875rem; background-color: #2563eb; border-color: #2563eb;">
                    <span>New employee</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- Filament v3 Table Card Container -->
    <div class="jb-card p-0 mb-5 shadow-sm" style="border-radius: 12px; overflow: hidden; background: #ffffff; border: 1px solid #e5e7eb; margin-bottom: 2.5rem;">
        <div class="jb-toolbar p-3 d-flex align-items-center justify-content-end gap-2" style="background: #ffffff; border-bottom: 1px solid #f3f4f6;">
            <!-- Search Input Pill with Heroicon Magnifying Glass -->
            <div class="jb-search-box rounded-3" style="background: #fff; width: 240px; height: 38px; padding: 0.4rem 0.75rem; border: 1px solid #d1d5db; display: flex; align-items: center; gap: 0.5rem; border-radius: 8px;">
                <svg class="text-muted" width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" /></svg>
                <input type="text" id="employeeSearchInput" placeholder="Search" value="{{ request('search') }}" autocomplete="off" style="border: 0; outline: none; background: transparent; width: 100%; font-size: 0.875rem; color: #111827;">
            </div>

            <!-- Circular Filter Button with Heroicon Funnel -->
            <div class="dropdown">
                <button class="jb-icon-btn position-relative" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-auto-close="outside" aria-expanded="false" title="Filter employees" style="width: 38px; height: 38px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #d1d5db; background: #fff; color: #6b7280;">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 0 1 .628.74v2.288a2.25 2.25 0 0 1-.659 1.59l-4.682 4.683a2.25 2.25 0 0 0-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 0 1 8 18.25v-5.757a2.25 2.25 0 0 0-.659-1.59L2.659 6.22A2.25 2.25 0 0 1 2 4.629V2.34a.75.75 0 0 1 .628-.74Z" clip-rule="evenodd" /></svg>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark border border-light" id="activeFilterBadge" style="padding: 0.18rem 0.35rem; font-size: 0.58rem; transform: translate(-30%, -20%) !important;">
                        {{ $activeFilters }}
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-3 shadow-sm" style="min-width: 260px; border-radius: 8px;">
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select id="filterStatusSelect" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="Active" @selected(request('status') === 'active')>Active</option>
                            <option value="Inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Role</label>
                        <select id="filterRoleSelect" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach ($roles as $roleName)
                                <option value="{{ $roleName }}" @selected(request('role') === $roleName)>{{ $roleName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">City</label>
                        <select id="filterCitySelect" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach ($cities as $cityName)
                                <option value="{{ $cityName }}" @selected(request('city') === $cityName)>{{ $cityName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" id="resetFiltersBtn" class="btn btn-outline-secondary btn-sm flex-fill">Clear Filters</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table datatable align-middle mb-0" id="employees-table">
                <thead>
                    <tr style="background: #ffffff; border-bottom: 1px solid #e5e7eb;">
                        <th class="ps-4" style="border-bottom: 1px solid #e5e7eb; color: #4b5563; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">Name</th>
                        <th style="border-bottom: 1px solid #e5e7eb; color: #4b5563; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">Email</th>
                        <th data-col="city" style="border-bottom: 1px solid #e5e7eb; color: #4b5563; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">City</th>
                        <th data-col="role" style="border-bottom: 1px solid #e5e7eb; color: #4b5563; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">Role</th>
                        <th data-col="status" style="border-bottom: 1px solid #e5e7eb; color: #4b5563; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">Active</th>
                        <th data-col="joined" style="border-bottom: 1px solid #e5e7eb; color: #4b5563; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">Joined</th>
                        <th class="text-end pe-4" style="width: 60px; border-bottom: 1px solid #e5e7eb;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr style="background-color: #ffffff; border-bottom: 1px solid #f3f4f6;">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    @if ($employee->profilePhotoUrl())
                                        <img src="{{ $employee->profilePhotoUrl() }}" alt="" class="rounded-circle" width="36" height="36" style="object-fit: cover;">
                                    @else
                                        @php
                                            $initials = '';
                                            if ($employee->first_name && $employee->last_name) {
                                                $initials = strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1));
                                            } else {
                                                $words = explode(' ', trim($employee->name));
                                                if (count($words) >= 2) {
                                                    $initials = strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
                                                } else {
                                                    $initials = strtoupper(substr($employee->name, 0, 2));
                                                }
                                            }
                                        @endphp
                                        <span class="avatar-circle-initials">{{ $initials }}</span>
                                    @endif
                                    <span class="ms-2 fw-semibold text-dark">{{ $employee->name }}</span>
                                </div>
                            </td>
                            <td class="text-secondary small">{{ $employee->email }}</td>
                            <td class="text-secondary small" data-col="city">{{ $employee->city ?? '—' }}</td>
                            <td data-col="role">
                                @php
                                    $roleName = $employee->roles->first()->name ?? 'Staff';
                                @endphp
                                <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis fw-medium" style="font-size: 0.75rem; padding: 0.25rem 0.6rem;">{{ $roleName }}</span>
                            </td>
                            <td data-col="status">
                                @if ($employee->isActive())
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-medium d-inline-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
                                        Active
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 fw-medium d-inline-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" /></svg>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="text-secondary small" data-col="joined" data-order="{{ $employee->joining_date?->timestamp ?? 0 }}">{{ $employee->joining_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="jb-kebab-btn" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
                                        <!-- Heroicon Ellipsis Vertical -->
                                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" /></svg>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-2" style="border-radius: 8px; min-width: 170px;">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2 text-primary" href="{{ route('admin.employees.show', $employee) }}" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; border-radius: 6px;">
                                                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" /><path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.147.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd" /></svg>
                                                <span class="text-body fw-medium">View profile</span>
                                            </a>
                                        </li>

                                        @can('employees.edit')
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2 text-primary" href="{{ route('admin.employees.edit', $employee) }}" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; border-radius: 6px;">
                                                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.861l-3.152 1.26a.5.5 0 0 1-.652-.652Z" /><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 1 10 6H4.75A.25.25 0 0 0 4.5 6.25v9.5c0 .138.112.25.25.25h9.5a.25.25 0 0 0 .25-.25V10a.75.75 0 0 1 1.5 0v5.75c0 .69-.56 1.25-1.25 1.25H4.75A1.25 1.25 0 0 1 3.5 16V5.75Z" /></svg>
                                                    <span class="text-body fw-medium">Edit</span>
                                                </a>
                                            </li>
                                        @endcan

                                        @can('employees.activate')
                                            <li>
                                                <form method="POST" action="{{ route('admin.employees.toggle-status', $employee) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 {{ $employee->isActive() ? 'text-danger' : 'text-success' }}" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; border-radius: 6px;">
                                                        @if ($employee->isActive())
                                                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" /></svg>
                                                            <span class="text-danger fw-medium">Deactivate</span>
                                                        @else
                                                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
                                                            <span class="text-success fw-medium">Activate</span>
                                                        @endif
                                                    </button>
                                                </form>
                                            </li>
                                        @endcan

                                        @can('employees.delete')
                                            <li><hr class="dropdown-divider my-1" style="border-color: var(--jb-border-light);"></li>
                                            <li>
                                                <button type="button" class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteEmployeeModal"
                                                    data-delete-url="{{ route('admin.employees.destroy', $employee) }}"
                                                    data-employee-name="{{ $employee->name }}"
                                                    style="padding: 0.45rem 0.75rem; font-size: 0.85rem; border-radius: 6px;">
                                                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .75.72v5.06a.75.75 0 0 1-1.5 0V8.44a.75.75 0 0 1 .75-.72Zm3.34 0a.75.75 0 0 1 .75.72v5.06a.75.75 0 0 1-1.5 0V8.44a.75.75 0 0 1 .75-.72Z" clip-rule="evenodd" /></svg>
                                                    <span class="text-danger fw-medium">Delete</span>
                                                </button>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No employees found.
                                @can('employees.create')
                                    <div class="mt-2">
                                        <a href="{{ route('admin.employees.create') }}" class="btn btn-sm btn-primary">Add the first employee</a>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap 5 Delete Confirmation Modal -->
    <div class="modal fade" id="deleteEmployeeModal" tabindex="-1" aria-labelledby="deleteEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger d-inline-flex align-items-center gap-2" id="deleteEmployeeModalLabel">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .75.72v5.06a.75.75 0 0 1-1.5 0V8.44a.75.75 0 0 1 .75-.72Zm3.34 0a.75.75 0 0 1 .75.72v5.06a.75.75 0 0 1-1.5 0V8.44a.75.75 0 0 1 .75-.72Z" clip-rule="evenodd" /></svg>
                        Delete Employee
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="mb-0 text-secondary" style="font-size: 0.925rem;">Are you sure you want to delete <strong id="deleteEmployeeName" class="text-dark"></strong>? This action cannot be undone from the system UI.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm px-3 fw-medium" data-bs-dismiss="modal" style="border-radius: 6px;">Cancel</button>
                    <form id="deleteEmployeeForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm px-3 fw-medium" style="border-radius: 6px;">Confirm Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteModal = document.getElementById('deleteEmployeeModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', (event) => {
                    const button = event.relatedTarget;
                    const deleteUrl = button.getAttribute('data-delete-url');
                    const employeeName = button.getAttribute('data-employee-name');

                    deleteModal.querySelector('#deleteEmployeeForm').action = deleteUrl;
                    deleteModal.querySelector('#deleteEmployeeName').textContent = employeeName;
                });
            }

            if (window.jQuery && $.fn.DataTable) {
                const table = $('#employees-table').DataTable({
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    ordering: true,
                    columnDefs: [
                        { orderable: false, targets: [-1] }
                    ],
                    layout: {
                        topStart: null,
                        topEnd: null,
                        bottomStart: 'info',
                        bottomEnd: ['pageLength', 'paging']
                    },
                    language: {
                        search: "",
                        searchPlaceholder: "Search...",
                        lengthMenu: "Per page _MENU_",
                        info: "Showing _START_ to _END_ of _TOTAL_ results"
                    }
                });

                // Move per-page length control into center cell of the single bottom row
                const dtWrapper = $('#employees-table').closest('.dt-container');
                const bottomRow = dtWrapper.find('.dt-layout-row').last();
                const lengthEl = bottomRow.find('.dt-length');
                const pagingEl = bottomRow.find('.dt-paging');
                if (lengthEl.length && pagingEl.length) {
                    $('<div class="dt-layout-cell dt-layout-full d-flex justify-content-center flex-fill"></div>')
                        .append(lengthEl)
                        .insertBefore(pagingEl.closest('.dt-layout-cell'));
                }

                // Add mb-2 class to pagination count container (.dt-layout-end)
                dtWrapper.find('.dt-layout-end').addClass('mb-2');

                $('#employeeSearchInput').on('keyup input', function() {
                    table.search(this.value).draw();
                });

                $('#filterStatusSelect').on('change', function() {
                    const val = this.value ? '^' + this.value + '$' : '';
                    table.column(4).search(val, true, false).draw();
                    updateActiveFiltersCount();
                });

                $('#filterRoleSelect').on('change', function() {
                    const val = this.value ? '^' + this.value + '$' : '';
                    table.column(3).search(val, true, false).draw();
                    updateActiveFiltersCount();
                });

                $('#filterCitySelect').on('change', function() {
                    const val = this.value;
                    table.column(2).search(val).draw();
                    updateActiveFiltersCount();
                });

                $('#resetFiltersBtn').on('click', function() {
                    $('#filterStatusSelect').val('').trigger('change');
                    $('#filterRoleSelect').val('').trigger('change');
                    $('#filterCitySelect').val('').trigger('change');
                    table.columns().search('').draw();
                    updateActiveFiltersCount();
                });

                function updateActiveFiltersCount() {
                    let count = 0;
                    if ($('#filterStatusSelect').val()) count++;
                    if ($('#filterRoleSelect').val()) count++;
                    if ($('#filterCitySelect').val()) count++;
                    $('#activeFilterBadge').text(count);
                }
            }
        });
    </script>
</x-admin-layout>
