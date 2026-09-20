@php
    $activeFilters = collect(['role'])->filter(fn ($key) => filled(request($key)))->count();
@endphp

<x-admin-bootstrap-layout title="Users" :white-bg="true">
    <div class="mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3">
        <div>
            <h1 class="jb-page-title">Users</h1>
            <p class="m-0 mt-1" style="font-size:var(--text-sm);color:var(--slate-500)">Manage individual permission overrides for login-enabled users.</p>
        </div>
        @can('employees.view')
            <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
                Go to Employee Management
            </a>
        @endcan
    </div>

    <div class="jb-card mb-4">
        <form method="GET" action="{{ route('admin.users.index') }}">
            <div class="jb-card-header d-flex align-items-center justify-content-end gap-2">
                <div class="jb-search">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--slate-400);flex-shrink:0"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" /></svg>
                    <input type="text" name="search" placeholder="Search name or email..." value="{{ request('search') }}">
                </div>

                <div class="dropdown">
                    <button type="button" class="jb-filter-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 0 1 .628.74v2.288a2.25 2.25 0 0 1-.659 1.59l-4.682 4.683a2.25 2.25 0 0 0-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 0 1 8 18.25v-5.757a2.25 2.25 0 0 0-.659-1.59L2.659 6.22A2.25 2.25 0 0 1 2 4.629V2.34a.75.75 0 0 1 .628-.74Z" clip-rule="evenodd" /></svg>
                        @if ($activeFilters)
                            <span class="jb-filter-badge">{{ $activeFilters }}</span>
                        @endif
                    </button>

                    <div class="dropdown-menu dropdown-menu-end p-3" style="width:16rem">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <label class="jb-form-label">Role</label>
                                <select name="role" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    @foreach ($roles as $roleName)
                                        <option value="{{ $roleName }}" @selected(request('role') === $roleName)>{{ $roleName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex gap-2 pt-1">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill">Apply</button>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm flex-fill">Clear</a>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="jb-hidden">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="jb-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role(s)</th>
                        <th class="text-end" style="width:4rem"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="jb-cell-strong">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @forelse ($user->roles as $role)
                                    <span class="jb-badge jb-badge-sm jb-badge-primary">{{ $role->name }}</span>
                                @empty
                                    <span class="jb-muted">No role assigned</span>
                                @endforelse
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.users.permissions.edit', $user) }}" class="btn btn-secondary btn-sm">
                                    Manage Permissions
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5" style="color:var(--slate-400)">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination-bar-bs :paginator="$users" label="users" />
    </div>
</x-admin-bootstrap-layout>
