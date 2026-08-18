@php
    $navIcon = function (string $path) {
        return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">'.$path.'</svg>';
    };
@endphp

<div class="jb-sidebar-brand">
    <a href="{{ route('dashboard') }}" class="jb-logo">
        <span class="jb-logo-mark">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 7l10 5 10-5-10-5z" fill="currentColor" opacity="0.9"/>
                <path d="M2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </span>
        {{ config('app.name') }}
    </a>
</div>

<nav class="jb-sidebar-nav">
    <a href="{{ route('dashboard') }}" class="jb-sidebar-link @if (request()->routeIs('dashboard')) active @endif">
        {!! $navIcon('<path d="M3 10.5 12 3l9 7.5" /><path d="M5 9.5V21h14V9.5" />') !!}
        Dashboard
    </a>

    <div class="jb-sidebar-section">Management</div>

    @can('employees.view')
        <a href="{{ route('admin.employees.index') }}" class="jb-sidebar-link @if (request()->routeIs('admin.employees.*')) active @endif">
            {!! $navIcon('<circle cx="9" cy="8" r="3.2" /><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6" /><path d="M16.5 9.5a2.6 2.6 0 1 0 0-5.2 2.6 2.6 0 0 0 0 5.2Z" /><path d="M15 14c2.8.4 5 2.7 5 6" />') !!}
            Employees
        </a>
    @endcan

    <a href="#" class="jb-sidebar-link disabled text-muted" style="pointer-events:none; opacity:.5;" title="Coming soon">
        {!! $navIcon('<path d="M17 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 20v-2a4 4 0 0 0-3-3.9" /><path d="M16 3.1a4 4 0 0 1 0 7.8" />') !!}
        Agents
    </a>
</nav>

<div class="jb-sidebar-nav pt-0" style="flex: 0 0 auto;">
    @can('viewAny', App\Models\Role::class)
        <div class="jb-sidebar-section">Settings</div>
        <a href="{{ route('admin.roles.index') }}" class="jb-sidebar-link @if (request()->routeIs('admin.roles.*')) active @endif">
            {!! $navIcon('<path d="M12 3 4 6v6c0 4.8 3.4 7.9 8 9 4.6-1.1 8-4.2 8-9V6l-8-3Z" /><path d="m9.5 12 1.8 1.8L15 10" />') !!}
            Employee Roles
        </a>
    @endcan
</div>

<div class="jb-sidebar-footer">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="jb-sidebar-link jb-logout-link border-0 bg-transparent w-100 text-start">
            {!! $navIcon('<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><path d="M16 17l5-5-5-5" /><path d="M21 12H9" />') !!}
            Logout
        </button>
    </form>
</div>
