<?php

namespace App\Support;

use App\Models\DprReport;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * Per-lead values of the Detailed Project Report. Static wording, headings,
 * and calculated totals (required-loan total, project cost, means of finance,
 * quotation total) are the document's own and are never collected.
 */
class DprData
{
    public const MAX_LOANS = 8;
    public const MAX_MACHINERY = 10;
    public const MAX_COLLATERAL = 10;

    /**
     * The DPR's photo sections reuse the Lead Documents upload system: these are
     * the document-type codes whose files the generator places in the report.
     */
    public const PHOTO_DOCUMENTS = [
        'machinery' => ['code' => 'machinery_photos', 'label' => 'Photos of New Machinery'],
        'final_product' => ['code' => 'final_product_photos', 'label' => 'Photos of Final Products'],
    ];

    /**
     * Stored and still written into the report by the generator, but no longer entered on the
     * Lead form. A save leaves their stored values alone.
     */
    public const NOT_ON_FORM = [
        'process_steps', 'process_output', 'process_description', 'wc_amount', 'wc_purpose', 'wc_raw_material',
        'wc_power_fuel', 'wc_labour', 'wc_receivables', 'market_potential',
    ];

    public const PROPERTY_TYPES = ['Residential', 'Commercial', 'Industrial', 'Land'];

    public const LOAN_KEYS = ['bank_name', 'loan_type', 'loan_amount', 'emi', 'outstanding_amount'];
    public const MACHINERY_KEYS = ['particulars', 'qty', 'amount'];
    public const COLLATERAL_KEYS = ['type', 'address', 'value'];

    /** Financial-details rows, in the template's order, and its three year columns. */
    public const FINANCIAL_ROWS = [
        'sales' => 'SALES (JOB WORK)',
        'purchase' => 'PURCHASE',
        'depreciation' => 'DEPRICIATION',
        'net_profit' => 'NET PROFIT AFTER INTEREST AND DEPRICIATION',
        'own_capital' => 'CAPITAL',
        'unsecured_loan' => 'UNSECURED LOAN',
        'capital' => 'TOTAL CAPITAL + UNSECURED LOAN',
        'investment' => 'INVESTMENT',
        'fixed_assets' => 'FIXED ASSETS BLOCK (Plant & Machinery)',
    ];
    public const FINANCIAL_PERIODS = ['p1' => 'F.Y. 23-24', 'p2' => 'F.Y. 24-25', 'p3' => 'A.Y 26-27 (provisional)'];

    /**
     * field => [type, max]: s = string, t = long text, d = date.
     *
     * @var array<string, array{0: string, 1: int}>
     */
    public const SCALARS = [
        'firm_name' => ['s', 255], 'constitution' => ['s', 100], 'applicant_name' => ['s', 255], 'business_address' => ['t', 1000],
        'pan' => ['s', 50], 'aadhaar' => ['s', 50], 'gender' => ['s', 20], 'mobile' => ['s', 30], 'email' => ['s', 255],
        'education' => ['s', 255], 'mother_name' => ['s', 255], 'date_of_birth' => ['d', 0], 'experience' => ['s', 255],
        'proprietor_history' => ['t', 2000],
        'activity' => ['t', 1000], 'business_profile' => ['t', 2000], 'land_and_factory' => ['t', 1000], 'udyam' => ['s', 100],
        'gst' => ['s', 100], 'fssai' => ['s', 100], 'manpower' => ['s', 100], 'connectivity' => ['s', 255], 'itr_filing' => ['s', 255],
        'present_bank' => ['s', 255], 'account_type' => ['s', 100], 'account_number' => ['s', 100], 'ifsc' => ['s', 50], 'location' => ['t', 1000],
        'term_required' => ['s', 50], 'term_contribution' => ['s', 50], 'cc_required' => ['s', 50], 'cc_contribution' => ['s', 50],
        'cost_land' => ['s', 50], 'cost_building' => ['s', 50], 'cost_machinery' => ['s', 50], 'cost_working_capital' => ['s', 50],
        'fin_promoter_capital' => ['s', 50], 'fin_term_loan' => ['s', 50], 'fin_cash_credit' => ['s', 50],
        'process_steps' => ['t', 1000], 'process_output' => ['t', 500], 'process_description' => ['t', 2000],
        'wc_amount' => ['s', 50], 'wc_purpose' => ['t', 1000], 'wc_raw_material' => ['s', 100], 'wc_power_fuel' => ['s', 100],
        'wc_labour' => ['s', 100], 'wc_receivables' => ['s', 100], 'market_potential' => ['t', 4000],
    ];

    public const JSON_FIELDS = ['current_loans', 'machinery', 'collateral', 'financials'];

    /** Amount-like inputs: digits with optional separators / decimals. */
    public const AMOUNT_FIELDS = [
        'term_required', 'term_contribution', 'cc_required', 'cc_contribution', 'cost_land', 'cost_building', 'cost_machinery',
        'cost_working_capital', 'fin_promoter_capital', 'fin_term_loan', 'fin_cash_credit', 'wc_amount',
    ];

    /**
     * Other lead form input -> DPR field. The lead's single Introduction feeds
     * both the proprietor history and the business profile, as the generator
     * always did. Current loans are linked row by row (see derivedFromLead).
     *
     * @var array<string, array{sources: array<int, string>, transform: string}>
     */
    public const LEAD_LINKS = [
        'firm_name' => ['sources' => ['business_name', 'firm_name'], 'transform' => 'first'],
        'constitution' => ['sources' => ['constitution_id'], 'transform' => 'first'],
        'applicant_name' => ['sources' => ['name'], 'transform' => 'first'],
        'business_address' => ['sources' => ['business_address', 'address'], 'transform' => 'first'],
        'pan' => ['sources' => ['pan_card'], 'transform' => 'first'],
        'aadhaar' => ['sources' => ['aadhar_card'], 'transform' => 'first'],
        'gender' => ['sources' => ['gender'], 'transform' => 'first'],
        'mobile' => ['sources' => ['mobile_number'], 'transform' => 'first'],
        'email' => ['sources' => ['email'], 'transform' => 'first'],
        'education' => ['sources' => ['education'], 'transform' => 'first'],
        'mother_name' => ['sources' => ['mother_name'], 'transform' => 'first'],
        'date_of_birth' => ['sources' => ['date_of_birth'], 'transform' => 'first'],
        'experience' => ['sources' => ['business_experience'], 'transform' => 'first'],
        'proprietor_history' => ['sources' => ['introduction'], 'transform' => 'first'],
        'activity' => ['sources' => ['business_activity'], 'transform' => 'first'],
        'business_profile' => ['sources' => ['introduction'], 'transform' => 'first'],
        'land_and_factory' => ['sources' => ['land_and_factory_building'], 'transform' => 'first'],
        'udyam' => ['sources' => ['udyam_registration'], 'transform' => 'first'],
        'gst' => ['sources' => ['gst_number'], 'transform' => 'first'],
        'fssai' => ['sources' => ['fssai_license'], 'transform' => 'first'],
        'manpower' => ['sources' => ['no_of_manpower'], 'transform' => 'first'],
        'connectivity' => ['sources' => ['connectivity'], 'transform' => 'first'],
        'location' => ['sources' => ['business_location', 'business_address'], 'transform' => 'first'],
        'present_bank' => ['sources' => ['bank_details[0][bank_name]', 'bank_id'], 'transform' => 'first'],
        'account_type' => ['sources' => ['bank_details[0][account_type]'], 'transform' => 'first'],
        'account_number' => ['sources' => ['bank_details[0][account_number]'], 'transform' => 'first'],
        'ifsc' => ['sources' => ['bank_details[0][ifsc_code]'], 'transform' => 'first'],
        'term_required' => ['sources' => ['term_loan_amount'], 'transform' => 'first'],
        'cc_required' => ['sources' => ['cc_amount'], 'transform' => 'first'],
        'fin_term_loan' => ['sources' => ['term_loan_amount'], 'transform' => 'first'],
        'fin_cash_credit' => ['sources' => ['cc_amount'], 'transform' => 'first'],
        'wc_amount' => ['sources' => ['cc_amount'], 'transform' => 'first'],
    ];

    /** The lead form's Current Loans rows that the DPR's loan table mirrors. */
    public const LOANS_LINK = 'current_loans';

    /**
     * @return array<string, mixed>
     */
    public static function for(Lead $lead): array
    {
        $saved = $lead->relationLoaded('dprReport') ? $lead->dprReport : $lead->dprReport()->first();

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
     * @return array<string, mixed> field => value (current_loans => rows)
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

        $out[self::LOANS_LINK] = self::rows((array) ($lead->current_loans ?? []), self::LOAN_KEYS, self::MAX_LOANS);

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
            $submitted = $data[$field] ?? null;
            $same = is_array($derived)
                ? json_encode($submitted ?: []) === json_encode($derived)
                : (string) $submitted === (string) $derived;
            if (! $same) {
                $overrides[] = $field;
            }
        }

        return $overrides;
    }

    /**
     * @return array<string, mixed>
     */
    public static function linkConfig(?DprReport $saved): array
    {
        $links = [];
        foreach (self::LEAD_LINKS as $field => $link) {
            $links[] = ['path' => $field] + $link;
        }

        return [
            'links' => $links,
            'loanKeys' => self::LOAN_KEYS,
            'overrides' => array_values((array) ($saved?->linked_overrides ?? [])),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromModel(DprReport $row): array
    {
        $data = [];
        foreach (self::SCALARS as $key => [$type]) {
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
        foreach (self::SCALARS as $key => $meta) {
            $value = $data[$key] ?? null;
            $out[$key] = (is_scalar($value) && trim((string) $value) !== '') ? trim((string) $value) : null;
        }

        $out['current_loans'] = self::rows($data['current_loans'] ?? [], self::LOAN_KEYS, self::MAX_LOANS);
        $out['machinery'] = self::rows($data['machinery'] ?? [], self::MACHINERY_KEYS, self::MAX_MACHINERY);
        $out['collateral'] = self::rows($data['collateral'] ?? [], self::COLLATERAL_KEYS, self::MAX_COLLATERAL);

        $financials = [];
        foreach (array_keys(self::FINANCIAL_ROWS) as $row) {
            foreach (array_keys(self::FINANCIAL_PERIODS) as $period) {
                $v = $data['financials'][$row][$period] ?? null;
                $financials[$row][$period] = (is_scalar($v) && trim((string) $v) !== '') ? trim((string) $v) : null;
            }
        }
        // The total is the sum of its two parts whenever it was not given.
        foreach (array_keys(self::FINANCIAL_PERIODS) as $period) {
            if ($financials['capital'][$period] === null) {
                $parts = array_filter([self::amount($financials['own_capital'][$period]), self::amount($financials['unsecured_loan'][$period])], fn ($n) => $n !== null);
                $financials['capital'][$period] = $parts ? rtrim(rtrim(number_format(array_sum($parts), 2, '.', ''), '0'), '.') : null;
            }
        }
        $out['financials'] = $financials;

        return $out;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        $rules = ['dpr' => ['nullable', 'array']];

        foreach (self::SCALARS as $key => [$type, $max]) {
            $rules["dpr.$key"] = $type === 'd' ? ['nullable', 'date'] : ['nullable', 'string', "max:$max"];
        }
        $rules['dpr.email'][] = 'email';
        foreach (self::AMOUNT_FIELDS as $key) {
            $rules["dpr.$key"][] = 'regex:/^[\d,.\s]+$/';
        }

        foreach (['current_loans' => [self::MAX_LOANS, self::LOAN_KEYS], 'machinery' => [self::MAX_MACHINERY, self::MACHINERY_KEYS], 'collateral' => [self::MAX_COLLATERAL, self::COLLATERAL_KEYS]] as $group => [$max, $keys]) {
            $rules["dpr.$group"] = ['nullable', 'array', "max:$max"];
            foreach ($keys as $k) {
                $rules["dpr.$group.*.$k"] = ['nullable', 'string', 'max:500'];
            }
        }
        $rules['dpr.collateral.*.type'][] = Rule::in(self::PROPERTY_TYPES);

        $rules['dpr.financials'] = ['nullable', 'array'];
        $rules['dpr.financials.*.*'] = ['nullable', 'string', 'max:100'];

        return $rules;
    }

    /**
     * Digits-only parse for totals: "9,75,000" => 975000.0; null when not a number.
     */
    public static function amount(?string $value): ?float
    {
        $parsed = ApplicationFormData::parseAmount($value);

        return $parsed === null ? null : (float) $parsed;
    }

    /**
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
