<x-admin-layout title="Roles" :white-bg="true">
    {{-- Top Header Section --}}
    <div class="mb-4">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0 small" style="font-size: 0.82rem; font-weight: 500;">
                <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-secondary">Roles</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">List</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="fw-bold mb-0" style="font-size: 1.75rem; color: #111827; letter-spacing: -0.02em;">Roles</h1>
            </div>
            @can('create', App\Models\Role::class)
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3.5 py-2 fw-semibold shadow-sm" style="background-color: #2563eb; border-color: #2563eb; border-radius: 0.5rem; font-size: 0.875rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>New role</span>
                </a>
            @endcan
        </div>
    </div>

    {{-- Main Table Card Container --}}
    <div class="jb-card p-0 mb-5 shadow-sm" style="border-radius: 12px; overflow: hidden; background: #ffffff; border: 1px solid #e5e7eb; margin-bottom: 2.5rem;">
        {{-- Search and Circular Filter Bar (Right-Aligned) --}}
        <div class="jb-toolbar p-3 d-flex align-items-center justify-content-end gap-2" style="background: #ffffff; border-bottom: 1px solid #f3f4f6;">
            <!-- Search Input Pill with Heroicon Magnifying Glass -->
            <div class="jb-search-box rounded-3" style="background: #fff; width: 240px; height: 38px; padding: 0.4rem 0.75rem; border: 1px solid #d1d5db; display: flex; align-items: center; gap: 0.5rem; border-radius: 8px;">
                <svg class="text-muted" width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" /></svg>
                <input type="text" id="roleSearchInput" placeholder="Search" value="{{ request('search') }}" autocomplete="off" style="border: 0; outline: none; background: transparent; width: 100%; font-size: 0.875rem; color: #111827;">
            </div>

            <!-- Circular Filter Button with Heroicon Funnel -->
            <div class="dropdown">
                <button class="jb-icon-btn position-relative" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-auto-close="outside" aria-expanded="false" title="Filter roles" style="width: 38px; height: 38px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #d1d5db; background: #fff; color: #6b7280;">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 0 1 .628.74v2.288a2.25 2.25 0 0 1-.659 1.59l-4.682 4.683a2.25 2.25 0 0 0-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 0 1 8 18.25v-5.757a2.25 2.25 0 0 0-.659-1.59L2.659 6.22A2.25 2.25 0 0 1 2 4.629V2.34a.75.75 0 0 1 .628-.74Z" clip-rule="evenodd" /></svg>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark border border-light" id="activeFilterBadge" style="padding: 0.18rem 0.35rem; font-size: 0.58rem; transform: translate(-30%, -20%) !important; display: none;">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-3 shadow-sm" style="min-width: 260px; border-radius: 8px;">
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select id="filterStatusSelect" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" id="resetFiltersBtn" class="btn btn-outline-secondary btn-sm flex-fill">Clear Filters</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table datatable align-middle mb-0" id="roles-table">
                <thead>
                    <tr style="background: #ffffff; border-bottom: 1px solid #e5e7eb;">
                        <th class="ps-4" style="border-bottom: 1px solid #e5e7eb; color: #4b5563; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">Name</th>
                        <th style="border-bottom: 1px solid #e5e7eb; color: #4b5563; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">Description</th>
                        <th style="border-bottom: 1px solid #e5e7eb; color: #4b5563; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">Permissions</th>
                        <th style="border-bottom: 1px solid #e5e7eb; color: #4b5563; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">Users</th>
                        <th data-col="status" style="border-bottom: 1px solid #e5e7eb; color: #4b5563; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">Active</th>
                        <th class="text-end pe-4" style="width: 60px; border-bottom: 1px solid #e5e7eb;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr style="border-bottom: 1px solid #f3f4f6; background-color: #ffffff;">
                            <td class="ps-4">
                                <span class="fw-semibold text-dark">{{ $role->name }}</span>
                            </td>
                            <td class="text-secondary small">{{ $role->description ?? '—' }}</td>
                            <td>
                                <span class="badge rounded-pill bg-light text-dark border px-2.5 py-1" style="font-weight: 500;">
                                    {{ $role->permissions_count }} permissions
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-light text-dark border px-2.5 py-1" style="font-weight: 500;">
                                    {{ $role->users_count }} users
                                </span>
                            </td>
                            <td>
                                @if ($role->is_active)
                                    <span class="badge rounded-pill bg-success-subtle text-success d-inline-flex align-items-center gap-1.5 px-2.5 py-1" style="font-weight: 500; font-size: 0.78rem;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        <span>Active</span>
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary d-inline-flex align-items-center gap-1.5 px-2.5 py-1" style="font-weight: 500; font-size: 0.78rem;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                        <span>Inactive</span>
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-link text-secondary p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 8px;">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.roles.edit', $role) }}">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                <span>Edit</span>
                                            </a>
                                        </li>
                                        @if (! $role->isProtected())
                                            <li>
                                                <form method="POST" action="{{ route('admin.roles.toggle-status', $role) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2 {{ $role->is_active ? 'text-danger' : 'text-success' }}">
                                                        @if ($role->is_active)
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                                            <span>Deactivate</span>
                                                        @else
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                            <span>Activate</span>
                                                        @endif
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.jQuery && $.fn.DataTable) {
                const table = $('#roles-table').DataTable({
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
                const dtWrapper = $('#roles-table').closest('.dt-container');
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

                $('#roleSearchInput').on('keyup input', function() {
                    table.search(this.value).draw();
                });

                $('#filterStatusSelect').on('change', function() {
                    const val = this.value ? '^' + this.value + '$' : '';
                    table.column(4).search(val, true, false).draw();
                    updateActiveFiltersCount();
                });

                $('#resetFiltersBtn').on('click', function() {
                    $('#filterStatusSelect').val('').trigger('change');
                    table.columns().search('').draw();
                    updateActiveFiltersCount();
                });

                function updateActiveFiltersCount() {
                    let count = 0;
                    if ($('#filterStatusSelect').val()) count++;
                    if (count > 0) {
                        $('#activeFilterBadge').text(count).show();
                    } else {
                        $('#activeFilterBadge').hide();
                    }
                }
            }
        });
    </script>
</x-admin-layout>
