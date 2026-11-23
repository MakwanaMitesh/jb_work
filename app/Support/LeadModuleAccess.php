<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\User;
use App\Services\LeadWorkflow;
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
 * Lead::create()/update() when the tab isn't editable for the submitting user (so a crafted request
 * can't write a field the UI never showed them), and _form.blade.php hides / locks each tab from
 * tabAccess(). Which tabs a user gets is decided by the lead workflow — see App\Services\LeadWorkflow.
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

    /**
     * Lead columns that sit at the top of the Details Sheet tab but are not part of FIELD_MODULES'
     * details_sheet list, because creating a lead needs them regardless of tab permissions. On an
     * existing lead they are as much the Details Sheet's as any other field on it, so a user who
     * can't edit that tab can't change them either (see filterFieldsForUser()).
     *
     * @var array<int, string>
     */
    public const BASE_FIELDS = [
        'name', 'email', 'mobile_number', 'alternate_mobile_number', 'agent_id', 'bank_id',
        'loan_product_id', 'constitution_id', 'city_id', 'source', 'status', 'notes',
    ];

    public static function canViewModule(?User $user, string $module): bool
    {
        return (bool) $user?->can("{$module}.view");
    }

    private static function workflow(): LeadWorkflow
    {
        return app(LeadWorkflow::class);
    }

    /**
     * Whether $user may see this Lead at all — see LeadWorkflow::canAccessLead().
     */
    public static function canAccessLead(?User $user, Lead $lead): bool
    {
        return self::workflow()->canAccessLead($user, $lead);
    }

    /**
     * Whether $user may change this Lead right now — see LeadWorkflow::canWorkOnLead().
     */
    public static function canWorkOnLead(?User $user, Lead $lead): bool
    {
        return self::workflow()->canWorkOnLead($user, $lead);
    }

    /**
     * Applies canAccessLead()'s rule to a Lead query — used by every listing (Leads, Pendency,
     * dashboards) so they can never drift apart.
     */
    public static function scopeVisibleTo(\Illuminate\Database\Eloquent\Builder $query, ?User $user): \Illuminate\Database\Eloquent\Builder
    {
        return self::workflow()->scopeVisibleTo($query, $user);
    }

    /**
     * @return array<string, 'edit'|'view'|null>
     */
    public static function tabAccess(?User $user, ?Lead $lead = null): array
    {
        return self::workflow()->tabAccess($user, $lead);
    }

    public static function tabAllows(?User $user, ?Lead $lead, string $module, bool $edit = false): bool
    {
        return self::workflow()->tabAllows($user, $lead, $module, $edit);
    }

    public static function canViewTab(?User $user, ?Lead $lead, string $module): bool
    {
        return (self::tabAccess($user, $lead)[$module] ?? null) !== null;
    }

    /**
     * Whether $user may write to $module on this Lead right now ($lead null = on creation): the
     * workflow must give them the tab as editable, and they need the module's own update permission.
     */
    public static function isModuleEditable(?User $user, ?Lead $lead, string $module): bool
    {
        return self::tabAllows($user, $lead, $module, true) && (bool) $user?->can("{$module}.update");
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
     * Removes any field the user may not write to from $data: a FIELD_MODULES tab's fields unless the
     * tab is editable for them (see isModuleEditable()), and on an existing lead the BASE_FIELDS unless
     * the Details Sheet is. The other six tabs' payloads are gated separately in LeadController.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function filterFieldsForUser(?User $user, array $data, ?Lead $lead = null): array
    {
        foreach (self::FIELD_MODULES as $module => $fields) {
            if (self::isModuleEditable($user, $lead, $module)) {
                continue;
            }
            foreach ($fields as $field) {
                unset($data[$field]);
            }
        }

        if ($lead && ! self::tabAllows($user, $lead, 'details_sheet', true)) {
            foreach (self::BASE_FIELDS as $field) {
                unset($data[$field]);
            }
        }

        return $data;
    }
}
