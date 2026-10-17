<?php

namespace App\Support;

use App\Models\CmaProfile;
use App\Models\Lead;

/**
 * Customer/profile values of the CMA workbook (its "profile" sheet): the
 * only part of the CMA that is entered per lead. The financial statements,
 * ratios and formulas are the workbook's own and are not collected.
 */
class CmaProfileData
{
    /**
     * field => [label, max length, long text?]
     *
     * @var array<string, array{0: string, 1: int, 2: bool}>
     */
    public const FIELDS = [
        'activity' => ['Activity', 255, false],
        'firm_name' => ['Name of Firm', 255, false],
        'proprietor_name' => ['Name of Proprietor', 255, false],
        'pan' => ['PAN', 50, false],
        'udyam_no' => ['Udyam No.', 100, false],
        'mobile' => ['Mobile No.', 30, false],
        'email' => ['Email ID', 255, false],
        'address' => ['Address', 1000, true],
        'business_experience' => ['Business Experience', 255, false],
        'area_of_operation' => ['Current Area of Operation', 255, false],
        'manpower' => ['Number of Manpower', 100, false],
        'space' => ['Infrastructure: Space', 255, false],
        'connectivity' => ['Infrastructure: Connectivity', 255, false],
        'location' => ['Infrastructure: Location', 255, false],
        'electricity_water' => ['Infrastructure: Electricity & Water', 255, false],
        'existing_facility' => ['Existing Facility', 255, false],
    ];

    /**
     * Other lead form input -> CMA field. The lead has one Business Location
     * and the workbook prints it twice (Current Area of Operation and
     * Infrastructure: Location), so both follow it until edited by hand.
     * Electricity & Water and Existing Facility have no lead input and are
     * entered here.
     *
     * @var array<string, array{sources: array<int, string>, transform: string}>
     */
    public const LEAD_LINKS = [
        'activity' => ['sources' => ['business_activity'], 'transform' => 'first'],
        'firm_name' => ['sources' => ['business_name', 'firm_name', 'name'], 'transform' => 'first'],
        'proprietor_name' => ['sources' => ['name'], 'transform' => 'first'],
        'pan' => ['sources' => ['pan_card'], 'transform' => 'first'],
        'udyam_no' => ['sources' => ['udyam_registration'], 'transform' => 'first'],
        'mobile' => ['sources' => ['mobile_number'], 'transform' => 'first'],
        'email' => ['sources' => ['email'], 'transform' => 'first'],
        'address' => ['sources' => ['business_address', 'address'], 'transform' => 'first'],
        'business_experience' => ['sources' => ['business_experience'], 'transform' => 'first'],
        'area_of_operation' => ['sources' => ['business_location'], 'transform' => 'first'],
        'manpower' => ['sources' => ['no_of_manpower'], 'transform' => 'first'],
        'space' => ['sources' => ['area_of_premises'], 'transform' => 'first'],
        'connectivity' => ['sources' => ['connectivity'], 'transform' => 'first'],
        'location' => ['sources' => ['business_location'], 'transform' => 'first'],
    ];

    /**
     * @return array<string, ?string>
     */
    public static function for(Lead $lead): array
    {
        $saved = $lead->relationLoaded('cmaProfile') ? $lead->cmaProfile : $lead->cmaProfile()->first();

        if (! $saved?->saved_at) {
            return self::normalize(self::derivedFromLead($lead));
        }

        $data = [];
        foreach (array_keys(self::FIELDS) as $field) {
            $data[$field] = $saved->{$field};
        }

        $overrides = (array) ($saved->linked_overrides ?? []);
        foreach (self::derivedFromLead($lead) as $field => $value) {
            if (! in_array($field, $overrides, true)) {
                $data[$field] = $value;
            }
        }

        return self::normalize($data);
    }

    /**
     * @return array<string, ?string>
     */
    public static function derivedFromLead(Lead $lead): array
    {
        $out = [];
        foreach (self::LEAD_LINKS as $field => $link) {
            $out[$field] = ApplicationFormData::transform(
                $link['transform'],
                array_map(fn ($source) => ApplicationFormData::sourceValue($lead, $source), $link['sources'])
            );
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $data normalized submission
     * @return array<int, string>
     */
    public static function overridesFor(Lead $lead, array $data): array
    {
        $overrides = [];
        foreach (self::derivedFromLead($lead) as $field => $derived) {
            if ((string) ($data[$field] ?? '') !== (string) $derived) {
                $overrides[] = $field;
            }
        }

        return $overrides;
    }

    /**
     * @return array<string, mixed>
     */
    public static function linkConfig(?CmaProfile $saved): array
    {
        $links = [];
        foreach (self::LEAD_LINKS as $field => $link) {
            $links[] = ['path' => $field] + $link;
        }

        return ['links' => $links, 'overrides' => array_values((array) ($saved?->linked_overrides ?? []))];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, ?string>
     */
    public static function normalize(array $data): array
    {
        $out = [];
        foreach (array_keys(self::FIELDS) as $field) {
            $value = $data[$field] ?? null;
            $out[$field] = (is_scalar($value) && trim((string) $value) !== '') ? trim((string) $value) : null;
        }

        return $out;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        $rules = ['cma' => ['nullable', 'array']];
        foreach (self::FIELDS as $field => [, $max]) {
            $rules["cma.$field"] = ['nullable', 'string', "max:$max"];
        }
        $rules['cma.email'][] = 'email';

        return $rules;
    }
}
