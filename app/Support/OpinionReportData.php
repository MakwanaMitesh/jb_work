<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\OpinionReport;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * Inputs of the Opinion Report / net-worth affidavit: the template's yellow
 * (dynamic) values and nothing else. Static wording is never editable.
 * Where each value lands is OrGenerator's business.
 */
class OpinionReportData
{
    public const RELATIONSHIPS = ['Son', 'Daughter', 'Wife'];
    public const OWNERSHIP_ORIGINS = ['Ancestral', 'Self acquired'];
    public const OWNERSHIP_TYPES = ['Individual name', 'Joint names'];
    public const LAND_TYPES = ['Agricultural', 'Non agricultural'];
    public const CEILING = ['Comes in ceiling area', 'Does not come in ceiling area'];

    public const MAX_PROPERTIES = 10;
    public const MAX_FAMILY = 10;

    public const PROPERTY_KEYS = ['place', 'ownership_origin', 'ownership_type', 'encumbered', 'extent_acres', 'market_value', 'case_suit', 'land_type', 'ceiling'];
    public const FAMILY_KEYS = ['name', 'date_of_birth', 'age', 'occupation', 'relation', 'address'];

    /** The three liquid-asset rows of the template, with the template's own row labels. */
    public const LIQUID_ASSETS = ['cash' => 'Cash and Bank', 'business_capital' => 'Business Capital', 'inventory' => 'Inventory'];
    public const LIQUID_KEYS = ['description', 'amount', 'encumbered'];

    /**
     * s = string, t = long text, d = date.
     *
     * @var array<string, string>
     */
    public const SCALARS = [
        'deponent_name' => 's', 'relationship' => 's', 'father_name' => 's', 'date_of_birth' => 'd',
        'place_of_birth' => 's', 'occupation' => 's', 'residential_address' => 't', 'office_address' => 't',
        'branch' => 's', 'borrower_name' => 's', 'assets_date' => 'd', 'deponent_date' => 'd', 'notify_by' => 's',
        'outside_india' => 't', 'gov_securities' => 't', 'shares' => 't', 'life_insurance' => 't',
        'life_policy_numbers' => 's', 'life_surrender_value' => 's', 'jewellery' => 't', 'associate_investment' => 't',
        'intangible_assets' => 't', 'income_per_month' => 's', 'other_asset' => 't',
        'borrowings_banks' => 't', 'borrowings_market' => 't', 'borrowings_other' => 't', 'indirect_liability' => 't',
        'other_liability' => 't',
    ];

    public const JSON_FIELDS = ['properties', 'liquid_assets', 'family_members'];

    /**
     * Other lead form input -> Opinion Report field. The Details Sheet already
     * carries the affidavit's person fields; the branch comes from the Bank Loan
     * tab (falling back to the selected bank, as the generator always has).
     *
     * @var array<string, array{sources: array<int, string>, transform: string}>
     */
    public const LEAD_LINKS = [
        'deponent_name' => ['sources' => ['name'], 'transform' => 'first'],
        'father_name' => ['sources' => ['father_full_name'], 'transform' => 'first'],
        'relationship' => ['sources' => ['relationship'], 'transform' => 'first'],
        'date_of_birth' => ['sources' => ['date_of_birth'], 'transform' => 'first'],
        'place_of_birth' => ['sources' => ['place_of_birth'], 'transform' => 'first'],
        'occupation' => ['sources' => ['occupation', 'business_activity'], 'transform' => 'first'],
        'residential_address' => ['sources' => ['address'], 'transform' => 'first'],
        'office_address' => ['sources' => ['business_address'], 'transform' => 'first'],
        'branch' => ['sources' => ['bank_loan_details[branch_name]', 'bank_id'], 'transform' => 'first'],
        'borrower_name' => ['sources' => ['business_name', 'firm_name', 'name'], 'transform' => 'first'],
    ];

    /**
     * @return array<string, mixed>
     */
    public static function for(Lead $lead): array
    {
        $saved = $lead->relationLoaded('opinionReport') ? $lead->opinionReport : $lead->opinionReport()->first();

        if (! $saved?->saved_at) {
            return self::normalize(self::derivedFromLead($lead));
        }

        $data = self::fromModel($saved);
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
    public static function linkConfig(?OpinionReport $saved): array
    {
        $links = [];
        foreach (self::LEAD_LINKS as $field => $link) {
            $links[] = ['path' => $field] + $link;
        }

        return ['links' => $links, 'overrides' => array_values((array) ($saved?->linked_overrides ?? []))];
    }

    /**
     * Whole years from a date of birth; null when there is none.
     */
    public static function ageFrom(?string $dob, ?Carbon $asOf = null): ?int
    {
        if ($dob === null || trim($dob) === '') {
            return null;
        }

        try {
            return (int) floor(Carbon::parse($dob)->diffInYears($asOf ?? Carbon::now(), true));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromModel(OpinionReport $row): array
    {
        $data = [];
        foreach (self::SCALARS as $key => $type) {
            $value = $row->{$key};
            $data[$key] = match (true) {
                $value === null => null,
                $type === 'd' => Carbon::parse($value)->format('Y-m-d'),
                default => (string) $value,
            };
        }
        foreach (self::JSON_FIELDS as $key) {
            $data[$key] = $row->{$key} ?? [];
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function normalize(array $data): array
    {
        $out = [];
        foreach (self::SCALARS as $key => $type) {
            $value = $data[$key] ?? null;
            $out[$key] = (is_scalar($value) && trim((string) $value) !== '') ? trim((string) $value) : null;
        }

        $out['properties'] = self::rows($data['properties'] ?? [], self::PROPERTY_KEYS, self::MAX_PROPERTIES);
        $out['family_members'] = self::rows($data['family_members'] ?? [], self::FAMILY_KEYS, self::MAX_FAMILY);

        $liquid = [];
        foreach (array_keys(self::LIQUID_ASSETS) as $row) {
            $given = is_array($data['liquid_assets'][$row] ?? null) ? $data['liquid_assets'][$row] : [];
            foreach (self::LIQUID_KEYS as $k) {
                $v = $given[$k] ?? null;
                $liquid[$row][$k] = (is_scalar($v) && trim((string) $v) !== '') ? trim((string) $v) : null;
            }
        }
        $out['liquid_assets'] = $liquid;

        return $out;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        $rules = ['opr' => ['nullable', 'array']];

        foreach (self::SCALARS as $key => $type) {
            $rules["opr.$key"] = match ($type) {
                'd' => ['nullable', 'date'],
                't' => ['nullable', 'string', 'max:1000'],
                default => ['nullable', 'string', 'max:255'],
            };
        }
        $rules['opr.relationship'][] = Rule::in(self::RELATIONSHIPS);

        $rules['opr.properties'] = ['nullable', 'array', 'max:' . self::MAX_PROPERTIES];
        foreach (self::PROPERTY_KEYS as $k) {
            $rules["opr.properties.*.$k"] = ['nullable', 'string', 'max:255'];
        }
        $rules['opr.properties.*.ownership_origin'][] = Rule::in(self::OWNERSHIP_ORIGINS);
        $rules['opr.properties.*.ownership_type'][] = Rule::in(self::OWNERSHIP_TYPES);
        $rules['opr.properties.*.land_type'][] = Rule::in(self::LAND_TYPES);
        $rules['opr.properties.*.ceiling'][] = Rule::in(self::CEILING);

        $rules['opr.family_members'] = ['nullable', 'array', 'max:' . self::MAX_FAMILY];
        foreach (self::FAMILY_KEYS as $k) {
            $rules["opr.family_members.*.$k"] = $k === 'date_of_birth' ? ['nullable', 'date'] : ['nullable', 'string', 'max:255'];
        }

        $rules['opr.liquid_assets'] = ['nullable', 'array'];
        foreach (self::LIQUID_KEYS as $k) {
            $rules["opr.liquid_assets.*.$k"] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * Drop rows with no content (keeps order, renumbers).
     *
     * @return array<int, array<string, ?string>>
     */
    private static function rows(mixed $rows, array $keys, int $max): array
    {
        $out = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            $row = is_array($row) ? $row : [];
            $clean = [];
            foreach ($keys as $k) {
                $v = $row[$k] ?? null;
                $clean[$k] = (is_scalar($v) && trim((string) $v) !== '') ? trim((string) $v) : null;
            }
            if (array_filter($clean, fn ($v) => $v !== null)) {
                $out[] = $clean;
            }
        }

        return array_slice($out, 0, $max);
    }
}
