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
                <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>New role</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- Unified DataTable Card -->
    <x-datatable-card tableId="roles-table" searchPlaceholder="Search">
        <x-slot:filters>
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Status</label>
                <select data-filter-column="4" data-filter-type="regex" class="form-select w-full border border-slate-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900">
                    <option value="">All</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
        </x-slot:filters>

        <x-slot:thead>
            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Description</th>
            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Permissions</th>
            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Users</th>
            <th data-col="status" class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active</th>
            <th class="px-6 py-3.5 text-right w-16"></th>
        </x-slot:thead>

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
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>
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
    </x-datatable-card>

</x-admin-layout>
