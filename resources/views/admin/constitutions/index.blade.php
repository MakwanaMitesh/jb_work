<x-admin-bootstrap-layout title="Customer Constitutions" :white-bg="true">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.constitutions.index') }}">Constitutions</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">List</span>
        </nav>

        <div class="d-flex align-items-center justify-content-between">
            <h1 class="jb-page-title">Customer Constitutions</h1>
            @can('constitutions.create')
                <a href="{{ route('admin.constitutions.create') }}" class="btn btn-primary">New Constitution</a>
            @endcan
        </div>
    </div>

    <x-datatable-card-bs tableId="constitutions-table" searchPlaceholder="Search">
        <x-slot:filters>
            <div>
                <label class="jb-form-label">Status</label>
                <select data-filter-column="2" data-filter-type="regex" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
        </x-slot:filters>

        <x-slot:thead>
            <th>Name</th>
            <th>Code</th>
            <th data-col="status">Status</th>
            <th>Order</th>
            <th class="text-end" style="width:4rem"></th>
        </x-slot:thead>

        @forelse ($constitutions as $constitution)
            <tr>
                <td>
                    <a href="{{ route('admin.constitutions.show', $constitution) }}" class="fw-semibold text-decoration-none">{{ $constitution->name }}</a>
                </td>
                <td><span class="font-monospace">{{ $constitution->code }}</span></td>
                <td data-col="status">
                    @if ($constitution->status === 'active')
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
                <td>{{ $constitution->sort_order }}</td>
                <td class="text-end">
                    <div class="dropdown">
                        <button class="jb-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" /></svg>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end text-start" style="width:11rem">
                            @can('constitutions.edit')
                                <li>
                                    <a href="{{ route('admin.constitutions.edit', $constitution) }}" class="dropdown-item">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        <span>Edit</span>
                                    </a>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('admin.constitutions.toggle-status', $constitution) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item">
                                            @if ($constitution->status === 'active')
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
                        </ul>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-5" style="color:var(--slate-400)">No customer constitutions found.</td>
            </tr>
        @endforelse
    </x-datatable-card-bs>

    <div class="mt-3">
        {{ $constitutions->links('pagination::bootstrap-5') }}
    </div>
</x-admin-bootstrap-layout>
