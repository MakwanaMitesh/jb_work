<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadTransfer;
use App\Models\LeadWorkflowStage;
use App\Models\LoanProduct;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowChangeLog;
use App\Services\LeadWorkflow;
use App\Services\PendencyService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The role-based tab + lead transfer workflow, end to end, with the six example roles:
 *
 *   A  Initial Verification    Details Sheet, Pre Sanction
 *   B  Application Processing  Application Form
 *   C  Document Verification   Documents
 *   D  Financial Analysis      Stock Statement, Opinion Report
 *   E  Loan Assessment         CMA, DPR
 *   F  Final Processing        Bank Details, Business Details, Loan Details
 */
class LeadRoleTransferWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private const STAGES = [
        'A' => ['Initial Verification', ['details_sheet', 'bank_loan']],
        'B' => ['Application Processing', ['application_form']],
        'C' => ['Document Verification', ['documents']],
        'D' => ['Financial Analysis', ['sbi_stock_statement', 'or']],
        'E' => ['Loan Assessment', ['cma', 'dpr']],
        'F' => ['Final Processing', ['bank_details', 'business_details', 'loan_details']],
    ];

    private User $admin;
    private LoanProduct $product;

    /** @var array<string, User> */
    private array $users = [];

    /** @var array<string, LeadWorkflowStage> */
    private array $stages = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('Admin');

        $this->product = LoanProduct::firstOrCreate(['code' => 'BL'], ['name' => 'Business Loan', 'status' => 'active']);
    }

    /**
     * Builds the six-stage workflow through the Super Admin screens (so the permission grant on
     * save is exercised too) and one active user per role.
     */
    private function configureWorkflow(array $viewModules = []): void
    {
        foreach (self::STAGES as $letter => [$roleName, $modules]) {
            $role = Role::create(['name' => $roleName, 'guard_name' => 'web', 'is_active' => true]);

            $this->actingAs($this->admin)->post(route('admin.workflow.store'), [
                'name' => $roleName,
                'role_id' => $role->id,
                'modules' => $modules,
                'view_modules' => $viewModules[$letter] ?? [],
                'is_active' => 1,
            ])->assertRedirect(route('admin.workflow.index'))->assertSessionHasNoErrors();

            $this->stages[$letter] = LeadWorkflowStage::where('role_id', $role->id)->firstOrFail();

            $user = User::factory()->create(['status' => 'active', 'name' => "User {$letter}"]);
            $user->assignRole($role);
            $this->users[$letter] = $user;
        }

        // Creating leads is the first stage's job; the Admin grants it on the role as usual.
        Role::findByName('Initial Verification')->givePermissionTo('leads.create');
    }

    /**
     * Treat every tab as filled in, so chain tests don't need to enter real data in all eleven.
     */
    private function everyTabComplete(): void
    {
        $this->mock(PendencyService::class, function ($mock) {
            $mock->shouldReceive('forLead')->andReturn(['modules' => [], 'pending' => 0, 'completed' => 0]);
        });
    }

    private function createLeadAs(User $user, array $extra = []): Lead
    {
        $this->actingAs($user)->post(route('admin.leads.store'), array_merge([
            'name' => 'Workflow Lead',
            'mobile_number' => '9800000000',
            'loan_product_id' => $this->product->id,
            'status' => 'new',
        ], $extra))->assertRedirect();

        return Lead::latest('id')->firstOrFail();
    }

    private function transfer(User $by, Lead $lead, ?User $to, array $extra = [])
    {
        return $this->actingAs($by)->post(route('admin.leads.transfer', $lead), array_merge([
            'transfer_to_user_id' => $to?->id,
            'transfer_stage_id' => $lead->fresh()->workflow_stage_id,
        ], $extra));
    }

    // ------------------------------------------------------- configuration

    public function test_super_admin_configures_stages_and_each_stage_role_gets_its_tab_permissions(): void
    {
        $this->configureWorkflow();

        $this->assertSame(6, LeadWorkflowStage::count());
        $this->assertSame(range(1, 6), LeadWorkflowStage::ordered()->pluck('sequence')->all());

        $a = $this->users['A'];
        $this->assertTrue($a->can('details_sheet.update'));
        $this->assertTrue($a->can('bank_loan.view'));
        $this->assertTrue($a->can('leads.edit'));
        $this->assertFalse($a->can('cma.view'));

        $workflow = app(LeadWorkflow::class);
        $this->assertSame($this->stages['B']->id, $workflow->nextStage($this->stages['A'])->id);
        $this->assertNull($workflow->nextStage($this->stages['F']));

        $this->assertSame(6, WorkflowChangeLog::where('action', 'stage_created')->count());
        $this->actingAs($this->admin)->get(route('admin.workflow.index'))
            ->assertOk()->assertSee('Initial Verification')->assertSee('Final Processing');
    }

    public function test_stage_create_and_edit_screens_render_and_update(): void
    {
        $this->configureWorkflow();
        $stage = $this->stages['B'];

        $this->actingAs($this->admin)->get(route('admin.workflow.create'))->assertOk()
            ->assertSee('Tabs This Stage Fills In')->assertSee('(Initial Verification)');
        $this->actingAs($this->admin)->get(route('admin.workflow.edit', $stage))->assertOk()
            ->assertSee('Application Processing');

        $this->actingAs($this->admin)->put(route('admin.workflow.update', $stage), [
            'name' => 'Application Processing',
            'role_id' => $stage->role_id,
            'sequence' => $stage->sequence,
            'modules' => ['application_form'],
            'view_modules' => ['details_sheet'],
            'is_active' => 1,
        ])->assertRedirect(route('admin.workflow.index'))->assertSessionHasNoErrors();

        $this->assertSame(['details_sheet'], $stage->fresh()->view_modules);
        $this->assertTrue($this->users['B']->fresh()->can('details_sheet.view'));
        $this->assertFalse($this->users['B']->fresh()->can('details_sheet.update'));
        $this->assertDatabaseHas('workflow_change_logs', ['action' => 'stage_updated']);
    }

    public function test_a_tab_cannot_be_filled_in_by_two_stages(): void
    {
        $this->configureWorkflow();
        $role = Role::create(['name' => 'Extra', 'guard_name' => 'web', 'is_active' => true]);

        $this->actingAs($this->admin)->post(route('admin.workflow.store'), [
            'name' => 'Duplicate', 'role_id' => $role->id, 'modules' => ['cma'], 'is_active' => 1,
        ])->assertSessionHasErrors('modules');
    }

    public function test_only_the_super_admin_can_manage_the_workflow(): void
    {
        $this->configureWorkflow();
        $a = $this->users['A'];

        $this->actingAs($a)->get(route('admin.workflow.index'))->assertForbidden();
        $this->actingAs($a)->post(route('admin.workflow.store'), ['name' => 'x'])->assertForbidden();
        $this->actingAs($a)->patch(route('admin.workflow.toggle-status', $this->stages['B']))->assertForbidden();
    }

    public function test_stages_can_be_reordered_and_a_stage_with_leads_cannot_be_deleted(): void
    {
        $this->configureWorkflow();
        $this->createLeadAs($this->users['A']);

        $this->actingAs($this->admin)->patch(route('admin.workflow.move', $this->stages['C']), ['direction' => 'up'])->assertRedirect();
        $order = LeadWorkflowStage::ordered()->pluck('name')->all();
        $this->assertSame(['Initial Verification', 'Document Verification', 'Application Processing'], array_slice($order, 0, 3));

        $this->actingAs($this->admin)->delete(route('admin.workflow.destroy', $this->stages['A']))->assertSessionHas('error');
        $this->assertDatabaseHas('lead_workflow_stages', ['id' => $this->stages['A']->id]);
    }

    // ---------------------------------------------------------- tab access

    public function test_create_page_shows_a_first_stage_user_only_their_tabs(): void
    {
        $this->configureWorkflow();

        $this->actingAs($this->users['A'])->get(route('admin.leads.create'))
            ->assertOk()
            ->assertSee('id="kyc-tab"', false)
            ->assertSee('id="bank-loan-tab"', false)
            ->assertDontSee('id="application-form-tab"', false)
            ->assertDontSee('id="documents-tab"', false)
            ->assertDontSee('id="cma-tab"', false)
            ->assertDontSee('id="business-tab"', false);
    }

    public function test_a_lead_created_by_a_first_stage_user_is_assigned_to_them_in_the_first_stage(): void
    {
        $this->configureWorkflow();
        $this->stages['A']->role->update(['sla_days' => 2]);

        $lead = $this->createLeadAs($this->users['A']);

        $this->assertSame($this->stages['A']->id, $lead->workflow_stage_id);
        $this->assertSame($this->users['A']->id, $lead->assigned_employee_id);
        $this->assertSame(Lead::WORKFLOW_IN_PROGRESS, $lead->workflow_status);
        $this->assertTrue($lead->deadline->between(now()->addDays(2)->subMinute(), now()->addDays(2)->addMinute()));
        $this->assertDatabaseHas('lead_transfers', ['lead_id' => $lead->id, 'action' => 'created', 'to_user_id' => $this->users['A']->id]);
    }

    public function test_a_lead_created_by_the_admin_waits_unassigned_for_any_first_stage_user(): void
    {
        $this->configureWorkflow();
        $lead = $this->createLeadAs($this->admin);

        $this->assertNull($lead->assigned_employee_id);
        $this->actingAs($this->users['A'])->get(route('admin.leads.show', $lead))->assertOk();
        $this->actingAs($this->users['B'])->get(route('admin.leads.show', $lead))->assertForbidden();
        $this->actingAs($this->users['A'])->get(route('admin.leads.index'))->assertSee('Workflow Lead');
        $this->actingAs($this->users['B'])->get(route('admin.leads.index'))->assertDontSee('Workflow Lead');
    }

    // ------------------------------------------------------------ transfer

    public function test_the_lead_moves_through_every_role_and_each_user_sees_only_their_tabs(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();
        $lead = $this->createLeadAs($this->users['A']);

        $expectTabs = [
            'B' => ['application-form'],
            'C' => ['documents'],
            'D' => ['stock-statement', 'opinion-report'],
            'E' => ['cma', 'dpr'],
            'F' => ['bank', 'business', 'loan'],
        ];
        $allTabs = ['kyc', 'bank-loan', 'application-form', 'documents', 'stock-statement', 'opinion-report', 'cma', 'dpr', 'bank', 'business', 'loan'];

        $previous = 'A';
        foreach ($expectTabs as $letter => $tabs) {
            $this->transfer($this->users[$previous], $lead, $this->users[$letter])->assertSessionHas('success');

            $fresh = $lead->fresh();
            $this->assertSame($this->stages[$letter]->id, $fresh->workflow_stage_id, "lead should be in stage {$letter}");
            $this->assertSame($this->users[$letter]->id, $fresh->assigned_employee_id);
            $this->assertNotNull($fresh->last_transferred_at);

            // The previous holder loses the lead completely — no viewing, no editing.
            $this->actingAs($this->users[$previous])->get(route('admin.leads.edit', $lead))->assertForbidden();
            $this->actingAs($this->users[$previous])->put(route('admin.leads.update', $lead), [])->assertForbidden();

            $page = $this->actingAs($this->users[$letter])->get(route('admin.leads.edit', $lead))->assertOk();
            foreach ($allTabs as $tab) {
                in_array($tab, $tabs, true)
                    ? $page->assertSee("id=\"{$tab}-tab\"", false)
                    : $page->assertDontSee("id=\"{$tab}-tab\"", false);
            }

            $previous = $letter;
        }

        // Final stage: no next user, the workflow completes.
        $this->transfer($this->users['F'], $lead, null)->assertSessionHas('success');
        $fresh = $lead->fresh();
        $this->assertSame(Lead::WORKFLOW_COMPLETED, $fresh->workflow_status);
        $this->assertNull($fresh->workflow_stage_id);

        $history = LeadTransfer::where('lead_id', $lead->id)->orderBy('id')->pluck('action')->all();
        $this->assertSame(['created', 'transferred', 'transferred', 'transferred', 'transferred', 'transferred', 'completed'], $history);

        $this->actingAs($this->admin)->get(route('admin.leads.show', $lead))
            ->assertOk()->assertSee('Workflow History')->assertSee('Workflow Completed');
    }

    public function test_transfer_is_refused_while_the_current_stage_tabs_are_incomplete(): void
    {
        $this->configureWorkflow();
        $lead = $this->createLeadAs($this->users['A']);

        $this->transfer($this->users['A'], $lead, $this->users['B'])->assertSessionHas('error');

        $this->assertSame($this->stages['A']->id, $lead->fresh()->workflow_stage_id);
        $this->assertSame($this->users['A']->id, $lead->fresh()->assigned_employee_id);
        $this->assertSame(0, LeadTransfer::where('action', 'transferred')->count());
    }

    public function test_transfer_succeeds_with_real_data_once_the_stage_tabs_are_filled_in(): void
    {
        $this->configureWorkflow();
        $lead = $this->createLeadAs($this->users['A'], [
            'bank_details' => [['bank_name' => 'SBI', 'account_number' => '12345', 'account_type' => 'Savings', 'ifsc_code' => 'SBIN0000001']],
            'itr_details' => [['assessment_year' => '2024-25', 'itr_audited' => 'Yes']],
        ]);

        $this->transfer($this->users['A'], $lead, $this->users['B'])->assertSessionHas('success');

        $this->assertSame($this->stages['B']->id, $lead->fresh()->workflow_stage_id);
    }

    public function test_the_lead_can_only_go_to_an_active_user_of_the_next_role(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();
        $lead = $this->createLeadAs($this->users['A']);

        // Not the next role (skipping a stage).
        $this->transfer($this->users['A'], $lead, $this->users['C'])->assertSessionHas('error');
        // Nobody selected.
        $this->transfer($this->users['A'], $lead, null)->assertSessionHas('error');
        // Right role, but inactive.
        $inactive = User::factory()->create(['status' => 'inactive']);
        $inactive->assignRole('Application Processing');
        $this->transfer($this->users['A'], $lead, $inactive)->assertSessionHas('error');

        $this->assertSame($this->stages['A']->id, $lead->fresh()->workflow_stage_id);

        // The next-user dropdown lists exactly the eligible users.
        $this->actingAs($this->users['A'])->get(route('admin.leads.edit', $lead))
            ->assertSee('User B')->assertDontSee($inactive->name)->assertDontSee('User C');
    }

    public function test_a_stale_or_repeated_transfer_does_nothing(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();
        $lead = $this->createLeadAs($this->users['A']);
        $stageA = $this->stages['A']->id;

        $this->transfer($this->users['A'], $lead, $this->users['B'], ['transfer_stage_id' => $stageA])->assertSessionHas('success');
        // Double-click / resubmitted page: A no longer holds the lead.
        $this->transfer($this->users['A'], $lead, $this->users['B'], ['transfer_stage_id' => $stageA])->assertForbidden();
        // Even an admin replaying the old page can't push it a second stage forward.
        $this->transfer($this->admin, $lead, $this->users['C'], ['transfer_stage_id' => $stageA])->assertSessionHas('error');

        $this->assertSame($this->stages['B']->id, $lead->fresh()->workflow_stage_id);
        $this->assertSame(1, LeadTransfer::where('action', 'transferred')->count());
    }

    public function test_edit_form_transfer_saves_the_form_first(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();
        $lead = $this->createLeadAs($this->users['A']);

        $this->actingAs($this->users['A'])->put(route('admin.leads.update', $lead), [
            'name' => 'Renamed Before Transfer',
            'mobile_number' => '9800000000',
            'loan_product_id' => $this->product->id,
            'status' => 'new',
            'place_of_birth' => 'Surat',
            'workflow_action' => 'transfer',
            'transfer_to_user_id' => $this->users['B']->id,
            'transfer_stage_id' => $this->stages['A']->id,
            'transfer_remarks' => 'KYC verified',
        ])->assertRedirect(route('admin.leads.index'))->assertSessionHas('success');

        $fresh = $lead->fresh();
        $this->assertSame('Renamed Before Transfer', $fresh->name);
        $this->assertSame('Surat', $fresh->place_of_birth);
        $this->assertSame($this->users['B']->id, $fresh->assigned_employee_id);
        $this->assertDatabaseHas('lead_transfers', ['lead_id' => $lead->id, 'action' => 'transferred', 'remarks' => 'KYC verified', 'transferred_by' => $this->users['A']->id]);
    }

    public function test_save_as_draft_keeps_the_lead_with_the_user(): void
    {
        $this->configureWorkflow();
        $lead = $this->createLeadAs($this->users['A']);

        $this->actingAs($this->users['A'])->put(route('admin.leads.update', $lead), [
            'name' => 'Workflow Lead', 'mobile_number' => '9800000000', 'loan_product_id' => $this->product->id,
            'status' => 'new', 'occupation' => 'Trader', 'workflow_action' => 'draft',
        ])->assertRedirect(route('admin.leads.edit', $lead));

        $this->assertSame('Trader', $lead->fresh()->occupation);
        $this->assertSame($this->stages['A']->id, $lead->fresh()->workflow_stage_id);
    }

    // ----------------------------------------------- create & transfer

    private function createAndTransfer(User $by, ?User $to, array $extra = [])
    {
        return $this->actingAs($by)->post(route('admin.leads.store'), array_merge([
            'name' => 'Create Transfer Lead',
            'mobile_number' => '9800000000',
            'loan_product_id' => $this->product->id,
            'status' => 'new',
            'workflow_action' => 'transfer',
            'transfer_to_user_id' => $to?->id,
            'transfer_remarks' => 'Fresh lead, KYC done',
        ], $extra));
    }

    public function test_create_page_offers_transfer_to_the_next_roles_users(): void
    {
        $this->configureWorkflow();
        $inactive = User::factory()->create(['status' => 'inactive', 'name' => 'Inactive Applicant']);
        $inactive->assignRole('Application Processing');

        $this->actingAs($this->users['A'])->get(route('admin.leads.create'))
            ->assertOk()
            ->assertSee('Lead Transfer')
            ->assertSee('Create &amp; Transfer to Next Role', false)
            ->assertSee('Initial Verification')
            ->assertSee('Application Processing')
            ->assertSee('User B')
            ->assertDontSee('Inactive Applicant')
            ->assertDontSee('User C');
    }

    public function test_create_page_has_no_transfer_section_for_users_who_cannot_transfer_from_the_first_stage(): void
    {
        $this->configureWorkflow();
        $this->users['B']->givePermissionTo('leads.create');

        $this->actingAs($this->users['B'])->get(route('admin.leads.create'))
            ->assertOk()->assertDontSee('Create &amp; Transfer to Next Role', false);
    }

    public function test_create_and_transfer_hands_the_new_lead_straight_to_the_next_role(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();
        $this->stages['B']->role->update(['sla_days' => 4]);

        $this->createAndTransfer($this->users['A'], $this->users['B'])
            ->assertRedirect(route('admin.leads.index'))
            ->assertSessionHas('success', "Lead \"Create Transfer Lead\" created and transferred to User B (Application Processing).");

        $lead = Lead::where('name', 'Create Transfer Lead')->firstOrFail();
        $this->assertSame($this->stages['B']->id, $lead->workflow_stage_id);
        $this->assertSame($this->users['B']->id, $lead->assigned_employee_id);
        $this->assertNotNull($lead->last_transferred_at);
        $this->assertTrue($lead->deadline->between(now()->addDays(4)->subMinute(), now()->addDays(4)->addMinute()));

        $this->assertSame(['created', 'transferred'], LeadTransfer::where('lead_id', $lead->id)->orderBy('id')->pluck('action')->all());
        $this->assertDatabaseHas('lead_transfers', [
            'lead_id' => $lead->id, 'action' => 'transferred', 'from_user_id' => $this->users['A']->id,
            'to_user_id' => $this->users['B']->id, 'remarks' => 'Fresh lead, KYC done',
        ]);

        // The creator is locked out immediately; the next user has it.
        $this->actingAs($this->users['A'])->get(route('admin.leads.edit', $lead))->assertForbidden();
        $this->actingAs($this->users['B'])->get(route('admin.leads.edit', $lead))->assertOk();
    }

    public function test_create_and_transfer_with_incomplete_tabs_keeps_the_lead_as_the_creators_draft(): void
    {
        $this->configureWorkflow();

        $response = $this->createAndTransfer($this->users['A'], $this->users['B']);

        $lead = Lead::where('name', 'Create Transfer Lead')->firstOrFail();
        $response->assertRedirect(route('admin.leads.edit', $lead));
        $this->assertStringContainsString('Complete these tabs before transferring: Details Sheet', session('error'));

        $this->assertSame($this->stages['A']->id, $lead->workflow_stage_id);
        $this->assertSame($this->users['A']->id, $lead->assigned_employee_id);
        $this->assertSame(['created'], LeadTransfer::where('lead_id', $lead->id)->pluck('action')->all());
    }

    public function test_create_and_transfer_to_an_ineligible_user_saves_nothing(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();

        // C is two stages ahead; nobody selected at all is refused the same way.
        $this->createAndTransfer($this->users['A'], $this->users['C'])
            ->assertRedirect(route('admin.leads.create'))->assertSessionHas('error');
        $this->createAndTransfer($this->users['A'], null)
            ->assertRedirect(route('admin.leads.create'))->assertSessionHas('error');

        $this->assertSame(0, Lead::count());
    }

    public function test_create_and_transfer_strips_fields_outside_the_first_stage(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();

        $this->createAndTransfer($this->users['A'], $this->users['B'], [
            'place_of_birth' => 'Surat',
            'business_name' => 'Crafted Pvt Ltd',
            'required_loan_amount' => '999999',
        ])->assertSessionHas('success');

        $lead = Lead::where('name', 'Create Transfer Lead')->firstOrFail();
        $this->assertSame('Surat', $lead->place_of_birth);
        $this->assertNull($lead->business_name);
        $this->assertNull($lead->required_loan_amount);
    }

    public function test_plain_create_and_draft_still_keep_the_lead_in_the_first_stage(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();

        $this->createAndTransfer($this->users['A'], $this->users['B'], ['name' => 'Plain Create', 'workflow_action' => ''])
            ->assertRedirect(route('admin.leads.index'));
        $draft = $this->createAndTransfer($this->users['A'], $this->users['B'], ['name' => 'Draft Create', 'workflow_action' => 'draft']);

        foreach (['Plain Create', 'Draft Create'] as $name) {
            $lead = Lead::where('name', $name)->firstOrFail();
            $this->assertSame($this->stages['A']->id, $lead->workflow_stage_id);
            $this->assertSame($this->users['A']->id, $lead->assigned_employee_id);
        }
        $draft->assertRedirect(route('admin.leads.edit', Lead::where('name', 'Draft Create')->first()));
    }

    // ------------------------------------------------------------ security

    public function test_a_crafted_request_cannot_write_tabs_outside_the_users_stage(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();
        $lead = $this->createLeadAs($this->users['A'], ['place_of_birth' => 'Surat']);
        $this->transfer($this->users['A'], $lead, $this->users['B']);

        // B (Application Form only) posts Details Sheet and Business fields it was never shown —
        // and omits the required Details Sheet fields entirely, which must not block the save.
        $this->actingAs($this->users['B'])->put(route('admin.leads.update', $lead), [
            'name' => 'Hacked',
            'place_of_birth' => 'Hacked',
            'business_name' => 'Hacked Pvt Ltd',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $fresh = $lead->fresh();
        $this->assertSame('Workflow Lead', $fresh->name);
        $this->assertSame('Surat', $fresh->place_of_birth);
        $this->assertNull($fresh->business_name);
    }

    public function test_direct_urls_to_another_stages_tabs_are_blocked(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();
        $lead = $this->createLeadAs($this->users['A']);
        $this->transfer($this->users['A'], $lead, $this->users['B']);
        $b = $this->users['B'];

        // B even holds the permissions — the workflow still refuses tabs outside B's stage.
        $b->givePermissionTo(['cma.view', 'cma.generate', 'cma.download', 'dpr.generate', 'inspection_sheet.download', 'documents.download_all']);

        $this->actingAs($b)->post(route('admin.leads.cma.generate', $lead))->assertForbidden();
        $this->actingAs($b)->get(route('admin.leads.cma.download', $lead))->assertForbidden();
        $this->actingAs($b)->post(route('admin.leads.dpr.generate', $lead))->assertForbidden();
        $this->actingAs($b)->get(route('admin.leads.inspection-sheet.pdf', $lead))->assertForbidden();
        $this->actingAs($b)->get(route('admin.leads.uploaded-documents.download-all', $lead))->assertForbidden();

        // ...and B's own tab works.
        $this->actingAs($b)->get(route('admin.leads.application-form.show', $lead))->assertRedirect(route('admin.leads.edit', $lead));

        // A user who doesn't hold the lead can't reach its own tab either.
        $this->actingAs($this->users['C'])->get(route('admin.leads.application-form.show', $lead))->assertForbidden();
    }

    public function test_detail_page_hides_sections_of_tabs_outside_the_users_stage(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();
        $lead = $this->createLeadAs($this->users['A']);
        $this->transfer($this->users['A'], $lead, $this->users['B']);

        $this->actingAs($this->users['B'])->get(route('admin.leads.show', $lead))
            ->assertOk()
            ->assertDontSee('id="kyc-panel"', false)
            ->assertDontSee('id="business-panel"', false)
            ->assertSee('Lead Transfer');
    }

    public function test_read_only_tabs_from_earlier_stages_are_shown_locked(): void
    {
        $this->configureWorkflow(['B' => ['details_sheet']]);
        $this->everyTabComplete();
        $lead = $this->createLeadAs($this->users['A']);
        $this->transfer($this->users['A'], $lead, $this->users['B']);

        $page = $this->actingAs($this->users['B'])->get(route('admin.leads.edit', $lead))->assertOk();
        $page->assertSee('id="kyc-tab"', false);
        $this->assertMatchesRegularExpression('/id="kyc-panel"[^>]*data-readonly/', $page->getContent());
        $this->assertDoesNotMatchRegularExpression('/id="application-form-panel"[^>]*data-readonly/', $page->getContent());

        $this->assertSame('view', app(LeadWorkflow::class)->tabAccess($this->users['B'], $lead->fresh())['details_sheet']);
        $this->assertSame('edit', app(LeadWorkflow::class)->tabAccess($this->users['B'], $lead->fresh())['application_form']);
    }

    // --------------------------------------------------- admin & history

    public function test_super_admin_can_reassign_only_within_the_current_stages_role(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();
        $lead = $this->createLeadAs($this->users['A']);
        $this->transfer($this->users['A'], $lead, $this->users['B']);

        $b2 = User::factory()->create(['status' => 'active', 'name' => 'User B2']);
        $b2->assignRole('Application Processing');

        $this->actingAs($this->admin)->post(route('admin.leads.assign', $lead), ['employee_id' => $this->users['C']->id])
            ->assertSessionHas('error');
        $this->assertSame($this->users['B']->id, $lead->fresh()->assigned_employee_id);

        $this->actingAs($this->admin)->post(route('admin.leads.assign', $lead), ['employee_id' => $b2->id, 'notes' => 'B on leave'])
            ->assertSessionHas('success');
        $this->assertSame($b2->id, $lead->fresh()->assigned_employee_id);
        $this->assertSame($this->stages['B']->id, $lead->fresh()->workflow_stage_id);
        $this->assertDatabaseHas('lead_transfers', ['lead_id' => $lead->id, 'action' => 'reassigned', 'from_user_id' => $this->users['B']->id, 'to_user_id' => $b2->id]);

        $this->actingAs($this->users['B'])->get(route('admin.leads.show', $lead))->assertForbidden();
        $this->actingAs($b2)->get(route('admin.leads.edit', $lead))->assertOk();
    }

    public function test_transfer_history_cannot_be_changed_or_deleted(): void
    {
        $this->configureWorkflow();
        $lead = $this->createLeadAs($this->users['A']);
        $record = LeadTransfer::where('lead_id', $lead->id)->firstOrFail();

        try {
            $record->update(['remarks' => 'tampered']);
            $this->fail('History row was updated');
        } catch (\LogicException) {
        }

        try {
            $record->delete();
            $this->fail('History row was deleted');
        } catch (\LogicException) {
        }

        $this->assertNull($record->fresh()->remarks);
    }

    public function test_an_inactive_stage_is_skipped_on_transfer(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();
        $this->actingAs($this->admin)->patch(route('admin.workflow.toggle-status', $this->stages['B']));
        $lead = $this->createLeadAs($this->users['A']);

        $this->transfer($this->users['A'], $lead, $this->users['B'])->assertSessionHas('error');
        $this->transfer($this->users['A'], $lead, $this->users['C'])->assertSessionHas('success');

        $this->assertSame($this->stages['C']->id, $lead->fresh()->workflow_stage_id);
    }

    public function test_transfer_resets_the_deadline_from_the_next_roles_sla(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();
        $this->stages['B']->role->update(['sla_days' => 3]);
        $lead = $this->createLeadAs($this->users['A']);

        $this->transfer($this->users['A'], $lead, $this->users['B']);

        $deadline = $lead->fresh()->deadline;
        $this->assertTrue($deadline->between(now()->addDays(3)->subMinute(), now()->addDays(3)->addMinute()));
    }

    public function test_listing_shows_workflow_columns_and_filters_by_stage(): void
    {
        $this->configureWorkflow();
        $this->everyTabComplete();
        $moved = $this->createLeadAs($this->users['A'], ['name' => 'Moved Lead']);
        $this->createLeadAs($this->users['A'], ['name' => 'Waiting Lead']);
        $this->transfer($this->users['A'], $moved, $this->users['B']);

        $this->actingAs($this->admin)->get(route('admin.leads.index', ['workflow_stage_id' => $this->stages['B']->id]))
            ->assertOk()
            ->assertSee('Moved Lead')
            ->assertDontSee('Waiting Lead')
            ->assertSee('Application Processing')
            ->assertSee('User B');

        $this->actingAs($this->users['A'])->get(route('admin.leads.index'))
            ->assertSee('Waiting Lead')->assertDontSee('Moved Lead');
    }

    public function test_without_any_configured_stage_existing_behaviour_is_unchanged(): void
    {
        $employee = User::factory()->create(['status' => 'active']);
        $employee->assignRole('Employee');
        $employee->givePermissionTo(['leads.view', 'leads.edit', 'details_sheet.view', 'details_sheet.update', 'cma.view']);

        $lead = Lead::create(['name' => 'Legacy', 'mobile_number' => '9800000000', 'loan_product_id' => $this->product->id, 'status' => 'new']);

        $access = app(LeadWorkflow::class)->tabAccess($employee, $lead);
        $this->assertSame('edit', $access['details_sheet']);
        $this->assertSame('edit', $access['cma']);
        $this->assertNull($access['dpr']);
        $this->actingAs($employee)->get(route('admin.leads.edit', $lead))->assertOk()->assertDontSee('Lead Transfer');
    }
}
