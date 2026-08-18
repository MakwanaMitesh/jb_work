<x-admin-layout :title="$agent->name">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            @if ($agent->profilePhotoUrl())
                <img src="{{ $agent->profilePhotoUrl() }}" alt="" class="w-14 h-14 rounded-full object-cover shrink-0">
            @else
                @php
                    $initials = '';
                    if ($agent->first_name && $agent->last_name) {
                        $initials = strtoupper(substr($agent->first_name, 0, 1) . substr($agent->last_name, 0, 1));
                    } else {
                        $initials = strtoupper(substr($agent->first_name ?? $agent->email, 0, 2));
                    }
                @endphp
                <span class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-lg font-bold shrink-0 border border-slate-200 dark:border-slate-700">{{ $initials }}</span>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-slate-950 dark:text-white leading-tight">{{ $agent->name }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $agent->email }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @can('agent.edit')
                <a href="{{ route('admin.agents.edit', $agent) }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold rounded-lg shadow-sm transition no-underline">
                    Edit
                </a>
            @endcan
            <a href="{{ route('admin.agents.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm no-underline">
                Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Basic Information -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Basic Information</h3>
            <dl class="grid grid-cols-3 gap-y-3 text-sm">
                <dt class="text-slate-500 dark:text-slate-400 font-medium">First Name</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $agent->first_name ?: '—' }}</dd>
                <dt class="text-slate-500 dark:text-slate-400 font-medium">Last Name</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $agent->last_name ?: '—' }}</dd>
                <dt class="text-slate-500 dark:text-slate-400 font-medium">Mobile</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $agent->mobile_number ?: '—' }}</dd>
                <dt class="text-slate-500 dark:text-slate-400 font-medium">City</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $agent->city ?: '—' }}</dd>
                <dt class="text-slate-500 dark:text-slate-400 font-medium">Address</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $agent->address ?: '—' }}</dd>
            </dl>
        </div>

        <!-- Status -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Agent Status</h3>
            <dl class="grid grid-cols-3 gap-y-3 text-sm">
                <dt class="text-slate-500 dark:text-slate-400 font-medium">Status</dt>
                <dd class="col-span-2">
                    @if ($agent->isActive())
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-500 border border-emerald-200/30 dark:border-emerald-900/30">Active</span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">Inactive</span>
                    @endif
                </dd>
                <dt class="text-slate-500 dark:text-slate-400 font-medium">Created Date</dt>
                <dd class="col-span-2 text-slate-900 dark:text-white font-semibold">{{ $agent->created_at?->format('d M Y') ?? '—' }}</dd>
            </dl>
        </div>
    </div>
</x-admin-layout>
