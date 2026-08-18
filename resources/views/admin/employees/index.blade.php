@php
    $activeFilters = collect(['status', 'role', 'city'])->filter(fn ($key) => filled(request($key)))->count();
@endphp

<x-admin-layout title="Employees" :white-bg="true">
    <div class="mb-6">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500 text-xs font-medium mb-1.5">
            <a href="{{ route('admin.employees.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300">Employees</a>
            <svg class="w-3.5 h-3.5 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="text-slate-900 dark:text-slate-200">List</span>
        </nav>

        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Employees</h1>
            @can('employees.create')
                <a href="{{ route('admin.employees.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                    <span>New employee</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- Table Card Container -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm mb-8">
        <!-- Toolbar -->
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2 bg-slate-50/50 dark:bg-slate-900/50">
            <!-- Search -->
            <div class="w-60 h-10 px-3 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-lg flex items-center gap-2">
                <svg class="text-slate-400 w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" /></svg>
                <input type="text" id="employeeSearchInput" placeholder="Search" value="{{ request('search') }}" autocomplete="off" class="bg-transparent border-0 outline-none text-sm w-full text-slate-900 dark:text-slate-100 placeholder-slate-400">
            </div>

            <!-- Filter Dropdown -->
            <div class="relative inline-block text-left" id="filterDropdownContainer">
                <button type="button" id="filterDropdownBtn" class="relative w-10 h-10 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition">
                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 0 1 .628.74v2.288a2.25 2.25 0 0 1-.659 1.59l-4.682 4.683a2.25 2.25 0 0 0-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 0 1 8 18.25v-5.757a2.25 2.25 0 0 0-.659-1.59L2.659 6.22A2.25 2.25 0 0 1 2 4.629V2.34a.75.75 0 0 1 .628-.74Z" clip-rule="evenodd" /></svg>
                    <span id="activeFilterBadge" class="absolute -top-1 -right-1 flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-950 px-1 text-[10px] font-bold border border-white dark:border-slate-900">
                        {{ $activeFilters }}
                    </span>
                </button>

                <div id="filterDropdownMenu" class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg p-4 hidden z-40">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Status</label>
                            <select id="filterStatusSelect" class="form-select w-full border border-slate-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900">
                                <option value="">All</option>
                                <option value="Active" @selected(request('status') === 'active')>Active</option>
                                <option value="Inactive" @selected(request('status') === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Role</label>
                            <select id="filterRoleSelect" class="form-select w-full border border-slate-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900">
                                <option value="">All</option>
                                @foreach ($roles as $roleName)
                                    <option value="{{ $roleName }}" @selected(request('role') === $roleName)>{{ $roleName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">City</label>
                            <select id="filterCitySelect" class="form-select w-full border border-slate-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900">
                                <option value="">All</option>
                                @foreach ($cities as $cityName)
                                    <option value="{{ $cityName }}" @selected(request('city') === $cityName)>{{ $cityName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pt-2">
                            <button type="button" id="resetFiltersBtn" class="w-full inline-flex items-center justify-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg">
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="employees-table">
                <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800">
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
                        <th data-col="city" class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">City</th>
                        <th data-col="role" class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Role</th>
                        <th data-col="status" class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active</th>
                        <th data-col="joined" class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3.5 text-right w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($employees as $employee)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition duration-150">
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    @if ($employee->profilePhotoUrl())
                                        <img src="{{ $employee->profilePhotoUrl() }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0">
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
                                        <span class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs font-bold shrink-0 border border-slate-200 dark:border-slate-700">{{ $initials }}</span>
                                    @endif
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ $employee->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ $employee->email }}</td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400" data-col="city">{{ $employee->city ?? '—' }}</td>
                            <td class="px-6 py-4.5 whitespace-nowrap" data-col="role">
                                @php
                                    $roleName = $employee->roles->first()->name ?? 'Staff';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-primary-50 dark:bg-primary-950/20 text-primary-700 dark:text-primary-500 border border-primary-200/50 dark:border-primary-900/30">{{ $roleName }}</span>
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap" data-col="status">
                                @if ($employee->isActive())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-500 border border-emerald-200/30 dark:border-emerald-900/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400" data-col="joined" data-order="{{ $employee->joining_date?->timestamp ?? 0 }}">{{ $employee->joining_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-right">
                                <div class="relative inline-block text-left" data-kebab-container>
                                    <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 flex items-center justify-center transition" type="button" data-kebab-btn>
                                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" /></svg>
                                    </button>
                                    <div class="absolute right-0 mt-1 w-44 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg p-1 hidden z-40" data-kebab-menu>
                                        <a href="{{ route('admin.employees.show', $employee) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-md no-underline">
                                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>View profile</span>
                                        </a>

                                        @can('employees.edit')
                                            <a href="{{ route('admin.employees.edit', $employee) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-md no-underline">
                                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                <span>Edit</span>
                                            </a>
                                        @endcan

                                        @can('employees.activate')
                                            <form method="POST" action="{{ route('admin.employees.toggle-status', $employee) }}" class="block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm {{ $employee->isActive() ? 'text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20' : 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/20' }} rounded-md text-left">
                                                    @if ($employee->isActive())
                                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                        <span>Deactivate</span>
                                                    @else
                                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <span>Activate</span>
                                                    @endif
                                                </button>
                                            </form>
                                        @endcan

                                        @can('employees.delete')
                                            <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                                            <button type="button" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-md text-left"
                                                data-delete-btn
                                                data-delete-url="{{ route('admin.employees.destroy', $employee) }}"
                                                data-employee-name="{{ $employee->name }}">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Delete</span>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-400 py-12">
                                No employees found.
                                @can('employees.create')
                                    <div class="mt-2">
                                        <a href="{{ route('admin.employees.create') }}" class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-semibold rounded-lg shadow-sm">Add the first employee</a>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reusable Delete Confirmation Modal -->
    <x-confirmation-modal 
        id="deleteEmployeeModal"
        title="Delete Employee"
        message="Are you sure you want to delete <strong id='deleteEmployeeName' class='text-slate-950 dark:text-white font-semibold'></strong>? This action cannot be undone from the system UI."
        icon="trash"
        confirmText="Delete"
        confirmButtonClass="bg-red-600 hover:bg-red-500 focus:ring-red-500/20 text-white"
        formId="deleteEmployeeForm"
        method="DELETE"
    />

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Dropdown Menu Toggles (Circular Filters)
            const filterDropdownBtn = document.getElementById('filterDropdownBtn');
            const filterDropdownMenu = document.getElementById('filterDropdownMenu');

            if (filterDropdownBtn && filterDropdownMenu) {
                filterDropdownBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    filterDropdownMenu.classList.toggle('hidden');
                });
                document.addEventListener('click', (e) => {
                    if (!filterDropdownMenu.contains(e.target) && e.target !== filterDropdownBtn) {
                        filterDropdownMenu.classList.add('hidden');
                    }
                });
            }

            // Dropdown Menu Toggles (Kebab Actions)
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-kebab-btn]');
                if (btn) {
                    e.stopPropagation();
                    const container = btn.closest('[data-kebab-container]');
                    const menu = container.querySelector('[data-kebab-menu]');
                    
                    // Hide all other menus
                    document.querySelectorAll('[data-kebab-menu]').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    
                    menu.classList.toggle('hidden');
                } else {
                    document.querySelectorAll('[data-kebab-menu]').forEach(m => m.classList.add('hidden'));
                }
            });

            // Delete Modal JavaScript
            const deleteModal = document.getElementById('deleteEmployeeModal');
            const deleteEmployeeForm = document.getElementById('deleteEmployeeForm');
            const deleteEmployeeNameEl = document.getElementById('deleteEmployeeName');

            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-delete-btn]');
                if (btn) {
                    const deleteUrl = btn.getAttribute('data-delete-url');
                    const employeeName = btn.getAttribute('data-employee-name');
                    deleteEmployeeForm.action = deleteUrl;
                    deleteEmployeeNameEl.textContent = employeeName;
                    deleteModal.classList.remove('hidden');
                }
            });

            // DataTables 2 Initialization
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
                    const val = this.value ? '\\b' + this.value + '\\b' : '';
                    table.column(4).search(val, true, false).draw();
                    updateActiveFiltersCount();
                });

                $('#filterRoleSelect').on('change', function() {
                    const val = this.value ? '\\b' + this.value + '\\b' : '';
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
