<x-admin-bootstrap-layout title="Roles" :white-bg="true">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="#">Roles</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">List</span>
        </nav>

        <div class="d-flex align-items-center justify-content-between">
            <h1 class="jb-page-title">Roles</h1>
            @can('create', App\Models\Role::class)
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>New role</span>
                </a>
            @endcan
        </div>
    </div>

    <x-datatable-card-bs tableId="roles-table" searchPlaceholder="Search">
        <x-slot:filters>
            <div>
                <label class="jb-form-label">Status</label>
                <select data-filter-column="4" data-filter-type="regex" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
        </x-slot:filters>

        <x-slot:thead>
            <th>Name</th>
            <th>Description</th>
            <th>Permissions</th>
            <th>Users</th>
            <th data-col="status">Active</th>
            <th class="text-end" style="width:4rem"></th>
        </x-slot:thead>

        @foreach ($roles as $role)
            <tr>
                <td class="jb-cell-strong">{{ $role->name }}</td>
                <td>{{ $role->description ?? '—' }}</td>
                <td>
                    <span class="jb-badge jb-badge-sm jb-badge-slate">{{ $role->permissions_count }} permissions</span>
                </td>
                <td>
                    <span class="jb-badge jb-badge-sm jb-badge-slate">{{ $role->users_count }} users</span>
                </td>
                <td data-col="status">
                    @if ($role->is_active)
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
                        <ul class="dropdown-menu dropdown-menu-end text-start" style="width:10rem">
                            <li>
                                <a href="{{ route('admin.roles.edit', $role) }}" class="dropdown-item">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <span>Edit</span>
                                </a>
                            </li>
                            @if (! $role->isProtected())
                                <li>
                                    <form method="POST" action="{{ route('admin.roles.toggle-status', $role) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item {{ $role->is_active ? 'dropdown-item-danger' : 'dropdown-item-success' }}">
                                            @if ($role->is_active)
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                <span>Deactivate</span>
                                            @else
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
    </x-datatable-card-bs>
</x-admin-bootstrap-layout>
