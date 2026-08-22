<x-admin-layout title="Employees" :white-bg="true">
    <div class="mb-6">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500 text-xs font-medium mb-1.5">
            <a href="{{ route('admin.employees.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300">Employees</a>
            <svg class="w-3.5 h-3.5 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="text-slate-900 dark:text-slate-200">List</span>
        </nav>

        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Employees</h1>
            @can('employees.create')
                <a href="{{ route('admin.employees.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                    <span>New employee</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- Unified DataTable Card -->
    <x-datatable-card tableId="employees-table" searchPlaceholder="Search">
        <x-slot:filters>
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Status</label>
                <select data-filter-column="4" data-filter-type="regex" class="form-select w-full border border-slate-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900">
                    <option value="">All</option>
                    <option value="Active" @selected(request('status') === 'active')>Active</option>
                    <option value="Inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Role</label>
                <select data-filter-column="3" data-filter-type="regex" class="form-select w-full border border-slate-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900">
                    <option value="">All</option>
                    @foreach ($roles as $roleName)
                        <option value="{{ $roleName }}" @selected(request('role') === $roleName)>{{ $roleName }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">City</label>
                <select data-filter-column="2" class="form-select w-full border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500/20 text-sm h-10 px-3">
                    <option value="">All</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->name }}" @selected(request('city') === $city->name)>{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>

        <x-slot:thead>
            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
            <th data-col="city" class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">City</th>
            <th data-col="role" class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Role</th>
            <th data-col="status" class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active</th>
            <th data-col="joined" class="px-6 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Joined</th>
            <th class="px-6 py-3.5 text-right w-16"></th>
        </x-slot:thead>

        @forelse ($employees as $employee)
            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition duration-150">
                <td class="px-6 py-4.5 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                        @if ($employee->profilePhotoUrl())
                            <img src="{{ $employee->profilePhotoUrl() }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0">
                        @else
                            @php
                                $initials = '';
                                if ($employee->first_name && $employee->last_name) {
                                    $initials = strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1));
                                } else {
                                    $words = explode(' ', trim($employee->name));
                                    if (count($words) >= 2) {
                                        $initials = strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
                                    } else {
                                        $initials = strtoupper(substr($employee->name, 0, 2));
                                    }
                                }
                            @endphp
                            <span class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs font-bold shrink-0 border border-slate-200 dark:border-slate-700">{{ $initials }}</span>
                        @endif
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $employee->name }}</span>
                    </div>
                </td>
                <td class="px-6 py-4.5 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ $employee->email }}</td>
                <td class="px-6 py-4.5 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400" data-col="city">{{ $employee->city?->name ?? '—' }}</td>
                <td class="px-6 py-4.5 whitespace-nowrap" data-col="role">
                    @php
                        $roleName = $employee->roles->first()->name ?? 'Staff';
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-primary-50 dark:bg-primary-950/20 text-primary-700 dark:text-primary-500 border border-primary-200/50 dark:border-primary-900/30">{{ $roleName }}</span>
                </td>
                <td class="px-6 py-4.5 whitespace-nowrap" data-col="status">
                    @if ($employee->isActive())
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
                <td class="px-6 py-4.5 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400" data-col="joined" data-order="{{ $employee->joining_date?->timestamp ?? 0 }}">{{ $employee->joining_date?->format('M d, Y') ?? '—' }}</td>
                <td class="px-6 py-4.5 whitespace-nowrap text-right">
                    <div class="relative inline-block text-left" data-kebab-container>
                        <button class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 flex items-center justify-center transition" type="button" data-kebab-btn>
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" /></svg>
                        </button>
                        <div class="absolute right-0 mt-1 w-44 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg p-1 hidden z-40" data-kebab-menu>
                            <a href="{{ route('admin.employees.show', $employee) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-md no-underline">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>View profile</span>
                            </a>

                            @can('employees.edit')
                                <a href="{{ route('admin.employees.edit', $employee) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-md no-underline">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <span>Edit</span>
                                </a>
                            @endcan

                            @can('employees.activate')
                                <form method="POST" action="{{ route('admin.employees.toggle-status', $employee) }}" class="block">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm {{ $employee->isActive() ? 'text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20' : 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/20' }} rounded-md text-left">
                                        @if ($employee->isActive())
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            <span>Deactivate</span>
                                        @else
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Activate</span>
                                        @endif
                                    </button>
                                </form>
                            @endcan

                            @can('employees.delete')
                                <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                                <button type="button" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-md text-left"
                                    data-delete-btn
                                    data-delete-url="{{ route('admin.employees.destroy', $employee) }}">
                                    <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Delete</span>
                                </button>
                            @endcan
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td class="text-center text-slate-400 py-12">No employees found.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endforelse
    </x-datatable-card>

    <!-- Reusable Delete Confirmation Modal -->
    <x-confirmation-modal 
        id="deleteEmployeeModal"
        title="Delete"
        message="Are you sure you want to delete? This action cannot be undone from the system UI."
        icon="trash"
        confirmText="Delete"
        confirmButtonClass="bg-red-600 hover:bg-red-500 focus:ring-red-500/20 text-white"
        formId="deleteEmployeeForm"
        method="DELETE"
    />

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Delete Modal JavaScript
            const deleteModal = document.getElementById('deleteEmployeeModal');
            const deleteEmployeeForm = document.getElementById('deleteEmployeeForm');

            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-delete-btn]');
                if (btn) {
                    const deleteUrl = btn.getAttribute('data-delete-url');
                    deleteEmployeeForm.action = deleteUrl;
                    deleteModal.classList.remove('hidden');
                }
            });
        });
    </script>
</x-admin-layout>
