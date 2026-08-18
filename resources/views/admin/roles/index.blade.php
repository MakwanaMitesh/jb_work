<x-admin-layout title="Roles" :white-bg="true">
    <div class="mb-6">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500 text-xs font-medium mb-1.5">
            <a href="#" class="hover:text-slate-600 dark:hover:text-slate-300">Roles</a>
            <svg class="w-3.5 h-3.5 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="text-slate-900 dark:text-slate-200">List</span>
        </nav>

        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Roles</h1>
            @can('create', App\Models\Role::class)
                <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>New role</span>
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
                <input type="text" id="roleSearchInput" placeholder="Search" value="{{ request('search') }}" autocomplete="off" class="bg-transparent border-0 outline-none text-sm w-full text-slate-900 dark:text-slate-100 placeholder-slate-400">
            </div>

            <!-- Filter Dropdown -->
            <div class="relative inline-block text-left" id="filterDropdownContainer">
                <button type="button" id="filterDropdownBtn" class="relative w-10 h-10 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition">
                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 0 1 .628.74v2.288a2.25 2.25 0 0 1-.659 1.59l-4.682 4.683a2.25 2.25 0 0 0-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 0 1 8 18.25v-5.757a2.25 2.25 0 0 0-.659-1.59L2.659 6.22A2.25 2.25 0 0 1 2 4.629V2.34a.75.75 0 0 1 .628-.74Z" clip-rule="evenodd" /></svg>
                    <span id="activeFilterBadge" class="absolute -top-1 -right-1 flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-950 px-1 text-[10px] font-bold border border-white dark:border-slate-900 hidden">0</span>
                </button>

                <div id="filterDropdownMenu" class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg p-4 hidden z-40">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Status</label>
                            <select id="filterStatusSelect" class="form-select w-full border border-slate-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900">
                                <option value="">All</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
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
            <table class="w-full text-left border-collapse" id="roles-table">
                <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800">
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Permissions</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Users</th>
                        <th data-col="status" class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active</th>
                        <th class="px-6 py-3.5 text-right w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($roles as $role)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition duration-150">
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $role->name }}</span>
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ $role->description ?? '—' }}</td>
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700/50">
                                    {{ $role->permissions_count }} permissions
                                </span>
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700/50">
                                    {{ $role->users_count }} users
                                </span>
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap" data-col="status">
                                @if ($role->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-500 border border-emerald-200/30 dark:border-emerald-900/30">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" /></svg>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-right">
                                <div class="relative inline-block text-left" data-kebab-container>
                                    <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 flex items-center justify-center transition" type="button" data-kebab-btn>
                                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" /></svg>
                                    </button>
                                    <div class="absolute right-0 mt-1 w-40 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg p-1 hidden z-40" data-kebab-menu>
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-md no-underline">
                                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            <span>Edit</span>
                                        </a>
                                        @if (! $role->isProtected())
                                            <form method="POST" action="{{ route('admin.roles.toggle-status', $role) }}" class="block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm {{ $role->is_active ? 'text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20' : 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/20' }} rounded-md text-left">
                                                    @if ($role->is_active)
                                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                        <span>Deactivate</span>
                                                    @else
                                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <span>Activate</span>
                                                    @endif
                                                </button>
                                            </form>
                                        @endif
                                    </div>
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
                    const val = this.value ? '\\b' + this.value + '\\b' : '';
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
                    const badge = $('#activeFilterBadge');
                    if (count > 0) {
                        badge.text(count).removeClass('hidden');
                    } else {
                        badge.addClass('hidden');
                    }
                }
            }
        });
    </script>
</x-admin-layout>
