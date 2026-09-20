<x-admin-bootstrap-layout title="Banks" :white-bg="true">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.banks.index') }}">Banks</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">List</span>
        </nav>

        <div class="d-flex align-items-center justify-content-between">
            <h1 class="jb-page-title">Banks Master</h1>
            <a href="{{ route('admin.banks.create') }}" class="btn btn-primary">New Bank</a>
        </div>
    </div>

    <x-datatable-card-bs tableId="banks-table" searchPlaceholder="Search Bank Name">
        <x-slot:filters>
            <div>
                <label class="jb-form-label">Status</label>
                <select data-filter-column="1" data-filter-type="regex" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
        </x-slot:filters>

        <x-slot:thead>
            <th>Bank Name</th>
            <th data-col="status">Status</th>
            <th>Order</th>
            <th class="text-end" style="width:4rem"></th>
        </x-slot:thead>

        @forelse ($banks as $bank)
            <tr>
                <td class="jb-cell-strong">{{ $bank->name }}</td>
                <td data-col="status">
                    @if ($bank->status === 'active')
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
                <td class="fw-semibold">{{ $bank->sort_order }}</td>
                <td class="text-end">
                    <div class="dropdown">
                        <button type="button" class="jb-icon-btn" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end text-start" style="width:12rem">
                            <li>
                                <a href="{{ route('admin.banks.edit', $bank) }}" class="dropdown-item">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Edit</span>
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('admin.banks.toggle-status', $bank) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="dropdown-item">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                        <span>Toggle Status</span>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('admin.banks.destroy', $bank) }}" onsubmit="return confirm('Are you sure you want to delete this Bank?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item dropdown-item-danger">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center py-5" style="color:var(--slate-500)">
                    No banks found.
                </td>
            </tr>
        @endforelse
    </x-datatable-card-bs>
</x-admin-bootstrap-layout>
