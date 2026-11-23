{{--
    "Lead Transfer" section. Expects $lead, $transferPanel (LeadController::transferPanelData()) and
    $transferMode: 'inline' when rendered inside the Edit page's main form (Transfer submits that form,
    so unsaved changes are saved first), 'standalone' on the detail page (its own small form).

    Every rule shown here is enforced again server-side by LeadWorkflow::transfer().
--}}
@php
    $tp = $transferPanel;
    // Create page: there is no lead yet — the section rides along in the create form, and the
    // button creates the lead and transfers it in one go (LeadController::store()).
    $creating = ! empty($tp['creating']);
    $inline = $creating || ($transferMode ?? 'standalone') === 'inline';
    $isFinal = $tp['stage'] && ! $tp['next'];
    $statusBadge = ['completed' => ['jb-badge-emerald', 'Completed'], 'in_progress' => ['jb-badge-amber', 'In Progress'], 'pending' => ['jb-badge-rose', 'Pending']];
    // On the detail page nothing can change before submitting, so an incomplete stage can't transfer.
    // On the Edit page the user may have just filled the missing fields in — the server re-checks after saving.
    $blockedByProgress = ! $inline && ! $tp['isComplete'];
@endphp

@if (($tp['configured'] || $tp['completed']) && (! $creating || $tp['canTransfer']))
<div class="jb-card mb-4" style="overflow:hidden" id="lead-transfer">
    <div class="jb-card-header d-flex align-items-center justify-content-between gap-2">
        <h3 class="jb-section-title m-0">Lead Transfer</h3>
        @if ($tp['completed'])
            <span class="jb-badge jb-badge-emerald">Workflow Completed</span>
        @elseif ($tp['lastTransfer'])
            <span class="jb-muted" style="font-size:var(--text-xs)">Last moved {{ $tp['lastTransfer']->transferred_at->format('d M Y, h:i A') }}</span>
        @endif
    </div>

    <div class="p-4">
        @if ($tp['completed'])
            <p class="m-0" style="font-size:var(--text-sm)">
                Every stage of this lead's workflow is complete.
                @if ($tp['lastTransfer']?->transferrer)
                    Completed by <strong>{{ $tp['lastTransfer']->transferrer->name }}</strong> on {{ $tp['lastTransfer']->transferred_at->format('d M Y, h:i A') }}.
                @endif
            </p>
        @elseif ($tp['stage'])
            <div class="row g-3 mb-3">
                <div class="col-6 col-lg-3">
                    <span class="jb-detail-label">Current Stage</span>
                    <span class="jb-detail-value">{{ $tp['stage']->name }}</span>
                </div>
                <div class="col-6 col-lg-3">
                    <span class="jb-detail-label">Current Role</span>
                    <span class="jb-detail-value">{{ $tp['stage']->role?->name }}</span>
                </div>
                <div class="col-6 col-lg-3">
                    <span class="jb-detail-label">Current Assigned User</span>
                    <span class="jb-detail-value">
                        @if ($creating)
                            {{ $tp['creatorName'] ?? 'Unassigned (until transferred)' }}
                        @else
                            {{ $lead->assignedEmployee?->name ?? 'Unassigned' }}
                        @endif
                    </span>
                </div>
                <div class="col-6 col-lg-3">
                    <span class="jb-detail-label">Next Role</span>
                    <span class="jb-detail-value">
                        @if ($tp['next'])
                            {{ $tp['next']->role?->name }} <span class="jb-muted fw-normal">({{ $tp['next']->name }})</span>
                        @else
                            Completed <span class="jb-muted fw-normal">(final stage)</span>
                        @endif
                    </span>
                </div>
            </div>

            <div class="mb-3">
                <span class="jb-detail-label mb-1">Tabs to complete in this stage</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($tp['progress'] as $tab)
                        <span class="jb-badge jb-badge-sm {{ $statusBadge[$tab['status']][0] }}">
                            {{ $tab['label'] }} · {{ $statusBadge[$tab['status']][1] }}
                        </span>
                    @endforeach
                </div>
                @unless ($tp['isComplete'])
                    <p class="jb-form-help m-0 mt-2">
                        Every tab must be <strong>Completed</strong> before the lead can be transferred.
                        @if ($creating) Create &amp; Transfer saves the lead first, then checks — if anything is missing, the lead stays with you as a draft.
                        @elseif ($inline) Fill in the remaining fields — Transfer saves your changes first, then checks again. @endif
                    </p>
                @endunless
            </div>

            @if ($tp['canTransfer'])
                @unless ($inline)
                    <form method="POST" action="{{ route('admin.leads.transfer', $lead) }}" id="lead-transfer-form">
                        @csrf
                @endunless
                        @unless ($creating)
                            <input type="hidden" name="transfer_stage_id" value="{{ $tp['stage']->id }}">
                        @endunless
                        @if ($inline)
                            <input type="hidden" name="workflow_action" value="" id="lead-transfer-action">
                        @endif

                        <div class="row g-3 align-items-end">
                            @if ($tp['next'])
                                <div class="col-12 col-md-5">
                                    <x-input-label-bs for="transfer_to_user_id" value="Select Next User" :required="true" />
                                    <select id="transfer_to_user_id" name="transfer_to_user_id" class="form-select jb-input" @disabled($tp['nextUsers']->isEmpty())>
                                        <option value="">{{ $tp['nextUsers']->isEmpty() ? 'No active user in ' . $tp['next']->role?->name : 'Select User' }}</option>
                                        @foreach ($tp['nextUsers'] as $nextUser)
                                            <option value="{{ $nextUser->id }}" @selected((int) old('transfer_to_user_id') === $nextUser->id)>{{ $nextUser->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-12 {{ $tp['next'] ? 'col-md-4' : 'col-md-8' }}">
                                <x-input-label-bs for="transfer_remarks" value="Remarks (optional)" />
                                <x-text-input-bs id="transfer_remarks" type="text" name="transfer_remarks" :value="old('transfer_remarks')" maxlength="1000" placeholder="Anything the next person should know" />
                            </div>
                            <div class="col-12 col-md-3 d-grid">
                                <button type="button" id="lead-transfer-btn" class="btn btn-success jb-btn-tall"
                                    data-final="{{ $isFinal ? '1' : '0' }}"
                                    @disabled($blockedByProgress || ($tp['next'] && ! old('transfer_to_user_id')))
                                    @if ($blockedByProgress) title="Complete every tab of this stage first" @endif>
                                    @if ($creating)
                                        {{ $isFinal ? 'Create & Complete Workflow' : 'Create & Transfer to Next Role' }}
                                    @else
                                        {{ $isFinal ? 'Complete Workflow' : 'Transfer to Next Role' }}
                                    @endif
                                </button>
                            </div>
                        </div>
                @unless ($inline)
                    </form>
                @endunless

                @php
                    $confirmTitle = $creating
                        ? ($isFinal ? 'Create and complete workflow?' : 'Create and transfer lead?')
                        : ($isFinal ? 'Complete workflow?' : 'Transfer lead?');
                    $confirmText = $isFinal
                        ? ($creating ? 'This creates the lead and finishes its workflow.' : 'This finishes the workflow for this lead. It cannot be undone by you.')
                        : ($creating
                            ? 'Are you sure you want to create and transfer this lead to the selected user (' . $tp['next']->role?->name . ')? Once transferred, you will no longer be able to edit this lead.'
                            : 'The lead will move to ' . $tp['next']->name . ' and you will no longer be able to edit it.');
                @endphp
                <x-confirmation-modal-bs id="lead-transfer-modal"
                    :title="$confirmTitle"
                    :message="e($confirmText)"
                    :confirmText="$creating ? ($isFinal ? 'Create & Complete' : 'Create & Transfer') : ($isFinal ? 'Complete' : 'Transfer')" confirmButtonClass="btn-success" />

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const btn = document.getElementById('lead-transfer-btn');
                        const select = document.getElementById('transfer_to_user_id');
                        const modal = document.getElementById('lead-transfer-modal');
                        const confirmBtn = document.getElementById('lead-transfer-modal-confirm-btn');
                        const form = {{ $inline ? "btn.closest('form')" : "document.getElementById('lead-transfer-form')" }};
                        const blocked = {{ $blockedByProgress ? 'true' : 'false' }};
                        if (!btn || !form) return;

                        const sync = () => { btn.disabled = blocked || (select ? !select.value : false); };
                        if (select) select.addEventListener('change', sync);
                        sync();

                        btn.addEventListener('click', () => {
                            if (btn.disabled) return;
                            @if ($creating && ! $isFinal)
                                // Name the chosen person in the confirmation, e.g. "to Harsh Bank (Bank Processing)".
                                const chosen = select.options[select.selectedIndex].text.trim();
                                document.getElementById('lead-transfer-modal-message').textContent =
                                    'Are you sure you want to create and transfer this lead to ' + chosen + ' (' + @json($tp['next']->role?->name) + ')? '
                                    + 'Once transferred, you will no longer be able to edit this lead.';
                            @endif
                            modal.classList.remove('jb-hidden');
                        });

                        confirmBtn.addEventListener('click', () => {
                            // One submission only — a second click must not send a second transfer.
                            confirmBtn.disabled = true;
                            btn.disabled = true;
                            @if ($inline)
                                document.getElementById('lead-transfer-action').value = 'transfer';
                            @endif
                            modal.classList.add('jb-hidden');
                            if (form.requestSubmit) { form.requestSubmit(); } else { form.submit(); }
                            // If the browser's own validation stopped the submit, let them try again.
                            setTimeout(() => { confirmBtn.disabled = false; sync(); }, 1500);
                        });
                    });
                </script>
            @else
                <div class="jb-alert jb-alert-info m-0">
                    Only {{ $lead->assignedEmployee ? $lead->assignedEmployee->name . ' (the assigned user)' : 'a user of the ' . $tp['stage']->role?->name . ' role' }} can transfer this lead from this stage.
                </div>
            @endif
        @else
            <p class="jb-muted m-0" style="font-size:var(--text-sm)">No active workflow stage.</p>
        @endif
    </div>
</div>
@endif
