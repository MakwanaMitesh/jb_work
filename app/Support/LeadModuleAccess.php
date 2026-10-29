<?php

namespace App\Support;

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
    public static function filterFieldsForUser(?User $user, array $data): array
    {
        foreach (self::FIELD_MODULES as $module => $fields) {
            if ($user?->can("{$module}.update")) {
                continue;
            }
            foreach ($fields as $field) {
                unset($data[$field]);
            }
        }

        return $data;
    }
}
