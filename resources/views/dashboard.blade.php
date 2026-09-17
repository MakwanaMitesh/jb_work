<x-admin-layout title="Dashboard">
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Dashboard</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Welcome back, {{ auth()->user()->name }} 👋</p>
    </div>

    @if (isset($expiredInsurances) && $expiredInsurances->isNotEmpty())
        <div class="mb-6 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200/50 dark:border-amber-900/30 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <div>
                    <h4 class="text-sm font-bold text-amber-900 dark:text-amber-300">Insurance Policies Expired</h4>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">{{ $expiredInsurances->count() }} active employee(s) have expired insurance policies.</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('insuranceExpiryModal').classList.remove('hidden')" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                View Employees
            </button>
        </div>

        <!-- Insurance Expiry Modal -->
        <div id="insuranceExpiryModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/40" onclick="document.getElementById('insuranceExpiryModal').classList.add('hidden')"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="relative z-10 inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-800">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Expired Insurance Policies</h3>
                        <button type="button" onclick="document.getElementById('insuranceExpiryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4 max-h-96 overflow-y-auto">
                        @foreach ($expiredInsurances as $emp)
                            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $emp->name }}</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Expired: {{ $emp->insurance_end_date->format('d M Y') }}</p>
                                </div>
                                <a href="{{ route('admin.employees.edit', $emp) }}" class="px-3 py-1.5 bg-primary-50 dark:bg-primary-950/20 text-primary-700 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/30 text-xs font-semibold rounded-lg transition no-underline">
                                    Renew / Edit
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Card 1 -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5 flex items-center gap-4">
            <span class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </span>
            <div>
                <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Employees</span>
                <span class="block text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['total_employees'] }}</span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5 flex items-center gap-4">
            <span class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div>
                <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Active Employees</span>
                <span class="block text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['active_employees'] }}</span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5 flex items-center gap-4">
            <span class="w-10 h-10 rounded-lg bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div>
                <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Inactive Employees</span>
                <span class="block text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['inactive_employees'] }}</span>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5 flex items-center gap-4">
            <span class="w-10 h-10 rounded-lg bg-primary-50 dark:bg-primary-950/30 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            </span>
            <div>
                <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Roles</span>
                <span class="block text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['total_roles'] }}</span>
            </div>
        </div>
    </div>

    <!-- Secondary Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Breakdown Chart/Progress -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 lg:col-span-7">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Employees per Role</h3>
            @if ($roleBreakdown->isEmpty())
                <p class="text-sm text-slate-400">No roles yet.</p>
            @else
                <div class="space-y-4">
                    @foreach ($roleBreakdown as $role)
                        @php $percent = $stats['total_employees'] > 0 ? round(($role->users_count / $stats['total_employees']) * 100) : 0; @endphp
                        <div>
                            <div class="flex justify-between items-center text-sm mb-1.5">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $role->name }}</span>
                                <span class="text-xs text-slate-400 font-bold bg-slate-50 dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/50 px-2 py-0.5 rounded-full">{{ $role->users_count }}</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2">
                                <div class="bg-primary-500 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Recent Employees -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 lg:col-span-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Recently Added</h3>
                @can('employees.view')
                    <a href="{{ route('admin.employees.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-500 no-underline">View All</a>
                @endcan
            </div>

            @if ($recentEmployees->isEmpty())
                <p class="text-sm text-slate-400">No employees yet.</p>
            @else
                <div class="space-y-3">
                    @foreach ($recentEmployees as $employee)
                        <div class="flex items-center justify-between p-2 rounded-lg border border-slate-50 dark:border-slate-800 hover:border-slate-100 dark:hover:border-slate-700 transition">
                            <div class="flex items-center gap-3">
                                @if ($employee->profilePhotoUrl())
                                    <img src="{{ $employee->profilePhotoUrl() }}" alt="" class="w-8 h-8 rounded-full object-cover shrink-0">
                                @else
                                    <span class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs font-bold shrink-0 border border-slate-200 dark:border-slate-700">{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
                                @endif
                                <div>
                                    <span class="block text-xs font-bold text-slate-900 dark:text-white">{{ $employee->name }}</span>
                                    <span class="block text-[10px] text-slate-400 mt-0.5">{{ $employee->roles->first()->name ?? '—' }}</span>
                                </div>
                            </div>
                            <div>
                                @if ($employee->isActive())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-500 border border-emerald-200/30 dark:border-emerald-900/30">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">Inactive</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
