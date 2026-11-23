<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveWorkflowStageRequest;
use App\Models\Lead;
use App\Models\LeadWorkflowStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\WorkflowChangeLog;
use App\Services\LeadWorkflow;
use App\Support\LeadTabs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Super Admin configuration of the lead workflow: which roles handle a lead, in what order, and
 * which Lead tabs each of them fills in (or may only read). Admin-only, like Role management.
 */
class WorkflowStageController extends Controller
{
    public function index(LeadWorkflow $workflow): View
    {
        $this->authorize('viewAny', Role::class);

        $stages = LeadWorkflowStage::with(['role' => fn ($q) => $q->withCount('users')])
            ->withCount(['leads as active_leads_count' => fn ($q) => $q->where('workflow_status', Lead::WORKFLOW_IN_PROGRESS)])
            ->ordered()
            ->get();

        $roleUsers = [];
        foreach ($stages as $stage) {
            $roleUsers[$stage->id] = $workflow->eligibleUsers($stage)->pluck('name')->all();
        }

        $logs = WorkflowChangeLog::with('user')->latest()->latest('id')->take(15)->get();

        return view('admin.workflow.index', compact('stages', 'workflow', 'roleUsers', 'logs'));
    }

    public function create(): View
    {
        $this->authorize('viewAny', Role::class);

        return view('admin.workflow.form', [
            'stage' => new LeadWorkflowStage([
                'sequence' => (int) LeadWorkflowStage::max('sequence') + 1,
                'is_active' => true,
                'modules' => [],
                'view_modules' => [],
            ]),
            'roles' => $this->assignableRoles(),
            'takenTabs' => $this->takenTabs(null),
        ]);
    }

    public function store(SaveWorkflowStageRequest $request): RedirectResponse
    {
        $stage = DB::transaction(function () use ($request) {
            $stage = LeadWorkflowStage::create($this->attributes($request) + [
                'sequence' => $request->validated('sequence') ?? ((int) LeadWorkflowStage::max('sequence') + 1),
            ]);
            $this->grantTabPermissions($stage);
            WorkflowChangeLog::record($request->user()->id, 'stage_created', $this->describe('Created', $stage));

            return $stage;
        });

        return redirect()->route('admin.workflow.index')->with('success', "Stage \"{$stage->name}\" created.");
    }

    public function edit(LeadWorkflowStage $stage): View
    {
        $this->authorize('viewAny', Role::class);

        return view('admin.workflow.form', [
            'stage' => $stage,
            'roles' => $this->assignableRoles(),
            'takenTabs' => $this->takenTabs($stage),
        ]);
    }

    public function update(SaveWorkflowStageRequest $request, LeadWorkflowStage $stage): RedirectResponse
    {
        DB::transaction(function () use ($request, $stage) {
            $stage->update($this->attributes($request) + [
                'sequence' => $request->validated('sequence') ?? $stage->sequence,
            ]);
            $this->grantTabPermissions($stage->fresh('role'));
            WorkflowChangeLog::record($request->user()->id, 'stage_updated', $this->describe('Updated', $stage->fresh('role')));
        });

        return redirect()->route('admin.workflow.index')->with('success', "Stage \"{$stage->name}\" updated.");
    }

    public function toggleStatus(Request $request, LeadWorkflowStage $stage): RedirectResponse
    {
        $this->authorize('viewAny', Role::class);

        $stage->update(['is_active' => ! $stage->is_active]);
        $state = $stage->is_active ? 'activated' : 'deactivated';
        WorkflowChangeLog::record($request->user()->id, "stage_{$state}", "Stage '{$stage->name}' {$state}.");

        $message = "Stage \"{$stage->name}\" {$state}.";
        if (! $stage->is_active && $stage->leads()->where('workflow_status', Lead::WORKFLOW_IN_PROGRESS)->exists()) {
            $message .= ' Leads already in this stage stay with their users; new transfers will skip it.';
        }

        return redirect()->route('admin.workflow.index')->with('success', $message);
    }

    /**
     * Swaps the stage with its neighbour above/below in the workflow order.
     */
    public function move(Request $request, LeadWorkflowStage $stage): RedirectResponse
    {
        $this->authorize('viewAny', Role::class);
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];

        DB::transaction(function () use ($stage, $direction, $request) {
            // Normalise to 1..n first so equal sequence numbers can't make a swap a no-op.
            $ordered = LeadWorkflowStage::ordered()->lockForUpdate()->get()->values();
            foreach ($ordered as $i => $s) {
                $s->sequence !== $i + 1 && $s->update(['sequence' => $i + 1]);
            }

            $index = $ordered->search(fn ($s) => $s->id === $stage->id);
            $swapWith = $ordered->get($direction === 'up' ? $index - 1 : $index + 1);
            if (! $swapWith) {
                return;
            }

            $current = $ordered[$index];
            [$a, $b] = [$current->sequence, $swapWith->sequence];
            $current->update(['sequence' => $b]);
            $swapWith->update(['sequence' => $a]);

            WorkflowChangeLog::record($request->user()->id, 'stage_reordered', "Stage '{$current->name}' moved {$direction} (now step {$b}).");
        });

        return redirect()->route('admin.workflow.index')->with('success', 'Workflow order updated.');
    }

    public function destroy(Request $request, LeadWorkflowStage $stage): RedirectResponse
    {
        $this->authorize('viewAny', Role::class);

        if ($stage->leads()->where('workflow_status', Lead::WORKFLOW_IN_PROGRESS)->exists()) {
            return redirect()->route('admin.workflow.index')
                ->with('error', "Stage \"{$stage->name}\" still has leads in it. Deactivate it instead, or move those leads on first.");
        }

        $name = $stage->name;
        $stage->delete();
        WorkflowChangeLog::record($request->user()->id, 'stage_deleted', "Stage '{$name}' deleted.");

        return redirect()->route('admin.workflow.index')->with('success', "Stage \"{$name}\" deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(SaveWorkflowStageRequest $request): array
    {
        $modules = LeadTabs::sorted($request->validated('modules'));

        return [
            'name' => $request->validated('name'),
            'role_id' => (int) $request->validated('role_id'),
            'modules' => $modules,
            'view_modules' => array_values(array_diff(LeadTabs::sorted($request->validated('view_modules') ?? []), $modules)),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * Gives the stage's role what it needs to work its tabs: opening and saving leads, every action
     * of its editable tabs, and the read actions of its read-only ones. Additive only — nothing is
     * ever revoked here, so permissions an Admin set by hand on the Role page are left alone (for
     * workflow users, the stage's tab list is what limits what they see anyway).
     */
    private function grantTabPermissions(LeadWorkflowStage $stage): void
    {
        $names = ['leads.view', 'leads.edit'];
        foreach ($stage->editableModules() as $key) {
            $names = array_merge($names, LeadTabs::permissionsFor($key, true));
        }
        foreach ($stage->readOnlyModules() as $key) {
            $names = array_merge($names, LeadTabs::permissionsFor($key, false));
        }

        $permissions = Permission::whereIn('name', array_unique($names))->where('guard_name', 'web')->get();
        $stage->role?->givePermissionTo($permissions);
    }

    private function describe(string $verb, LeadWorkflowStage $stage): string
    {
        $tabs = implode(', ', LeadTabs::labels($stage->modules));
        $readOnly = $stage->readOnlyModules() ? '; read-only: ' . implode(', ', LeadTabs::labels($stage->readOnlyModules())) : '';
        $state = $stage->is_active ? 'active' : 'inactive';

        return "{$verb} stage '{$stage->name}' (step {$stage->sequence}, role {$stage->role?->name}, {$state}) — tabs: {$tabs}{$readOnly}.";
    }

    private function assignableRoles()
    {
        return Role::whereNotIn('name', ['Admin', 'Agent'])->withCount('users')->orderBy('name')->get();
    }

    /**
     * Tabs another stage already fills in, keyed to that stage's name (they can't be picked again).
     *
     * @return array<string, string>
     */
    private function takenTabs(?LeadWorkflowStage $except): array
    {
        $taken = [];
        $others = LeadWorkflowStage::query()->when($except?->exists, fn ($q) => $q->whereKeyNot($except->id))->get();
        foreach ($others as $other) {
            foreach ($other->modules ?? [] as $key) {
                $taken[$key] = $other->name;
            }
        }

        return $taken;
    }
}
