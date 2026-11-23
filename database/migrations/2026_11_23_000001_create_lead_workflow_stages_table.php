<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the hardcoded App\Support\LeadStage chain with an Admin-configurable one.
 *
 * Every lead now points at a lead_workflow_stages row (workflow_stage_id) instead of a string
 * key (current_stage). The three stages the hardcoded chain used are recreated as rows — but
 * only for roles that actually exist in this database, never inventing roles — and every lead's
 * old key is translated to the matching row before the old column is dropped.
 */
return new class extends Migration
{
    /**
     * The hardcoded chain being replaced, copied here verbatim so this migration keeps working
     * after the class itself is deleted.
     */
    private const LEGACY_STAGES = [
        'details_entry' => ['label' => 'Details Entry', 'role' => 'Detail Entry', 'modules' => ['details_sheet', 'bank_loan']],
        'bank_processing' => ['label' => 'Bank Processing', 'role' => 'Bank Processing', 'modules' => ['bank_details', 'business_details', 'loan_details', 'application_form', 'documents']],
        'report_generation' => ['label' => 'Report Generation', 'role' => 'Report Generation', 'modules' => ['sbi_stock_statement', 'or', 'cma', 'dpr']],
    ];

    public function up(): void
    {
        Schema::create('lead_workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedInteger('sequence')->index();
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            // Lead-tab module keys (see App\Support\LeadTabs) this stage's role fills in.
            $table->json('modules');
            // Lead-tab module keys from earlier stages this stage's role may look at, read-only.
            $table->json('view_modules')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('workflow_stage_id')->nullable()->after('assigned_at')
                ->constrained('lead_workflow_stages')->nullOnDelete();
            $table->string('workflow_status', 20)->default('in_progress')->after('workflow_stage_id')->index();
            $table->timestamp('last_transferred_at')->nullable()->after('workflow_status');
        });

        $this->migrateLegacyStages();

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('current_stage');
        });
    }

    private function migrateLegacyStages(): void
    {
        $stageIds = [];
        $sequence = 1;
        foreach (self::LEGACY_STAGES as $key => $stage) {
            $roleId = DB::table('roles')->where('name', $stage['role'])->value('id');
            if (! $roleId) {
                continue;
            }
            $stageIds[$key] = DB::table('lead_workflow_stages')->insertGetId([
                'name' => $stage['label'],
                'sequence' => $sequence++,
                'role_id' => $roleId,
                'modules' => json_encode($stage['modules']),
                'view_modules' => json_encode([]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $firstStageId = $stageIds ? reset($stageIds) : null;

        DB::table('leads')->orderBy('id')->select(['id', 'current_stage'])->chunkById(200, function ($leads) use ($stageIds, $firstStageId) {
            foreach ($leads as $lead) {
                // Under the old chain, a null current_stage meant either "created before the chain
                // existed" or "forwarded past the last stage". Only the latter left a
                // 'workflow finished' activity behind, so that's what tells them apart.
                $finished = $lead->current_stage === null && DB::table('lead_activities')
                    ->where('lead_id', $lead->id)
                    ->where('type', 'stage_forwarded')
                    ->where('description', 'like', '%workflow finished%')
                    ->exists();

                DB::table('leads')->where('id', $lead->id)->update($finished
                    ? ['workflow_stage_id' => null, 'workflow_status' => 'completed']
                    : ['workflow_stage_id' => $stageIds[$lead->current_stage] ?? $firstStageId, 'workflow_status' => 'in_progress']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('current_stage', 50)->nullable()->after('assigned_at');
        });

        $keysByName = collect(self::LEGACY_STAGES)->mapWithKeys(fn ($s, $key) => [$s['label'] => $key]);
        foreach (DB::table('lead_workflow_stages')->get(['id', 'name']) as $stage) {
            if ($key = $keysByName[$stage->name] ?? null) {
                DB::table('leads')->where('workflow_stage_id', $stage->id)->update(['current_stage' => $key]);
            }
        }

        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workflow_stage_id');
            $table->dropColumn(['workflow_status', 'last_transferred_at']);
        });

        Schema::dropIfExists('lead_workflow_stages');
    }
};
