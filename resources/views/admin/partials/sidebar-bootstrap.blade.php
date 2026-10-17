@php
    $navIcon = function (string $path) {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">'.$path.'</svg>';
    };
@endphp

<div class="jb-sidebar-header">
    <a href="{{ route('dashboard') }}" class="jb-brand-link">
        <span class="jb-brand-mark">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M12 2L2 7l10 5 10-5-10-5z" fill="currentColor" opacity="0.9"/>
                <path d="M2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </span>
        <span>{{ config('app.name') }}</span>
    </a>
</div>

<div class="flex-grow-1 overflow-auto px-3 py-3">
    <div class="d-flex flex-column gap-1 mb-4">
        <a href="{{ route('dashboard') }}" class="jb-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            {!! $navIcon('<path d="M3 10.5 12 3l9 7.5" /><path d="M5 9.5V21h14V9.5" />') !!}
            <span>Dashboard</span>
        </a>
    </div>

    <div class="d-flex flex-column gap-1 mb-4">
        <div class="jb-nav-heading mb-1">Management</div>

        @can('employees.view')
            <a href="{{ route('admin.employees.index') }}" class="jb-nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                {!! $navIcon('<circle cx="9" cy="8" r="3.2" /><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6" /><path d="M16.5 9.5a2.6 2.6 0 1 0 0-5.2 2.6 2.6 0 0 0 0 5.2Z" /><path d="M15 14c2.8.4 5 2.7 5 6" />') !!}
                <span>Employees</span>
            </a>
        @endcan

        @can('agent.view')
            <a href="{{ route('admin.agents.index') }}" class="jb-nav-link {{ request()->routeIs('admin.agents.*') ? 'active' : '' }}">
                {!! $navIcon('<path d="M17 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 20v-2a4 4 0 0 0-3-3.9" /><path d="M16 3.1a4 4 0 0 1 0 7.8" />') !!}
                <span>Agents</span>
            </a>
        @endcan

        @can('leads.view')
            <a href="{{ route('admin.leads.index') }}" class="jb-nav-link {{ request()->routeIs('admin.leads.*') ? 'active' : '' }}">
                {!! $navIcon('<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />') !!}
                <span>Leads</span>
            </a>
        @endcan

        @can('rough_leads.view')
            <a href="{{ route('admin.rough-leads.index') }}" class="jb-nav-link {{ request()->routeIs('admin.rough-leads.*') ? 'active' : '' }}">
                {!! $navIcon('<path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />') !!}
                <span>Rough Leads</span>
            </a>
        @endcan

        @can('contact_enquiry.view')
            <a href="{{ route('admin.contact-enquiries.index') }}" class="jb-nav-link {{ request()->routeIs('admin.contact-enquiries.*') ? 'active' : '' }}">
                {!! $navIcon('<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />') !!}
                <span>Contact Enquiries</span>
            </a>
        @endcan
    </div>

    <div class="d-flex flex-column gap-1">
        <div class="jb-nav-heading mb-1">Settings</div>

        @can('viewAny', App\Models\Role::class)
            <a href="{{ route('admin.roles.index') }}" class="jb-nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                {!! $navIcon('<path d="M12 3 4 6v6c0 4.8 3.4 7.9 8 9 4.6-1.1 8-4.2 8-9V6l-8-3Z" /><path d="m9.5 12 1.8 1.8L15 10" />') !!}
                <span>Employee Roles</span>
            </a>
        @endcan

        @can('loan_products.view')
            <a href="{{ route('admin.loan-products.index') }}" class="jb-nav-link {{ request()->routeIs('admin.loan-products.*') ? 'active' : '' }}">
                {!! $navIcon('<path d="M12 2L2 7l10 5 10-5-10-5z" /><path d="M2 17l10 5 10-5" />') !!}
                <span>Loan Products</span>
            </a>
        @endcan

        @can('constitutions.view')
            <a href="{{ route('admin.constitutions.index') }}" class="jb-nav-link {{ request()->routeIs('admin.constitutions.*') ? 'active' : '' }}">
                {!! $navIcon('<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" />') !!}
                <span>Constitutions</span>
            </a>
        @endcan

        <a href="{{ route('admin.assessment-years.index') }}" class="jb-nav-link {{ request()->routeIs('admin.assessment-years.*') ? 'active' : '' }}">
            {!! $navIcon('<path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z" />') !!}
            <span>Assessment Years</span>
        </a>

        <a href="{{ route('admin.banks.index') }}" class="jb-nav-link {{ request()->routeIs('admin.banks.*') ? 'active' : '' }}">
            {!! $navIcon('<path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M8 10v11M12 10v11M16 10v11M20 10v11" />') !!}
            <span>Banks</span>
        </a>

        @can('document_types.view')
            <a href="{{ route('admin.document-types.index') }}" class="jb-nav-link {{ request()->routeIs('admin.document-types.*') ? 'active' : '' }}">
                {!! $navIcon('<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>') !!}
                <span>Document Master</span>
            </a>
        @endcan

        @can('city.view')
            <a href="{{ route('admin.cities.index') }}" class="jb-nav-link {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}">
                {!! $navIcon('<path d="M12 2L2 7l10 5 10-5-10-5z" /><path d="M2 17l10 5 10-5M2 12l10 5 10-5" />') !!}
                <span>Cities</span>
            </a>
        @endcan
    </div>
</div>

<div class="p-3 border-top flex-shrink-0">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="jb-logout-btn">
            {!! $navIcon('<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><path d="M16 17l5-5-5-5" /><path d="M21 12H9" />') !!}
            <span>Logout</span>
        </button>
    </form>
</div>
