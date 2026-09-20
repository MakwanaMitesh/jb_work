@props([
    'tableId' => 'datatable',
    'searchPlaceholder' => 'Search...',
])

<div class="jb-card mb-4" id="{{ $tableId }}-card">
    <div class="jb-card-header d-flex align-items-center justify-content-end gap-2">
        <div class="jb-search">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--slate-400);flex-shrink:0"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" /></svg>
            <input type="text" id="{{ $tableId }}-search-input" placeholder="{{ $searchPlaceholder }}" autocomplete="off">
        </div>

        @if (isset($filters))
            <div class="dropdown">
                <button type="button" id="{{ $tableId }}-filter-btn" class="jb-filter-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 0 1 .628.74v2.288a2.25 2.25 0 0 1-.659 1.59l-4.682 4.683a2.25 2.25 0 0 0-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 0 1 8 18.25v-5.757a2.25 2.25 0 0 0-.659-1.59L2.659 6.22A2.25 2.25 0 0 1 2 4.629V2.34a.75.75 0 0 1 .628-.74Z" clip-rule="evenodd" /></svg>
                    <span id="{{ $tableId }}-filter-badge" class="jb-filter-badge jb-hidden">0</span>
                </button>

                <div class="dropdown-menu dropdown-menu-end p-3" style="width:16rem">
                    <div class="d-flex flex-column gap-3">
                        {{ $filters }}
                        <div class="pt-1">
                            <button type="button" id="{{ $tableId }}-clear-filters-btn" class="btn btn-sm w-100" style="background:var(--slate-100);color:var(--slate-700);font-size:.75rem">
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="table-responsive" style="min-height:200px">
        <table class="jb-table" id="{{ $tableId }}">
            <thead>
                <tr>
                    {{ $thead }}
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tableId = '{{ $tableId }}';
        const card = document.getElementById(tableId + '-card');
        const searchInput = document.getElementById(tableId + '-search-input');
        const clearBtn = document.getElementById(tableId + '-clear-filters-btn');

        // Filter dropdown is handled natively by Bootstrap (data-bs-toggle="dropdown").

        if (window.jQuery && $.fn.DataTable) {
            $.fn.dataTable.ext.errMode = 'none';
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

            if (searchInput) {
                $(searchInput).on('keyup input', function () {
                    table.search(this.value).draw();
                });
            }

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
                        badge.classList.remove('jb-hidden');
                    } else {
                        badge.classList.add('jb-hidden');
                    }
                }
            };

            filterInputs.forEach(input => {
                $(input).on('change', function () {
                    const colIndex = parseInt(this.getAttribute('data-filter-column'));
                    const isRegex = this.getAttribute('data-filter-type') === 'regex';
                    const val = this.value ? (isRegex ? '\\b' + this.value + '\\b' : this.value) : '';

                    table.column(colIndex).search(val, isRegex, !isRegex).draw();
                    updateBadge();
                });
            });

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
