<x-admin-bootstrap-layout title="Cities" :white-bg="true">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.cities.index') }}">Cities</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">List</span>
        </nav>

        <div class="d-flex align-items-center justify-content-between">
            <h1 class="jb-page-title">Cities</h1>
            @can('city.create')
                <a href="{{ route('admin.cities.create') }}" class="btn btn-primary">New city</a>
            @endcan
        </div>
    </div>

    <x-datatable-card-bs tableId="cities-table" searchPlaceholder="Search">
        <x-slot:filters>
            <div>
                <label class="jb-form-label">Status</label>
                <select data-filter-column="1" data-filter-type="regex" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="Active" @selected(request('status') === 'active')>Active</option>
                    <option value="Inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
        </x-slot:filters>

        <x-slot:thead>
            <th>Name</th>
            <th data-col="status">Active</th>
            <th class="text-end" style="width:4rem"></th>
        </x-slot:thead>

        @forelse ($cities as $city)
            <tr>
                <td class="jb-cell-strong">{{ $city->name }}</td>
                <td data-col="status">
                    @if ($city->isActive())
                        <span class="jb-badge jb-badge-emerald">
                            <span class="jb-dot jb-dot-emerald"></span>
                            Active
                        </span>
                    @else
                        <span class="jb-badge jb-badge-slate">
                            <span class="jb-dot jb-dot-slate"></span>
                            Inactive
                        </span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="dropdown">
                        <button class="jb-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" /></svg>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end text-start" style="width:11rem">
                            @can('city.edit')
                                <li>
                                    <a href="{{ route('admin.cities.edit', $city) }}" class="dropdown-item">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        <span>Edit</span>
                                    </a>
                                </li>
                            @endcan

                            @can('city.activate')
                                <li>
                                    <form method="POST" action="{{ route('admin.cities.toggle-status', $city) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item">
                                            @if ($city->isActive())
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                <span>Deactivate</span>
                                            @else
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Activate</span>
                                            @endif
                                        </button>
                                    </form>
                                </li>
                            @endcan

                            @can('city.delete')
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button type="button" class="dropdown-item dropdown-item-danger"
                                        data-delete-btn
                                        data-delete-url="{{ route('admin.cities.destroy', $city) }}">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Delete</span>
                                    </button>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center py-5" style="color:var(--slate-400)">No cities found.</td>
            </tr>
        @endforelse
    </x-datatable-card-bs>

    <x-confirmation-modal-bs
        id="deleteCityModal"
        title="Delete"
        message="Are you sure you want to delete? This action cannot be undone from the system UI."
        icon="trash"
        confirmText="Delete"
        confirmButtonClass="btn-danger"
        formId="deleteCityForm"
        method="DELETE"
    />

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteModal = document.getElementById('deleteCityModal');
            const deleteCityForm = document.getElementById('deleteCityForm');

            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-delete-btn]');
                if (btn) {
                    const deleteUrl = btn.getAttribute('data-delete-url');
                    deleteCityForm.action = deleteUrl;
                    deleteModal.classList.remove('jb-hidden');
                }
            });
        });
    </script>
</x-admin-bootstrap-layout>
