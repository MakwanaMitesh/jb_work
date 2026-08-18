@php
    $activeFilters = collect(['role'])->filter(fn ($key) => filled(request($key)))->count();
@endphp

<x-admin-layout title="Users" :white-bg="true">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Users</h1>
            <p class="text-muted small mb-0">Manage individual permission overrides for login-enabled users.</p>
        </div>
        @can('employees.view')
            <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">Go to Employee Management</a>
        @endcan
    </div>

    <div class="jb-card p-0 mb-4">
        <form method="GET" action="{{ route('admin.users.index') }}">
            <div class="jb-toolbar p-3">
                <div class="jb-search-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="search" placeholder="Search name or email..." value="{{ request('search') }}">
                </div>

                <div class="dropdown">
                    <button class="btn btn-outline-secondary d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <x-icon name="filter" :size="14" />
                        Filter @if ($activeFilters) <span class="badge rounded-pill bg-primary ms-1">{{ $activeFilters }}</span> @endif
                    </button>
                    <div class="dropdown-menu p-3 shadow-sm" style="min-width: 220px;">
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">Role</label>
                            <select name="role" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach ($roles as $roleName)
                                    <option value="{{ $roleName }}" @selected(request('role') === $roleName)>{{ $roleName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">Apply</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                        </div>
                    </div>
                </div>

                <button type="submit" class="d-none">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table datatable align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Email</th>
                        <th>Role(s)</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $user->name }}</td>
                            <td class="text-muted small">{{ $user->email }}</td>
                            <td>
                                @forelse ($user->roles as $role)
                                    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">{{ $role->name }}</span>
                                @empty
                                    <span class="text-muted small">No role assigned</span>
                                @endforelse
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.users.permissions.edit', $user) }}" class="btn btn-sm btn-outline-secondary">
                                    Manage Permissions
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination-bar :paginator="$users" label="users" />
    </div>
</x-admin-layout>
