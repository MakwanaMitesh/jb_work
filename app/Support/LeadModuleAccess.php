<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

/**
 * Which Lead columns belong to which of the five Lead-tab modules that save straight onto the Lead
 * model (Details Sheet, Bank Loan, Bank Details, Business Details, Loan Details) — the other six
 * Lead-tab modules (Application Form, Documents, Stock Statement, Opinion Report, CMA, DPR) already
 * save to their own table/array under their own `{module}.create`/`{module}.update` permission check
 * (see each Saver's permissionFor(), and LeadController::processLeadDocumentUploads()), so they are
 * not repeated here.
 *
 * Used in two places that must agree: LeadController strips a field here before it ever reaches
 * Lead::create()/update() when the submitting user lacks that module's update permission (so a
 * crafted request can't write a field the UI never showed them), and the Lead tab buttons in
 * _form.blade.php hide the whole module when the user lacks its view permission.
 */
class LeadModuleAccess
{
    /**
     * @var array<string, array<int, string>>
     */
    public const FIELD_MODULES = [
        'details_sheet' => [
            'date_of_birth', 'place_of_birth', 'occupation', 'gender', 'address', 'aadhar_card', 'pan_card',
            'udyam_registration', 'fssai_license', 'education', 'mother_name', 'father_full_name', 'relationship',
            'itr_id', 'itr_password', 'itr_audited', 'itr_ay_2026_27', 'itr_ay_2025_26', 'itr_ay_2024_25', 'itr_details',
        ],
        'bank_loan' => ['bank_loan_details'],
        'bank_details' => ['bank_details'],
        'business_details' => [
            'business_name', 'constitution_of_business', 'introduction', 'business_address', 'gst_applicable',
            'gst_number', 'gst_id', 'gst_password', 'firm_name', 'business_activity', 'business_experience',
            'no_of_manpower', 'business_location', 'area_of_premises', 'land_and_factory_building', 'connectivity',
        ],
        'loan_details' => [
            'required_loan_amount', 'cc_amount', 'cc_details', 'term_loan_amount', 'term_loan_machinery_details', 'current_loans',
        ],
    ];

    public static function canViewModule(?User $user, string $module): bool
    {
        return (bool) $user?->can("{$module}.view");
    }

    /**
     * Whether $user may see/edit this Lead at all, under the Lead Stage workflow:
     *  - `leads.view_all` (or Admin) always can.
     *  - Specifically assigned (Lead::assigned_employee_id === $user->id) always can.
     *  - Otherwise, while the Lead is unassigned ("role-pool"), any `leads.view_own` holder whose
     *    Role matches the Lead's current stage's Role can — until an Admin assigns it to one
     *    specific person via the existing leads.assign flow, after which only that person can.
     */
    public static function canAccessLead(?User $user, Lead $lead): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->can('leads.view_all')) {
            return true;
        }

        // Neither scoping permission granted: unrestricted, same as before this feature existed —
        // the stage/own-lead restriction is opt-in via leads.view_own.
        if (! $user->can('leads.view_own')) {
            return true;
        }

        if ($lead->assigned_employee_id !== null) {
            return $lead->assigned_employee_id === $user->id;
        }

        $role = LeadStage::roleFor($lead->current_stage);

        return $role !== null && $user->hasRole($role);
    }

    /**
     * Applies the same visibility rule as canAccessLead() to a Lead query — used by every index
     * listing (Leads, Pendency) so the two can never drift apart the way they once did. No-op
     * (shows everything) for leads.view_all holders or anyone without leads.view_own.
     */
    public static function scopeVisibleTo(\Illuminate\Database\Eloquent\Builder $query, ?User $user): \Illuminate\Database\Eloquent\Builder
    {
        if (! $user || $user->can('leads.view_all') || ! $user->can('leads.view_own')) {
            return $query;
        }

        $userRoles = $user->getRoleNames();
        $rolePoolStages = array_keys(array_filter(
            array_column(LeadStage::STAGES, 'role', 'key'),
            fn ($role) => $userRoles->contains($role)
        ));

        return $query->where(function ($q) use ($user, $rolePoolStages) {
            $q->where('assigned_employee_id', $user->id)
                ->orWhere(function ($q2) use ($rolePoolStages) {
                    $q2->whereNull('assigned_employee_id')->whereIn('current_stage', $rolePoolStages);
                });
        });
    }

    /**
     * Whether $user may actually write to $module's fields on this Lead right now: they need
     * canAccessLead() for the Lead, the module's own `{module}.update` permission (unchanged),
     * and the module has to belong to the Lead's *current* stage.
     */
    public static function isModuleEditable(?User $user, Lead $lead, string $module): bool
    {
        if (! self::canAccessLead($user, $lead) || ! $user?->can("{$module}.update")) {
            return false;
        }

        // The stage restriction only applies to users who opted into the Lead Stage workflow via
        // leads.view_own — leads.view_all (Admin) and users with neither scoping permission (the
        // pre-workflow default) are unaffected, same as canAccessLead() above.
        if ($user->can('leads.view_all') || ! $user->can('leads.view_own')) {
            return true;
        }

        return in_array($module, LeadStage::modulesFor($lead->current_stage), true);
    }

    public static function canViewSubsection(?User $user, string $module, string $key): bool
    {
        return (bool) $user?->can("{$module}.{$key}.view");
    }

    /**
     * @return array<string, bool>
     */
    public static function visibleSubsections(?User $user, string $module): array
    {
        $keys = array_keys(PermissionSeeder::LEAD_MODULE_SUBSECTIONS[$module] ?? []);

        return array_combine($keys, array_map(fn ($key) => self::canViewSubsection($user, $module, $key), $keys));
    }

    /**
     * Removes any field the user may not write to from $data — name/mobile/status/loan_product_id
     * and the other six Lead-tab modules' own fields are untouched, since those are governed by
     * `leads.edit`/`leads.create` or their own Saver as they always were.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function filterFieldsForUser(?User $user, array $data, ?Lead $lead = null): array
    {
        foreach (self::FIELD_MODULES as $module => $fields) {
            // $lead is null on creation (no stage to check yet) — stage gating only applies once a
            // Lead, and therefore a current_stage, exists.
            $allowed = $lead
                ? self::isModuleEditable($user, $lead, $module)
                : (bool) $user?->can("{$module}.update");

            if ($allowed) {
                continue;
            }
            foreach ($fields as $field) {
                unset($data[$field]);
            }
        }

        return $data;
    }
}
