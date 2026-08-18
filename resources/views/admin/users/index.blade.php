@php
    $activeFilters = collect(['role'])->filter(fn ($key) => filled(request($key)))->count();
@endphp

<x-admin-layout title="Users" :white-bg="true">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Users</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Manage individual permission overrides for login-enabled users.</p>
        </div>
        @can('employees.view')
            <a href="{{ route('admin.employees.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                Go to Employee Management
            </a>
        @endcan
    </div>

    <!-- Table Card Container -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden mb-8">
        <form method="GET" action="{{ route('admin.users.index') }}">
            <!-- Toolbar -->
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2 bg-slate-50/50 dark:bg-slate-900/50">
                <!-- Search -->
                <div class="w-60 h-10 px-3 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-lg flex items-center gap-2">
                    <svg class="text-slate-400 w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" /></svg>
                    <input type="text" name="search" placeholder="Search name or email..." value="{{ request('search') }}" class="bg-transparent border-0 outline-none text-sm w-full text-slate-900 dark:text-slate-100 placeholder-slate-400">
                </div>

                <!-- Filter Dropdown -->
                <div class="relative inline-block text-left" id="filterDropdownContainer">
                    <button type="button" id="filterDropdownBtn" class="relative w-10 h-10 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 0 1 .628.74v2.288a2.25 2.25 0 0 1-.659 1.59l-4.682 4.683a2.25 2.25 0 0 0-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 0 1 8 18.25v-5.757a2.25 2.25 0 0 0-.659-1.59L2.659 6.22A2.25 2.25 0 0 1 2 4.629V2.34a.75.75 0 0 1 .628-.74Z" clip-rule="evenodd" /></svg>
                        @if ($activeFilters)
                            <span class="absolute -top-1 -right-1 flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-950 px-1 text-[10px] font-bold border border-white dark:border-slate-900">
                                {{ $activeFilters }}
                            </span>
                        @endif
                    </button>

                    <div id="filterDropdownMenu" class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg p-4 hidden z-40">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Role</label>
                                <select name="role" class="form-select w-full border border-slate-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900">
                                    <option value="">All</option>
                                    @foreach ($roles as $roleName)
                                        <option value="{{ $roleName }}" @selected(request('role') === $roleName)>{{ $roleName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex gap-2 pt-2">
                                <button type="submit" class="inline-flex justify-center px-4 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-500 rounded-lg shadow-sm flex-1">Apply</button>
                                <a href="{{ route('admin.users.index') }}" class="inline-flex justify-center px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-sm flex-1 no-underline">Clear</a>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="hidden">Search</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800">
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Role(s)</th>
                        <th class="px-6 py-3.5 text-right w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition duration-150">
                            <td class="px-6 py-4.5 whitespace-nowrap font-semibold text-slate-900 dark:text-white">{{ $user->name }}</td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</td>
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                @forelse ($user->roles as $role)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-500 border border-amber-200/50 dark:border-amber-900/30">{{ $role->name }}</span>
                                @empty
                                    <span class="text-xs text-slate-400">No role assigned</span>
                                @endforelse
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-right">
                                <a href="{{ route('admin.users.permissions.edit', $user) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm no-underline">
                                    Manage Permissions
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-slate-400 py-12">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination-bar :paginator="$users" label="users" />
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
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
        });
    </script>
</x-admin-layout>
