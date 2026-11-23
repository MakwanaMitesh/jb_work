<?php

namespace App\Services;

use App\Exceptions\WorkflowException;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\LeadTransfer;
use App\Models\LeadWorkflowStage;
use App\Models\User;
use App\Support\LeadTabs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The single source of truth for the Admin-configured lead workflow (LeadWorkflowStage rows):
 * which stage a lead is in, who may see it, which of its tabs each user may see or edit, whether
 * the current stage's tabs are complete, and moving the lead on to the next stage's user.
 *
 * Three kinds of user, decided per request:
 *  - Super Admin: the Admin role or `leads.view_all`. Sees and edits everything, can reassign.
 *  - Workflow user: holds an active role that owns at least one workflow stage. Sees only leads
 *    assigned to them (or unassigned leads waiting in a stage their role owns), sees only the tabs
 *    their role's stages list, and may edit only the current stage's tabs on leads they hold.
 *  - Anyone else: the pre-workflow behaviour, unchanged (permission checks plus leads.view_own).
 */
class LeadWorkflow
{
    /** @var Collection<int, LeadWorkflowStage>|null */
    private ?Collection $stages = null;

    /** @var array<int, Collection<int, int>> */
    private array $roleIdsByUser = [];

    // ---------------------------------------------------------------- stages

    /**
     * Every stage, active or not, in workflow order.
     *
     * @return Collection<int, LeadWorkflowStage>
     */
    public function stages(): Collection
    {
        return $this->stages ??= LeadWorkflowStage::with('role')->ordered()->get();
    }

    /**
     * @return Collection<int, LeadWorkflowStage>
     */
    public function activeStages(): Collection
    {
        return $this->stages()->where('is_active', true)->values();
    }

    public function firstStage(): ?LeadWorkflowStage
    {
        return $this->activeStages()->first();
    }

    /**
     * The next *active* stage after $stage, or null when $stage is the final one.
     */
    public function nextStage(LeadWorkflowStage $stage): ?LeadWorkflowStage
    {
        return $this->activeStages()->first(
            fn (LeadWorkflowStage $s) => $s->sequence > $stage->sequence || ($s->sequence === $stage->sequence && $s->id > $stage->id)
        );
    }

    public function isCompleted(Lead $lead): bool
    {
        return $lead->workflow_status === Lead::WORKFLOW_COMPLETED;
    }

    /**
     * The stage $lead is in right now, or null once the workflow is complete (or not configured).
     * A lead with no stage recorded — created before any stage existed — waits in the first one.
     */
    public function stageFor(Lead $lead): ?LeadWorkflowStage
    {
        if ($this->isCompleted($lead)) {
            return null;
        }

        if ($lead->workflow_stage_id) {
            return $this->stages()->firstWhere('id', $lead->workflow_stage_id) ?? $this->firstStage();
        }

        return $this->firstStage();
    }

    public function deadlineFor(?LeadWorkflowStage $stage): ?Carbon
    {
        $days = $stage?->role?->sla_days;

        return $days ? now()->addDays($days) : null;
    }

    // ----------------------------------------------------------------- users

    public function isSuperAdmin(?User $user): bool
    {
        return $user !== null && ($user->hasRole('Admin') || $user->can('leads.view_all'));
    }

    /**
     * IDs of the user's roles that are switched on — a deactivated role grants nothing, here as
     * everywhere else (see User::getPermissionsViaRoles()).
     *
     * @return Collection<int, int>
     */
    public function activeRoleIds(User $user): Collection
    {
        return $this->roleIdsByUser[$user->id] ??= $user->roles->where('is_active', true)->pluck('id')->values();
    }

    /**
     * Stages (active or not) whose role the user holds. Inactive stages still count: a user must not
     * slip out of the workflow's restrictions just because their stage was switched off.
     *
     * @return Collection<int, LeadWorkflowStage>
     */
    public function stagesOf(User $user): Collection
    {
        $roleIds = $this->activeRoleIds($user);

        return $this->stages()->filter(fn (LeadWorkflowStage $s) => $roleIds->contains($s->role_id))->values();
    }

    public function isWorkflowUser(?User $user): bool
    {
        return $user !== null && ! $this->isSuperAdmin($user) && $this->stagesOf($user)->isNotEmpty();
    }

    public function holdsStageRole(?User $user, ?LeadWorkflowStage $stage): bool
    {
        return $user !== null && $stage !== null && $this->activeRoleIds($user)->contains($stage->role_id);
    }

    /**
     * Active users holding $stage's (active) role — the only people a lead may be handed to there.
     *
     * @return Collection<int, User>
     */
    public function eligibleUsers(?LeadWorkflowStage $stage): Collection
    {
        if (! $stage || ! $stage->role?->is_active) {
            return collect();
        }

        return User::query()
            ->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->whereKey($stage->role_id))
            ->orderBy('name')
            ->get();
    }

    // ---------------------------------------------------------------- access

    public function canAccessLead(?User $user, Lead $lead): bool
    {
        if (! $user) {
            return false;
        }
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($this->isWorkflowUser($user)) {
            if ($lead->assigned_employee_id !== null) {
                return $lead->assigned_employee_id === $user->id;
            }

            return ! $this->isCompleted($lead) && $this->holdsStageRole($user, $this->stageFor($lead));
        }

        // Pre-workflow behaviour: leads.view_own narrows to own leads; otherwise unrestricted.
        return ! $user->can('leads.view_own') || $lead->assigned_employee_id === $user->id;
    }

    /**
     * Whether $user may change $lead right now (edit its current-stage tabs, save, transfer).
     * A workflow user loses this the moment the lead moves on to the next stage.
     */
    public function canWorkOnLead(?User $user, Lead $lead): bool
    {
        if (! $this->canAccessLead($user, $lead)) {
            return false;
        }
        if (! $this->isWorkflowUser($user)) {
            return true;
        }

        return ! $this->isCompleted($lead) && $this->holdsStageRole($user, $this->stageFor($lead));
    }

    /**
     * Whether $user may hand $lead on to the next stage (or complete it, at the final stage).
     */
    public function canTransfer(?User $user, Lead $lead): bool
    {
        $stage = $this->stageFor($lead);
        if (! $stage || ! $this->canWorkOnLead($user, $lead)) {
            return false;
        }

        return $this->isSuperAdmin($user) || $this->holdsStageRole($user, $stage);
    }

    /**
     * Each Lead tab's access level for $user: 'edit', 'view' (read-only) or null (hidden — not even
     * rendered). $lead is null on the Create page, where a new lead is about to enter the first stage.
     *
     * @return array<string, 'edit'|'view'|null>
     */
    public function tabAccess(?User $user, ?Lead $lead = null): array
    {
        $access = array_fill_keys(LeadTabs::keys(), null);
        if (! $user) {
            return $access;
        }

        $workflowUser = $this->isWorkflowUser($user);
        $ownTabs = [];
        $editable = [];

        if ($workflowUser) {
            foreach ($this->stagesOf($user) as $stage) {
                $ownTabs = array_merge($ownTabs, $stage->editableModules(), $stage->view_modules ?? []);
            }

            $stage = $lead ? $this->stageFor($lead) : $this->firstStage();
            $mayEdit = $lead ? $this->canWorkOnLead($user, $lead) : $this->holdsStageRole($user, $stage);
            if ($stage && $mayEdit) {
                $editable = $stage->editableModules();
            }
        }

        foreach (LeadTabs::keys() as $key) {
            if (! $user->can("{$key}.view")) {
                continue;
            }
            if (! $workflowUser) {
                $access[$key] = 'edit';
                continue;
            }
            if (! in_array($key, $ownTabs, true)) {
                continue;
            }
            $access[$key] = in_array($key, $editable, true) ? 'edit' : 'view';
        }

        return $access;
    }

    /**
     * Whether the workflow lets $user use $module on $lead outside the form itself — reading its data
     * on the detail page, downloading its reports ($edit false), or generating / saving / deleting
     * ($edit true). Only workflow users are limited by tab here; everyone else keeps the existing
     * per-action permission checks unchanged (callers still check those).
     */
    public function tabAllows(?User $user, ?Lead $lead, string $module, bool $edit = false): bool
    {
        if (! $user || ($lead && ! $this->canAccessLead($user, $lead))) {
            return false;
        }
        if (! $this->isWorkflowUser($user)) {
            return true;
        }

        $access = $this->tabAccess($user, $lead)[$module] ?? null;

        return $edit ? $access === 'edit' : $access !== null;
    }

    /**
     * Narrows a Lead query to what $user may see — the same rule as canAccessLead().
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user || $this->isSuperAdmin($user)) {
            return $query;
        }

        if (! $this->isWorkflowUser($user)) {
            return $user->can('leads.view_own') ? $query->where('assigned_employee_id', $user->id) : $query;
        }

        $stageIds = $this->stagesOf($user)->pluck('id')->all();
        $holdsFirst = $this->holdsStageRole($user, $this->firstStage());

        return $query->where(function (Builder $q) use ($user, $stageIds, $holdsFirst) {
            $q->where('assigned_employee_id', $user->id)
                ->orWhere(function (Builder $pool) use ($stageIds, $holdsFirst) {
                    $pool->whereNull('assigned_employee_id')
                        ->where('workflow_status', Lead::WORKFLOW_IN_PROGRESS)
                        ->where(function (Builder $s) use ($stageIds, $holdsFirst) {
                            $s->whereIn('workflow_stage_id', $stageIds ?: [0]);
                            if ($holdsFirst) {
                                $s->orWhereNull('workflow_stage_id');
                            }
                        });
                });
        });
    }

    // ------------------------------------------------------------- progress

    /**
     * Completion of each of $stage's editable tabs on $lead, from PendencyService (the same
     * presence-based rules the Pendency dashboard uses). A tab that doesn't apply to this lead
     * counts as complete.
     *
     * PendencyService groups a few subsections under a module although their data is entered on
     * another tab (Details Sheet's "Bank Details" row lives on the Bank Details tab — its anchor
     * says so). Those are left to whichever stage owns that tab; a stage is never held up by data
     * it isn't allowed to enter.
     *
     * @return array<int, array{key: string, label: string, status: 'pending'|'in_progress'|'completed', pending: int}>
     */
    public function stageProgress(Lead $lead, LeadWorkflowStage $stage): array
    {
        $modules = collect(app(PendencyService::class)->forLead($lead)['modules'])->keyBy('key');

        return array_map(function (string $key) use ($modules) {
            $module = $modules->get(LeadTabs::pendencyKey($key));
            $subs = collect($module['subsections'] ?? [])
                ->filter(fn ($sub) => in_array(LeadTabs::keyForAnchor($sub['anchor'] ?? null), [null, $key], true));
            $pending = $subs->where('status', 'pending')->count();
            $completed = $subs->where('status', 'complete')->count();

            $status = match (true) {
                ! $module || ! ($module['applicable'] ?? true) || $pending === 0 => 'completed',
                $completed > 0 => 'in_progress',
                default => 'pending',
            };

            return ['key' => $key, 'label' => LeadTabs::label($key), 'status' => $status, 'pending' => $pending];
        }, LeadTabs::sorted($stage->editableModules()));
    }

    /**
     * @return array<int, string> labels of $stage's tabs still incomplete on $lead
     */
    public function incompleteTabs(Lead $lead, LeadWorkflowStage $stage): array
    {
        return array_values(array_map(
            fn ($t) => $t['label'],
            array_filter($this->stageProgress($lead, $stage), fn ($t) => $t['status'] !== 'completed')
        ));
    }

    // ------------------------------------------------------------ mutations

    /**
     * Fills in workflow fields on a lead that is about to be created: it enters the first stage,
     * and is assigned straight to its creator when the creator is the one who works that stage.
     */
    public function prepareNewLead(Lead $lead, ?User $creator): void
    {
        $first = $this->firstStage();
        $lead->workflow_status = Lead::WORKFLOW_IN_PROGRESS;
        if (! $first) {
            return;
        }

        $lead->workflow_stage_id = $first->id;
        $lead->deadline ??= $this->deadlineFor($first);

        if ($creator && empty($lead->assigned_employee_id) && $this->isWorkflowUser($creator) && $this->holdsStageRole($creator, $first)) {
            $lead->assigned_employee_id = $creator->id;
            $lead->assigned_by = $creator->id;
            $lead->assigned_at = now();
        }
    }

    public function recordCreation(Lead $lead, ?User $creator): void
    {
        $stage = $lead->workflow_stage_id ? $this->stages()->firstWhere('id', $lead->workflow_stage_id) : null;

        if ($lead->assigned_employee_id) {
            LeadAssignment::create([
                'lead_id' => $lead->id,
                'employee_id' => $lead->assigned_employee_id,
                'assigned_by' => $lead->assigned_by ?? $lead->assigned_employee_id,
                'assigned_at' => $lead->assigned_at ?? now(),
                'notes' => 'Assigned to creator on entering the first workflow stage',
            ]);
        }

        if (! $stage) {
            return;
        }

        LeadTransfer::create([
            'lead_id' => $lead->id,
            'action' => LeadTransfer::ACTION_CREATED,
            'to_stage_id' => $stage->id,
            'to_stage_name' => $stage->name,
            'to_role_name' => $stage->role?->name,
            'to_user_id' => $lead->assigned_employee_id,
            'transferred_by' => $creator?->id,
            'transferred_at' => now(),
        ]);
    }

    /**
     * Hands $lead from its current stage to $toUserId in the next stage — or, at the final stage,
     * marks the workflow complete (no user needed). Runs under a row lock and re-checks every rule,
     * so a double-click, a stale page or a crafted request cannot move a lead twice or out of order.
     *
     * @param int|null $expectedStageId the stage the submitting page showed; a mismatch means the
     *        lead already moved on since the page was loaded, and nothing happens.
     * @throws WorkflowException with a message fit to show the user
     */
    public function transfer(Lead $lead, User $actor, ?int $toUserId, ?string $remarks = null, ?int $expectedStageId = null): LeadTransfer
    {
        return DB::transaction(function () use ($lead, $actor, $toUserId, $remarks, $expectedStageId) {
            $locked = Lead::whereKey($lead->id)->lockForUpdate()->firstOrFail();
            $stage = $this->stageFor($locked);

            if (! $stage) {
                throw new WorkflowException('This lead has already completed its workflow.');
            }
            if ($expectedStageId !== null && $stage->id !== $expectedStageId) {
                throw new WorkflowException('This lead has already been moved to another stage. Please reload the page.');
            }
            if (! $this->canTransfer($actor, $locked)) {
                throw new WorkflowException('You are not allowed to transfer this lead.', 403);
            }

            $incomplete = $this->incompleteTabs($locked, $stage);
            if ($incomplete) {
                throw new WorkflowException('Complete these tabs before transferring: ' . implode(', ', $incomplete) . '.');
            }

            $fromUserId = $locked->assigned_employee_id;
            $next = $this->nextStage($stage);
            $now = now();

            if (! $next) {
                $locked->forceFill([
                    'workflow_status' => Lead::WORKFLOW_COMPLETED,
                    'workflow_stage_id' => null,
                    'deadline' => null,
                    'last_transferred_at' => $now,
                ])->save();

                $record = $this->log($locked, LeadTransfer::ACTION_COMPLETED, $stage, $fromUserId, null, null, $actor, $remarks, $now);
                $locked->logActivity($actor->id, 'workflow_completed', "Stage '{$stage->name}' complete — workflow finished");
                $lead->setRawAttributes($locked->getAttributes(), true);

                return $record;
            }

            $toUser = $toUserId ? $this->eligibleUsers($next)->firstWhere('id', $toUserId) : null;
            if (! $toUser) {
                throw new WorkflowException("Select an active user from the '{$next->role?->name}' role to transfer to.");
            }

            $locked->forceFill([
                'workflow_stage_id' => $next->id,
                'assigned_employee_id' => $toUser->id,
                'assigned_by' => $actor->id,
                'assigned_at' => $now,
                'deadline' => $this->deadlineFor($next),
                'last_transferred_at' => $now,
            ])->save();

            LeadAssignment::create([
                'lead_id' => $locked->id,
                'employee_id' => $toUser->id,
                'assigned_by' => $actor->id,
                'assigned_at' => $now,
                'notes' => trim("Transferred from '{$stage->name}' to '{$next->name}'. " . ($remarks ?? '')),
            ]);

            $record = $this->log($locked, LeadTransfer::ACTION_TRANSFERRED, $stage, $fromUserId, $next, $toUser->id, $actor, $remarks, $now);
            $locked->logActivity($actor->id, 'stage_forwarded', "Stage '{$stage->name}' complete — transferred to {$toUser->name} ({$next->name})");
            $lead->setRawAttributes($locked->getAttributes(), true);

            return $record;
        });
    }

    /**
     * Moves $lead to another user of the *same* stage (Super Admin / leads.assign only). Nothing
     * about the lead's data or stage changes — only who holds it.
     *
     * @throws WorkflowException
     */
    public function reassign(Lead $lead, User $actor, int $toUserId, ?string $remarks = null): ?LeadTransfer
    {
        return DB::transaction(function () use ($lead, $actor, $toUserId, $remarks) {
            $locked = Lead::whereKey($lead->id)->lockForUpdate()->firstOrFail();
            $stage = $this->stageFor($locked);

            if ($stage) {
                $toUser = $this->eligibleUsers($stage)->firstWhere('id', $toUserId);
                if (! $toUser) {
                    throw new WorkflowException("A lead in '{$stage->name}' can only be assigned to an active user of the '{$stage->role?->name}' role.");
                }
            } elseif ($this->isCompleted($locked)) {
                throw new WorkflowException('This lead has completed its workflow and cannot be reassigned.');
            } else {
                $toUser = User::where('status', 'active')->find($toUserId);
                if (! $toUser) {
                    throw new WorkflowException('Select an active employee.');
                }
            }

            $fromUserId = $locked->assigned_employee_id;
            $locked->assignTo($toUser->id, $actor->id, $remarks);

            $record = $stage
                ? $this->log($locked, LeadTransfer::ACTION_REASSIGNED, $stage, $fromUserId, $stage, $toUser->id, $actor, $remarks, now())
                : null;
            $lead->setRawAttributes($locked->fresh()->getAttributes(), true);

            return $record;
        });
    }

    private function log(Lead $lead, string $action, ?LeadWorkflowStage $from, ?int $fromUserId, ?LeadWorkflowStage $to, ?int $toUserId, User $actor, ?string $remarks, Carbon $at): LeadTransfer
    {
        return LeadTransfer::create([
            'lead_id' => $lead->id,
            'action' => $action,
            'from_stage_id' => $from?->id,
            'from_stage_name' => $from?->name,
            'from_role_name' => $from?->role?->name,
            'from_user_id' => $fromUserId,
            'to_stage_id' => $to?->id,
            'to_stage_name' => $to?->name,
            'to_role_name' => $to?->role?->name,
            'to_user_id' => $toUserId,
            'transferred_by' => $actor->id,
            'remarks' => $remarks !== null && trim($remarks) !== '' ? trim($remarks) : null,
            'transferred_at' => $at,
        ]);
    }
}
