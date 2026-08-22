@props([
    'tableId' => 'datatable',
    'searchPlaceholder' => 'Search...',
])

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm mb-8" id="{{ $tableId }}-card">
    <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2 bg-slate-50/50 dark:bg-slate-900/50">
        <!-- Search -->
        <div class="w-60 h-10 px-3 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-lg flex items-center gap-2">
            <svg class="text-slate-400 w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" /></svg>
            <input type="text" id="{{ $tableId }}-search-input" placeholder="{{ $searchPlaceholder }}" autocomplete="off" class="bg-transparent border-0 outline-none text-sm w-full text-slate-900 dark:text-slate-100 placeholder-slate-400">
        </div>

        @if (isset($filters))
            <!-- Filter Dropdown -->
            <div class="relative inline-block text-left" id="{{ $tableId }}-filter-container">
                <button type="button" id="{{ $tableId }}-filter-btn" class="relative w-10 h-10 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition">
                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 0 1 .628.74v2.288a2.25 2.25 0 0 1-.659 1.59l-4.682 4.683a2.25 2.25 0 0 0-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 0 1 8 18.25v-5.757a2.25 2.25 0 0 0-.659-1.59L2.659 6.22A2.25 2.25 0 0 1 2 4.629V2.34a.75.75 0 0 1 .628-.74Z" clip-rule="evenodd" /></svg>
                    <span id="{{ $tableId }}-filter-badge" class="absolute -top-1 -right-1 flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-950 px-1 text-[10px] font-bold border border-white dark:border-slate-900 hidden">0</span>
                </button>

                <div id="{{ $tableId }}-filter-menu" class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg p-4 hidden z-40">
                    <div class="space-y-4">
                        {{ $filters }}
                        <div class="pt-2">
                            <button type="button" id="{{ $tableId }}-clear-filters-btn" class="w-full inline-flex items-center justify-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg">
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="overflow-x-auto min-h-[200px]">
        <table class="w-full text-left border-collapse" id="{{ $tableId }}">
            <thead>
                <tr class="bg-slate-50/75 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800">
                    {{ $thead }}
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tableId = '{{ $tableId }}';
        const card = document.getElementById(tableId + '-card');
        const filterBtn = document.getElementById(tableId + '-filter-btn');
        const filterMenu = document.getElementById(tableId + '-filter-menu');
        const searchInput = document.getElementById(tableId + '-search-input');
        const clearBtn = document.getElementById(tableId + '-clear-filters-btn');

        // Dropdown toggle logic
        if (filterBtn && filterMenu) {
            filterBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                filterMenu.classList.toggle('hidden');
            });
            document.addEventListener('click', (e) => {
                if (!filterMenu.contains(e.target) && e.target !== filterBtn) {
                    filterMenu.classList.add('hidden');
                }
            });
        }

        // DataTable Initialization
        if (window.jQuery && $.fn.DataTable) {
            const tableElement = $('#' + tableId);
            const table = tableElement.DataTable({
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

            // Adjust single row paging positions
            const dtWrapper = tableElement.closest('.dt-container');
            const bottomRow = dtWrapper.find('.dt-layout-row').last();
            const lengthEl = bottomRow.find('.dt-length');
            const pagingEl = bottomRow.find('.dt-paging');
            if (lengthEl.length && pagingEl.length) {
                $('<div class="dt-layout-cell dt-layout-full d-flex justify-content-center flex-fill"></div>')
                    .append(lengthEl)
                    .insertBefore(pagingEl.closest('.dt-layout-cell'));
            }
            dtWrapper.find('.dt-layout-end').addClass('mb-2');

            // Hook up Search Input
            if (searchInput) {
                $(searchInput).on('keyup input', function() {
                    table.search(this.value).draw();
                });
            }

            // Hook up filters dynamically
            const filterInputs = card.querySelectorAll('[data-filter-column]');
            
            const updateBadge = () => {
                let count = 0;
                filterInputs.forEach(input => {
                    if (input.value) count++;
                });
                const badge = document.getElementById(tableId + '-filter-badge');
                if (badge) {
                    if (count > 0) {
                        badge.textContent = count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            };

            filterInputs.forEach(input => {
                $(input).on('change', function() {
                    const colIndex = parseInt(this.getAttribute('data-filter-column'));
                    const isRegex = this.getAttribute('data-filter-type') === 'regex';
                    const val = this.value ? (isRegex ? '\\b' + this.value + '\\b' : this.value) : '';
                    
                    table.column(colIndex).search(val, isRegex, !isRegex).draw();
                    updateBadge();
                });
            });

            // Clear filters logic
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    filterInputs.forEach(input => {
                        $(input).val('').trigger('change');
                    });
                    table.columns().search('').draw();
                    updateBadge();
                });
            }
        }
    });
</script>
