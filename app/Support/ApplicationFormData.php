<?php

namespace App\Support;

use App\Models\ApplicationForm;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * Field catalogue for the Application Form (the three-sheet SBI Mudra loan
 * application): option lists, scalar field types, the repeating-group
 * shapes, Lead-derived defaults, and validation rules. The Excel cell
 * mapping lives separately in ApplicationFormCellMap.
 */
class ApplicationFormData
{
    public const CONSTITUTIONS = ['Proprietary', 'Partnership', 'Pvt. Ltd.', 'Ltd. Company', 'Others'];
    public const CATEGORIES = ['Shishu', 'Kishor', 'Tarun'];
    public const PREMISES = ['Rented', 'Owned'];
    public const SOCIAL_CATEGORIES = ['General', 'SC', 'ST', 'OBC'];
    public const MINORITIES = ['None', 'Buddhists', 'Muslims', 'Christians', 'Sikhs', 'Jains', 'Zoroastrians'];
    public const SEX = ['M' => 'Male', 'F' => 'Female', 'O' => 'Other'];
    public const YES_NO = ['Yes', 'No'];
    public const COMPLIANCE = ['Yes', 'No', 'N.A.'];
    public const ID_PROOFS = ['PAN', 'Aadhaar Card', 'Voter ID', 'Passport', 'Driving Licence'];
    public const ADDRESS_PROOFS = ['Aadhaar Card', 'Voter ID', 'Passport', 'Driving Licence', 'Electricity Bill', 'Ration Card'];

    public const EXISTING_FACILITIES = [
        'savings' => 'Savings Account',
        'current' => 'Current Account',
        'cash_credit' => 'Cash Credit',
        'term_loan' => 'Term Loan',
        'lc_bg' => 'LC/BG',
    ];

    public const PROPOSED_FACILITIES = [
        'cash_credit' => 'Cash Credit',
        'term_loan' => 'Term Loan',
        'lc_bg' => 'LC/BG',
    ];

    public const PERIODS = [
        'past_year_2' => 'Past Year-II (Actual)',
        'past_year_1' => 'Past Year-I (Actual)',
        'present_year' => 'Present Year (Estimate)',
        'next_year' => 'Next Year (Projection)',
    ];

    /** Same wording as the template's section J. */
    public const OBLIGATIONS = [
        'shops_establishment' => '1. Registration under Shops and Establishment Act',
        'msme' => '2. Registration under MSME (Provisional /Final)',
        'drug_license' => '3. Drug License',
        'sales_tax' => '4. Latest Sales Tax Return Filed',
        'income_tax' => '5.Latest Income Tax Returns Filed',
        'other_dues' => '6.Any other Statutory dues remaining outstanding',
    ];

    /** The template has room for two associate-concern rows and two machinery rows. */
    public const MAX_ASSOCIATES = 2;
    public const MAX_MACHINERY = 2;

    public const ASSOCIATE_KEYS = ['name', 'address', 'bank', 'nature', 'interest'];
    public const FACILITY_KEYS = ['bank', 'limit', 'outstanding', 'security', 'status'];
    public const PROPOSED_KEYS = ['amount', 'purpose', 'security'];
    public const MACHINERY_KEYS = ['type', 'purpose', 'supplier', 'total_cost', 'promoter_contribution', 'loan_required'];
    public const PERIOD_KEYS = ['net_sales', 'net_profit', 'net_worth'];

    /** Fields needed before the workbook can be generated (everything else may stay blank). */
    public const REQUIRED_FOR_GENERATION = [
        'enterprise_name' => 'Enterprise name',
        'constitution' => 'Constitution',
        'business_address' => 'Current business address',
        'applicant_name' => 'Proprietor / Partner / Director name',
    ];

    /**
     * Scalar columns: s = string, t = long text, d = date, n = amount.
     *
     * @var array<string, string>
     */
    public const SCALARS = [
        'application_serial_no' => 's', 'branch_name' => 's', 'category' => 's',
        'enterprise_name' => 's', 'constitution' => 's', 'constitution_other' => 's',
        'business_address' => 't', 'state' => 's', 'pincode' => 's', 'business_premises' => 's',
        'telephone' => 's', 'mobile_country_code' => 's', 'mobile_number' => 's', 'email' => 's',
        'activity_existing' => 't', 'activity_proposed' => 't', 'commencement_date' => 'd',
        'is_registered' => 's', 'registration_no_act' => 's', 'registered_office_address' => 't',
        'social_category' => 's', 'minority_community' => 's',
        'applicant_name' => 's', 'date_of_birth' => 'd', 'sex' => 's', 'residential_address' => 't',
        'applicant_mobile' => 's', 'qualification' => 's', 'experience_years' => 's',
        'id_proof_type' => 's', 'id_proof_number' => 's', 'address_proof_type' => 's',
        'address_proof_number' => 's', 'pan_din_number' => 's', 'bank_relationship' => 's',
        'customer_id' => 's',
        'wc_fy_label_1' => 's', 'wc_fy_label_2' => 's', 'wc_actual_sales_1' => 'n', 'wc_actual_sales_2' => 'n',
        'wc_projected_sales' => 'n', 'wc_working_cycle_months' => 's', 'wc_inventory' => 'n',
        'wc_debtors' => 'n', 'wc_creditors' => 'n', 'wc_promoter_contribution' => 'n', 'wc_limits' => 'n',
        'repayment_period' => 's', 'moratorium_period' => 's',
        'declaration_date' => 'd', 'declaration_place' => 's',
    ];

    public const JSON_FIELDS = [
        'associate_concerns', 'existing_facilities', 'proposed_facilities', 'machinery', 'performance', 'statutory',
    ];

    /**
     * Details Sheet (lead form input) → Application Form field.
     *
     * Each linked field follows its sources live in the browser and is
     * recomputed from the Lead on the server. A user's own edit to a linked
     * field is kept as an override (see ApplicationFormSaver). Sources are
     * lead form input names; the first non-empty one wins unless the
     * transform says otherwise. Transforms are implemented twice, in
     * transform() here and in the tab's script, and must stay in step.
     *
     * Fields not listed are Application-only and never overwritten from the Lead.
     *
     * @var array<string, array{sources: array<int, string>, transform: string}>
     */
    public const LEAD_LINKS = [
        'enterprise_name' => ['sources' => ['business_name', 'firm_name', 'name'], 'transform' => 'first'],
        'branch_name' => ['sources' => ['bank_loan_details[branch_name]'], 'transform' => 'first'],
        'constitution' => ['sources' => ['constitution_id'], 'transform' => 'constitution'],
        'constitution_other' => ['sources' => ['constitution_id'], 'transform' => 'constitution_other'],
        'business_address' => ['sources' => ['business_address', 'address'], 'transform' => 'first'],
        'pincode' => ['sources' => ['business_address', 'address'], 'transform' => 'pincode'],
        'mobile_country_code' => ['sources' => ['mobile_number'], 'transform' => 'country_code'],
        'mobile_number' => ['sources' => ['mobile_number'], 'transform' => 'first'],
        'email' => ['sources' => ['email'], 'transform' => 'first'],
        'activity_existing' => ['sources' => ['business_activity'], 'transform' => 'first'],
        'is_registered' => ['sources' => ['udyam_registration'], 'transform' => 'yes_if_any'],
        'registration_no_act' => ['sources' => ['udyam_registration'], 'transform' => 'udyam_text'],
        'registered_office_address' => ['sources' => ['business_address', 'address'], 'transform' => 'first'],
        'applicant_name' => ['sources' => ['name'], 'transform' => 'first'],
        'date_of_birth' => ['sources' => ['date_of_birth'], 'transform' => 'first'],
        'sex' => ['sources' => ['gender'], 'transform' => 'sex'],
        'residential_address' => ['sources' => ['address'], 'transform' => 'first'],
        'applicant_mobile' => ['sources' => ['mobile_number'], 'transform' => 'first'],
        'qualification' => ['sources' => ['education'], 'transform' => 'first'],
        'experience_years' => ['sources' => ['business_experience'], 'transform' => 'first'],
        'id_proof_type' => ['sources' => ['pan_card', 'aadhar_card'], 'transform' => 'id_type'],
        'id_proof_number' => ['sources' => ['pan_card', 'aadhar_card'], 'transform' => 'first'],
        'address_proof_type' => ['sources' => ['aadhar_card'], 'transform' => 'aadhaar_type'],
        'address_proof_number' => ['sources' => ['aadhar_card'], 'transform' => 'first'],
        'pan_din_number' => ['sources' => ['pan_card'], 'transform' => 'first'],
        'declaration_place' => ['sources' => ['city_id', 'business_location'], 'transform' => 'first'],
        'proposed_facilities.cash_credit.amount' => ['sources' => ['cc_amount'], 'transform' => 'amount'],
        'proposed_facilities.term_loan.amount' => ['sources' => ['term_loan_amount'], 'transform' => 'amount'],
    ];

    /**
     * What the form shows and the workbook is generated from.
     *
     * Before the form is ever saved: everything the Lead can supply. Once
     * saved: the saved values, except that linked fields the user has not
     * overridden follow the Lead's current values. Application-only fields
     * are never touched by Lead edits.
     *
     * @return array<string, mixed>
     */
    public static function for(Lead $lead): array
    {
        $saved = $lead->relationLoaded('applicationForm') ? $lead->applicationForm : $lead->applicationForm()->first();

        if (! $saved?->saved_at) {
            return self::normalize(self::leadDefaults($lead));
        }

        $data = self::fromModel($saved);
        $overrides = (array) ($saved->linked_overrides ?? []);

        foreach (self::derivedFromLead($lead) as $path => $value) {
            if (! in_array($path, $overrides, true)) {
                data_set($data, $path, $value);
            }
        }

        return self::normalize($data);
    }

    /**
     * Linked path => value derived from the Lead (null when the Lead has none).
     *
     * @return array<string, ?string>
     */
    public static function derivedFromLead(Lead $lead): array
    {
        $out = [];

        foreach (self::LEAD_LINKS as $path => $link) {
            $values = array_map(fn ($source) => self::sourceValue($lead, $source), $link['sources']);
            $out[$path] = self::transform($link['transform'], $values);
        }

        return $out;
    }

    /**
     * Linked paths whose submitted value differs from what the Lead implies.
     *
     * @param array<string, mixed> $data normalized submission
     * @return array<int, string>
     */
    public static function overridesFor(Lead $lead, array $data): array
    {
        $overrides = [];

        foreach (self::derivedFromLead($lead) as $path => $derived) {
            if ((string) data_get($data, $path) !== (string) $derived) {
                $overrides[] = $path;
            }
        }

        return $overrides;
    }

    /**
     * The value of a lead form input, as the browser form would show it.
     */
    public static function sourceValue(Lead $lead, string $input): string
    {
        $value = match ($input) {
            'constitution_id' => $lead->constitution_of_business,
            'city_id' => $lead->city?->name,
            'bank_id' => $lead->bank?->name,
            'bank_loan_details[branch_name]' => $lead->bank_loan_details['branch_name'] ?? null,
            'date_of_birth' => $lead->date_of_birth?->format('Y-m-d'),
            default => preg_match('/^(\w+)\[(\d+)\]\[(\w+)\]$/', $input, $m)
                ? data_get($lead->{$m[1]}, $m[2] . '.' . $m[3])
                : $lead->{$input},
        };

        return is_scalar($value) ? trim((string) $value) : '';
    }

    /**
     * @param array<int, string> $values source values in LEAD_LINKS order ('' = empty)
     */
    public static function transform(string $transform, array $values): ?string
    {
        $first = collect($values)->first(fn ($v) => $v !== '');
        $first = $first === null ? null : (string) $first;

        return match ($transform) {
            'constitution' => self::matchConstitution($first)[0],
            'constitution_other' => self::matchConstitution($first)[1],
            'pincode' => $first !== null && preg_match('/\b(\d{6})\b/', $first, $m) ? $m[1] : null,
            'country_code' => $first !== null ? '91' : null,
            'yes_if_any' => $first !== null ? 'Yes' : null,
            'udyam_text' => $first !== null ? 'UDYAM AADHAR [' . $first . ']' : null,
            'sex' => self::sexFrom($first),
            'id_type' => ($values[0] ?? '') !== '' ? 'PAN' : (($values[1] ?? '') !== '' ? 'Aadhaar Card' : null),
            'aadhaar_type' => $first !== null ? 'Aadhaar Card' : null,
            'amount' => self::parseAmount($first),
            default => $first,
        };
    }

    private static function sexFrom(?string $gender): ?string
    {
        $g = strtolower((string) $gender);

        return $g === '' ? null : (str_starts_with($g, 'm') ? 'M' : (str_starts_with($g, 'f') ? 'F' : 'O'));
    }

    /**
     * For the tab's script: links plus the paths the user has overridden.
     *
     * @return array<string, mixed>
     */
    public static function linkConfig(?ApplicationForm $saved): array
    {
        $links = [];
        foreach (self::LEAD_LINKS as $path => $link) {
            $links[] = ['path' => $path] + $link;
        }

        return ['links' => $links, 'overrides' => array_values((array) ($saved?->linked_overrides ?? []))];
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromModel(ApplicationForm $form): array
    {
        $data = [];

        foreach (self::SCALARS as $key => $type) {
            $value = $form->{$key};

            $data[$key] = match (true) {
                $value === null => null,
                $type === 'd' => Carbon::parse($value)->format('Y-m-d'),
                $type === 'n' => self::cleanAmount($value),
                default => (string) $value,
            };
        }

        foreach (self::JSON_FIELDS as $key) {
            $data[$key] = $form->{$key} ?? [];
        }

        return $data;
    }

    /**
     * Everything the Lead can supply, for a lead whose form was never saved.
     *
     * @return array<string, mixed>
     */
    public static function leadDefaults(Lead $lead): array
    {
        $data = [];
        foreach (self::derivedFromLead($lead) as $path => $value) {
            data_set($data, $path, $value);
        }

        // Savings / current account banks from the Bank Details tab.
        $existing = [];
        foreach ((array) $lead->bank_details as $bank) {
            $type = strtolower((string) ($bank['account_type'] ?? ''));
            $key = str_contains($type, 'saving') ? 'savings' : (str_contains($type, 'current') ? 'current' : null);
            $name = trim((string) ($bank['bank_name'] ?? ''));
            if ($key && $name !== '' && empty($existing[$key]['bank'])) {
                $existing[$key]['bank'] = $name;
            }
        }
        $data['existing_facilities'] = $existing;

        return $data;
    }

    /**
     * Fill every key so callers never test for missing indexes, and keep the
     * repeating groups to the shapes the template can hold.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function normalize(array $data): array
    {
        $out = [];

        foreach (self::SCALARS as $key => $type) {
            $value = $data[$key] ?? null;
            $out[$key] = ($value === null || $value === '') ? null : (is_scalar($value) ? (string) $value : null);
        }

        $out['associate_concerns'] = self::rows($data['associate_concerns'] ?? [], self::ASSOCIATE_KEYS, self::MAX_ASSOCIATES);
        $out['machinery'] = self::rows($data['machinery'] ?? [], self::MACHINERY_KEYS, self::MAX_MACHINERY);
        $out['existing_facilities'] = self::keyed($data['existing_facilities'] ?? [], array_keys(self::EXISTING_FACILITIES), self::FACILITY_KEYS);
        $out['proposed_facilities'] = self::keyed($data['proposed_facilities'] ?? [], array_keys(self::PROPOSED_FACILITIES), self::PROPOSED_KEYS);
        $out['performance'] = self::keyed($data['performance'] ?? [], array_keys(self::PERIODS), self::PERIOD_KEYS);
        $out['statutory'] = self::keyed($data['statutory'] ?? [], array_keys(self::OBLIGATIONS), ['status', 'remarks']);

        return $out;
    }

    /**
     * Required-for-generation fields that are still empty, as label strings.
     *
     * @param array<string, mixed> $data
     * @return array<int, string>
     */
    public static function missingRequired(array $data): array
    {
        $missing = [];
        foreach (self::REQUIRED_FOR_GENERATION as $key => $label) {
            if (blank($data[$key] ?? null)) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        $enums = [
            'category' => self::CATEGORIES,
            'constitution' => self::CONSTITUTIONS,
            'business_premises' => self::PREMISES,
            'is_registered' => self::YES_NO,
            'social_category' => self::SOCIAL_CATEGORIES,
            'minority_community' => self::MINORITIES,
            'sex' => array_keys(self::SEX),
        ];

        $rules = ['af' => ['nullable', 'array']];

        foreach (self::SCALARS as $key => $type) {
            $rules["af.$key"] = match ($type) {
                't' => ['nullable', 'string', 'max:1000'],
                'd' => ['nullable', 'date'],
                'n' => ['nullable', 'numeric', 'min:0', 'max:9999999999999'],
                default => ['nullable', 'string', 'max:255'],
            };

            if (isset($enums[$key])) {
                $rules["af.$key"][] = Rule::in($enums[$key]);
            }
        }

        $rules['af.pincode'] = ['nullable', 'regex:/^\d{6}$/'];
        $rules['af.mobile_country_code'] = ['nullable', 'regex:/^\d{1,4}$/'];

        foreach (['associate_concerns' => [self::MAX_ASSOCIATES, self::ASSOCIATE_KEYS], 'machinery' => [self::MAX_MACHINERY, self::MACHINERY_KEYS]] as $group => [$max, $keys]) {
            $rules["af.$group"] = ['nullable', 'array', "max:$max"];
            foreach ($keys as $k) {
                $rules["af.$group.*.$k"] = ['nullable', 'string', 'max:255'];
            }
        }

        $keyedGroups = [
            'existing_facilities' => self::FACILITY_KEYS,
            'proposed_facilities' => self::PROPOSED_KEYS,
            'performance' => self::PERIOD_KEYS,
        ];
        foreach ($keyedGroups as $group => $keys) {
            $rules["af.$group"] = ['nullable', 'array'];
            foreach ($keys as $k) {
                $rules["af.$group.*.$k"] = ['nullable', 'string', 'max:255'];
            }
        }

        $rules['af.statutory'] = ['nullable', 'array'];
        $rules['af.statutory.*.status'] = ['nullable', Rule::in(self::COMPLIANCE)];
        $rules['af.statutory.*.remarks'] = ['nullable', 'string', 'max:255'];

        return $rules;
    }

    /**
     * Lead's free-text constitution → one of the template's five options.
     *
     * @return array{0: ?string, 1: ?string}
     */
    public static function matchConstitution(?string $text): array
    {
        if ($text === null) {
            return [null, null];
        }

        $t = strtolower($text);

        return match (true) {
            str_contains($t, 'propriet') => ['Proprietary', null],
            str_contains($t, 'partner') || str_contains($t, 'llp') => ['Partnership', null],
            str_contains($t, 'pvt') || str_contains($t, 'private') => ['Pvt. Ltd.', null],
            str_contains($t, 'ltd') || str_contains($t, 'limited') => ['Ltd. Company', null],
            default => ['Others', $text],
        };
    }

    /**
     * "9,75,000" / "Rs. 975000" → "975000"; anything non-numeric → null.
     */
    public static function parseAmount(mixed $value): ?string
    {
        $stripped = preg_replace('/[^\d.]/', '', (string) $value);

        return ($stripped !== '' && is_numeric($stripped)) ? self::cleanAmount($stripped) : null;
    }

    public static function cleanAmount(mixed $value): string
    {
        $s = (string) $value;

        return str_contains($s, '.') ? rtrim(rtrim($s, '0'), '.') : $s;
    }

    /**
     * Drop rows with no content and renumber.
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

    /**
     * @return array<string, array<string, ?string>>
     */
    private static function keyed(mixed $rows, array $rowKeys, array $fieldKeys): array
    {
        $out = [];

        foreach ($rowKeys as $rk) {
            $row = is_array($rows[$rk] ?? null) ? $rows[$rk] : [];
            foreach ($fieldKeys as $fk) {
                $v = $row[$fk] ?? null;
                $out[$rk][$fk] = (is_scalar($v) && trim((string) $v) !== '') ? trim((string) $v) : null;
            }
        }

        return $out;
    }
}
