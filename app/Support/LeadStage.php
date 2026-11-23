<?php

namespace App\Support;

/**
 * The sequential Stage chain a Lead moves through: each stage is a fixed group of Lead-tab
 * modules (see PermissionSeeder::MODULES for the module keys) owned by a single Role. A Lead
 * is "in" exactly one stage at a time (Lead::current_stage); LeadModuleAccess uses this to decide
 * who can see/edit it and LeadController::submitStage() advances it to the next one.
 *
 * Edit this array to change the chain — there is no admin UI for it yet, by design (kept as a
 * deliberately small first version; see the lead-stage-workflow branch plan).
 *
 * @var array<int, array{key: string, label: string, modules: array<int, string>, role: string}>
 */
class LeadStage
{
    public const STAGES = [
        [
            'key' => 'details_entry',
            'label' => 'Details Entry',
            'modules' => ['details_sheet', 'bank_loan'],
            'role' => 'Detail Entry',
        ],
        [
            'key' => 'bank_processing',
            'label' => 'Bank Processing',
            'modules' => ['bank_details', 'business_details', 'loan_details', 'application_form', 'documents'],
            'role' => 'Bank Processing',
        ],
        [
            'key' => 'report_generation',
            'label' => 'Report Generation',
            'modules' => ['sbi_stock_statement', 'or', 'cma', 'dpr'],
            'role' => 'Report Generation',
        ],
    ];

    public static function first(): array
    {
        return self::STAGES[0];
    }

    public static function find(?string $key): ?array
    {
        foreach (self::STAGES as $stage) {
            if ($stage['key'] === $key) {
                return $stage;
            }
        }

        return null;
    }

    /**
     * The stage after $key, or null when $key is the last stage (or unknown/finished).
     */
    public static function next(?string $key): ?array
    {
        foreach (self::STAGES as $i => $stage) {
            if ($stage['key'] === $key) {
                return self::STAGES[$i + 1] ?? null;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public static function modulesFor(?string $key): array
    {
        return self::find($key)['modules'] ?? [];
    }

    public static function roleFor(?string $key): ?string
    {
        return self::find($key)['role'] ?? null;
    }

    /**
     * The deadline a Lead gets the moment it enters stage $key, based on that stage's Role's
     * sla_days (Admin-configured on the Role itself) — null when the stage doesn't exist, its
     * Role doesn't exist, or that Role has no sla_days set (no deadline enforced).
     */
    public static function deadlineFromNow(?string $key): ?\Illuminate\Support\Carbon
    {
        $roleName = self::roleFor($key);
        if (! $roleName) {
            return null;
        }

        $slaDays = \App\Models\Role::where('name', $roleName)->value('sla_days');

        return $slaDays ? now()->addDays($slaDays) : null;
    }

    public static function labelFor(?string $key): ?string
    {
        return self::find($key)['label'] ?? null;
    }

    /**
     * PendencyService keys its modules slightly differently from the permission module names for
     * two of them ('sbi_stock_statement' permission -> 'stock_statement' pendency key, 'or' ->
     * 'opinion_report') — this translates modulesFor() into PendencyService's own keys so stage
     * completion can be checked against PendencyService::forLead()'s result.
     *
     * @return array<int, string>
     */
    public static function pendencyModulesFor(?string $key): array
    {
        $map = ['sbi_stock_statement' => 'stock_statement', 'or' => 'opinion_report'];

        return array_map(fn ($module) => $map[$module] ?? $module, self::modulesFor($key));
    }
}
