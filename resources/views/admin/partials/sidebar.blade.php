@php
    $navIcon = function (string $path) {
        return '<svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">'.$path.'</svg>';
    };
@endphp

<div class="h-16 flex items-center px-6 border-b border-slate-100 dark:border-slate-800 shrink-0">
    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2.5 font-bold text-lg text-slate-900 dark:text-white no-underline">
        <span class="w-9 h-9 rounded-lg bg-gradient-to-tr from-primary-500 to-orange-600 flex items-center justify-center text-white shrink-0 shadow-sm shadow-primary-500/20">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 7l10 5 10-5-10-5z" fill="currentColor" opacity="0.9"/>
                <path d="M2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </span>
        <span class="tracking-tight">{{ config('app.name') }}</span>
    </a>
</div>

<div class="flex-1 overflow-y-auto px-4 py-4 space-y-6">
    <div class="space-y-1">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('dashboard') ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/20 dark:text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
            {!! $navIcon('<path d="M3 10.5 12 3l9 7.5" /><path d="M5 9.5V21h14V9.5" />') !!}
            <span>Dashboard</span>
        </a>
    </div>

    <div class="space-y-1.5">
        <div class="px-3 text-[0.7rem] font-bold tracking-wider text-slate-400 dark:text-slate-500 uppercase">Management</div>

        @can('employees.view')
            <a href="{{ route('admin.employees.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.employees.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/20 dark:text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                {!! $navIcon('<circle cx="9" cy="8" r="3.2" /><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6" /><path d="M16.5 9.5a2.6 2.6 0 1 0 0-5.2 2.6 2.6 0 0 0 0 5.2Z" /><path d="M15 14c2.8.4 5 2.7 5 6" />') !!}
                <span>Employees</span>
            </a>
        @endcan

        @can('agent.view')
            <a href="{{ route('admin.agents.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.agents.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/20 dark:text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                {!! $navIcon('<path d="M17 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 20v-2a4 4 0 0 0-3-3.9" /><path d="M16 3.1a4 4 0 0 1 0 7.8" />') !!}
                <span>Agents</span>
            </a>
        @endcan

        @can('leads.view')
            <a href="{{ route('admin.leads.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.leads.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/20 dark:text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                {!! $navIcon('<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />') !!}
                <span>Leads</span>
            </a>
        @endcan
    </div>

    <div class="space-y-1.5">
        <div class="px-3 text-[0.7rem] font-bold tracking-wider text-slate-400 dark:text-slate-500 uppercase">Settings</div>
        @can('viewAny', App\Models\Role::class)
            <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.roles.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/20 dark:text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                {!! $navIcon('<path d="M12 3 4 6v6c0 4.8 3.4 7.9 8 9 4.6-1.1 8-4.2 8-9V6l-8-3Z" /><path d="m9.5 12 1.8 1.8L15 10" />') !!}
                <span>Employee Roles</span>
            </a>
        @endcan
        @can('city.view')
            <a href="{{ route('admin.cities.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.cities.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/20 dark:text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                {!! $navIcon('<path d="M12 2L2 7l10 5 10-5-10-5z" /><path d="M2 17l10 5 10-5M2 12l10 5 10-5" />') !!}
                <span>Cities</span>
            </a>
        @endcan
    </div>
</div>

<div class="p-4 border-t border-slate-100 dark:border-slate-800 shrink-0">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/20 w-100 text-start">
            {!! $navIcon('<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><path d="M16 17l5-5-5-5" /><path d="M21 12H9" />') !!}
            <span>Logout</span>
        </button>
    </form>
</div>
