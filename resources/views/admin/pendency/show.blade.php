<x-admin-bootstrap-layout title="Pendency — {{ $lead->name }}" :white-bg="true">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.pendency.index') }}">Pendency</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">{{ $lead->name }}</span>
        </nav>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h1 class="jb-page-title mb-0">{{ $lead->name }} <span class="text-muted" style="font-size:var(--text-base)">#{{ $lead->id }}</span></h1>
                <p class="jb-muted mb-0" style="font-size:var(--text-xs)">{{ $lead->city?->name }} @if($lead->assignedEmployee) &middot; Assigned to {{ $lead->assignedEmployee->name }} @endif</p>
            </div>
            @can('leads.edit')
                <a href="{{ route('admin.leads.edit', $lead) }}" class="btn btn-primary btn-sm">Open Lead</a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-4">
            <div class="jb-stat-tile h-100 p-3">
                <span class="jb-stat-label">Pending Items</span>
                <span class="d-block fw-bold mt-2" style="font-size:var(--text-2xl);color:var(--rose-600)">{{ $pending }}</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="jb-stat-tile h-100 p-3">
                <span class="jb-stat-label">Completed Items</span>
                <span class="d-block fw-bold mt-2" style="font-size:var(--text-2xl);color:var(--emerald-600)">{{ $completed }}</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="jb-stat-tile h-100 p-3">
                <span class="jb-stat-label">Modules With Pending Work</span>
                <span class="d-block fw-bold mt-2" style="font-size:var(--text-2xl);color:var(--amber-600)">{{ count(array_filter($modules, fn ($m) => ($m['pending_count'] ?? 0) > 0)) }}</span>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column gap-2">
        @foreach ($modules as $module)
            <div class="jb-card p-0 pendency-module">
                <button type="button" class="pendency-module-toggle w-100 d-flex align-items-center justify-content-between gap-2 p-3 border-0 bg-transparent text-start" aria-expanded="false">
                    <span class="d-flex align-items-center gap-2">
                        <span class="pendency-chevron">&#9656;</span>
                        <span class="fw-semibold">{{ $module['label'] }}</span>
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        @if (! $module['applicable'])
                            <span class="jb-badge jb-badge-xs jb-badge-slate">Not Applicable</span>
                        @else
                            @if ($module['pending_count'] > 0)
                                <span class="jb-badge jb-badge-xs jb-badge-rose">{{ $module['pending_count'] }} pending</span>
                            @endif
                            <span class="jb-badge jb-badge-xs jb-badge-emerald">{{ $module['completed_count'] }} complete</span>
                        @endif
                    </span>
                </button>
                <div class="pendency-module-body jb-hidden border-top">
                    @if (! $module['applicable'])
                        <p class="text-muted p-3 mb-0" style="font-size:var(--text-sm)">{{ $module['na_reason'] }}</p>
                    @elseif (empty($module['subsections']))
                        <p class="text-muted p-3 mb-0" style="font-size:var(--text-sm)">{{ $module['na_reason'] ?? 'Nothing to track here.' }}</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($module['subsections'] as $sub)
                                <li class="list-group-item d-flex align-items-center justify-content-between gap-2">
                                    <span class="d-flex align-items-center gap-2">
                                        @if ($sub['status'] === 'complete')
                                            <span class="pendency-dot pendency-dot--complete" title="Complete"></span>
                                        @elseif ($sub['status'] === 'na')
                                            <span class="pendency-dot pendency-dot--na" title="Not applicable"></span>
                                        @else
                                            <span class="pendency-dot pendency-dot--pending" title="Pending"></span>
                                        @endif
                                        <span>{{ $sub['label'] }}</span>
                                        @if ($sub['note'])
                                            <span class="text-muted" style="font-size:var(--text-xs)">— {{ $sub['note'] }}</span>
                                        @endif
                                    </span>
                                    @if ($sub['status'] === 'pending' && $sub['anchor'])
                                        <a href="{{ route('admin.leads.edit', $lead) }}{{ $sub['anchor'] }}" class="btn btn-outline-secondary btn-sm">Complete now</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <style>
        /* Scoped to this page's module cards only. */
        .pendency-chevron { display: inline-block; transition: transform .15s ease; }
        .pendency-module-toggle[aria-expanded="true"] .pendency-chevron { transform: rotate(90deg); }
        .pendency-dot { width: .6rem; height: .6rem; border-radius: 50%; display: inline-block; }
        .pendency-dot--complete { background: var(--emerald-600, #059669); }
        .pendency-dot--pending { background: var(--rose-600, #e11d48); }
        .pendency-dot--na { background: var(--slate-400, #94a3b8); }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.pendency-module-toggle').forEach(function (btn) {
                const body = btn.closest('.pendency-module').querySelector('.pendency-module-body');
                btn.addEventListener('click', function () {
                    const open = body.classList.toggle('jb-hidden') === false;
                    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            });
            // Open the first module that has pending work, so the user lands somewhere useful.
            const firstPendingModule = Array.from(document.querySelectorAll('.pendency-module')).find(function (m) {
                return m.querySelector('.jb-badge-rose');
            });
            if (firstPendingModule) firstPendingModule.querySelector('.pendency-module-toggle').click();
        });
    </script>
</x-admin-bootstrap-layout>
