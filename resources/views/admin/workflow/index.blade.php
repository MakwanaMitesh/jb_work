<x-admin-bootstrap-layout title="Lead Workflow" :white-bg="true">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="#">Lead Workflow</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">Stages</span>
        </nav>

        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="jb-page-title">Lead Workflow</h1>
                <p class="jb-muted m-0 mt-1" style="font-size:var(--text-sm)">
                    A lead moves through these stages in order. Each stage's role sees only its own tabs, fills them in, then transfers the lead to a user of the next stage's role.
                </p>
            </div>
            <a href="{{ route('admin.workflow.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 flex-shrink-0">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>New stage</span>
            </a>
        </div>
    </div>

    @if ($stages->isEmpty())
        <div class="jb-alert jb-alert-info mb-4">
            No workflow stages yet. Until you add one, leads are not routed between roles and tab access follows role permissions only.
        </div>
    @endif

    <div class="jb-card mb-4" style="overflow:hidden">
        <div class="table-responsive">
            <table class="jb-table">
                <thead>
                    <tr>
                        <th style="width:4.5rem">Step</th>
                        <th>Stage</th>
                        <th>Role &amp; Users</th>
                        <th>Tabs Filled In</th>
                        <th>Read-only Tabs</th>
                        <th>Next</th>
                        <th>Leads Here</th>
                        <th>Status</th>
                        <th class="text-end" style="width:4rem"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stages as $i => $stage)
                        @php $next = $stage->is_active ? $workflow->nextStage($stage) : null; @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="jb-badge jb-badge-slate">{{ $i + 1 }}</span>
                                    <div class="d-flex flex-column">
                                        <form method="POST" action="{{ route('admin.workflow.move', $stage) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="direction" value="up">
                                            <button type="submit" class="btn btn-link p-0 lh-1" title="Move up" @disabled($loop->first)>
                                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.workflow.move', $stage) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="direction" value="down">
                                            <button type="submit" class="btn btn-link p-0 lh-1" title="Move down" @disabled($loop->last)>
                                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                            <td class="jb-cell-strong">{{ $stage->name }}</td>
                            <td style="white-space:normal;min-width:12rem">
                                <div class="fw-semibold">{{ $stage->role?->name }}</div>
                                @if (! $stage->role?->is_active)
                                    <span class="jb-badge jb-badge-xs jb-badge-rose">Role inactive</span>
                                @endif
                                <div class="jb-muted" style="font-size:var(--text-xs)">
                                    @if ($roleUsers[$stage->id])
                                        {{ implode(', ', $roleUsers[$stage->id]) }}
                                    @else
                                        <span style="color:var(--rose-600)">No active users — assign this role under Employees.</span>
                                    @endif
                                </div>
                            </td>
                            <td style="white-space:normal;min-width:12rem">
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach (\App\Support\LeadTabs::labels($stage->modules) as $label)
                                        <span class="jb-badge jb-badge-sm jb-badge-indigo">{{ $label }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td style="white-space:normal">
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse (\App\Support\LeadTabs::labels($stage->readOnlyModules()) as $label)
                                        <span class="jb-badge jb-badge-sm jb-badge-slate">{{ $label }}</span>
                                    @empty
                                        <span class="jb-muted">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                @if (! $stage->is_active)
                                    <span class="jb-muted">Skipped</span>
                                @elseif ($next)
                                    {{ $next->name }}
                                    <div class="jb-muted" style="font-size:var(--text-xs)">{{ $next->role?->name }}</div>
                                @else
                                    <span class="jb-badge jb-badge-emerald">Completed</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.leads.index', ['workflow_stage_id' => $stage->id]) }}" class="jb-badge jb-badge-sm jb-badge-slate text-decoration-none">{{ $stage->active_leads_count }} leads</a>
                            </td>
                            <td>
                                @if ($stage->is_active)
                                    <span class="jb-badge jb-badge-emerald"><span class="jb-dot jb-dot-emerald"></span>Active</span>
                                @else
                                    <span class="jb-badge jb-badge-slate"><span class="jb-dot jb-dot-slate"></span>Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="jb-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" /></svg>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end text-start" style="width:11rem">
                                        <li>
                                            <a href="{{ route('admin.workflow.edit', $stage) }}" class="dropdown-item">
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                <span>Edit</span>
                                            </a>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.workflow.toggle-status', $stage) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="dropdown-item {{ $stage->is_active ? 'dropdown-item-danger' : 'dropdown-item-success' }}">
                                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $stage->is_active ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }}"/></svg>
                                                    <span>{{ $stage->is_active ? 'Deactivate' : 'Activate' }}</span>
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.workflow.destroy', $stage) }}" onsubmit="return confirm('Delete the stage &quot;{{ e($stage->name) }}&quot;? Its transfer history is kept.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item dropdown-item-danger">
                                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center jb-muted py-4">No stages configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="jb-card" style="overflow:hidden">
        <div class="jb-card-header">
            <h3 class="jb-section-title m-0">Recent Workflow Changes</h3>
        </div>
        <div class="p-3">
            @forelse ($logs as $log)
                <div class="d-flex flex-column flex-md-row justify-content-between gap-1 py-2 {{ $loop->last ? '' : 'border-bottom' }}" style="font-size:var(--text-sm)">
                    <span>{{ $log->description }}</span>
                    <span class="jb-muted flex-shrink-0" style="font-size:var(--text-xs)">{{ $log->user?->name ?? 'System' }} · {{ $log->created_at->format('d M Y, h:i A') }}</span>
                </div>
            @empty
                <p class="jb-muted m-0" style="font-size:var(--text-sm)">No changes recorded yet.</p>
            @endforelse
        </div>
    </div>
</x-admin-bootstrap-layout>
