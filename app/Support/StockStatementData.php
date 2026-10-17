<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\StockStatement;
use Carbon\Carbon;

/**
 * Inputs of the SBI Stock Statement: exactly the template's blue,
 * non-formula cells (see SbiStockStatementGenerator for where each lands).
 * Calculated cells (values, closing stock, summary totals, drawing power)
 * are never collected; they remain formulas in the workbook.
 */
class StockStatementData
{
    /** The template has two inventory rows (18 and 19). */
    public const MAX_ITEMS = 2;

    public const ITEM_KEYS = ['name', 'opening_qty', 'opening_rate', 'in_qty', 'in_rate', 'out_qty', 'out_rate', 'qty_in_transit'];

    /** Item fields that must be numbers; qty_in_transit is free text in the template (a dash). */
    public const ITEM_NUMERIC = ['opening_qty', 'opening_rate', 'in_qty', 'in_rate', 'out_qty', 'out_rate'];

    /**
     * s = string, d = date, n = decimal, i = whole number.
     *
     * @var array<string, string>
     */
    public const SCALARS = [
        'statement_date' => 'd',
        'company_name' => 's',
        'cc_limit' => 's',
        'place' => 's',
        'summary_amount' => 's',
        'debtor_name' => 's',
        'debtor_number' => 'i',
        'debtor_amount' => 'n',
        'debtor_more6_number' => 'i',
        'debtor_more6_amount' => 'n',
        'debtor2_name' => 's',
        'debtor2_up6_number' => 'i',
        'debtor2_up6_amount' => 'n',
        'debtor2_more6_number' => 'i',
        'debtor2_more6_amount' => 'n',
        'sundry_creditors_year_end' => 'n',
        'margin_stock' => 'n',
        'margin_book_debts' => 's',
        'sub_limit_stock' => 'n',
        'sub_limit_book_debts' => 'n',
    ];

    /**
     * Part B.1's age-analysis table (rows 32-35 of the template): three fixed
     * categories — the table has no "add category" of its own — plus a Total
     * row the workbook calculates, never entered.
     */
    public const AGE_CATEGORIES = [
        'govt' => 'Government and semi-government organisations',
        'associates' => 'Associates/affiliates/subsidiaries',
        'others' => 'Others',
    ];
    public const AGE_KEYS = ['up6_number', 'up6_amount', 'more6_number', 'more6_amount', 'bills_number', 'bills_amount'];
    public const AGE_NUMERIC_INT = ['up6_number', 'more6_number', 'bills_number'];

    /** Part C's Sales / Production / Consumption table (rows 47-50), by reporting period (columns F, I, L). */
    public const OTHER_INFO_METRICS = [
        'sales' => 'Sales',
        'production' => 'Production',
        'consumption_raw_material' => 'Consumption of Raw Materials',
        'consumption_stores' => 'Consumption of Stores',
    ];
    public const OTHER_INFO_PERIODS = [
        'projected' => 'Projected for Current Accounting Year',
        'upto_last_month' => 'Up to End of Last Month',
        'during_month' => 'During the Month Under Report',
    ];

    /**
     * Other lead form input -> Stock Statement field (same contract as
     * ApplicationFormData::LEAD_LINKS; transforms are shared).
     *
     * @var array<string, array{sources: array<int, string>, transform: string}>
     */
    public const LEAD_LINKS = [
        'company_name' => ['sources' => ['business_name', 'firm_name', 'name'], 'transform' => 'first'],
        'cc_limit' => ['sources' => ['cc_amount'], 'transform' => 'first'],
        'place' => ['sources' => ['city_id', 'business_location'], 'transform' => 'first'],
    ];

    /**
     * What the tab shows and the workbook is built from: lead-derived
     * defaults until the first save, then the saved values with only the
     * un-overridden linked fields following the Lead.
     *
     * @return array<string, mixed>
     */
    public static function for(Lead $lead): array
    {
        $saved = $lead->relationLoaded('stockStatement') ? $lead->stockStatement : $lead->stockStatement()->first();

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
    public static function linkConfig(?StockStatement $saved): array
    {
        $links = [];
        foreach (self::LEAD_LINKS as $field => $link) {
            $links[] = ['path' => $field] + $link;
        }

        return ['links' => $links, 'overrides' => array_values((array) ($saved?->linked_overrides ?? []))];
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromModel(StockStatement $row): array
    {
        $data = [];
        foreach (self::SCALARS as $key => $type) {
            $value = $row->{$key};
            $data[$key] = match (true) {
                $value === null => null,
                $type === 'd' => Carbon::parse($value)->format('Y-m-d'),
                $type === 'n' => ApplicationFormData::cleanAmount($value),
                default => (string) $value,
            };
        }
        $data['items'] = $row->items ?? [];
        $data['age_analysis'] = $row->age_analysis ?? [];
        $data['other_info'] = $row->other_info ?? [];

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

        $items = [];
        foreach (is_array($data['items'] ?? null) ? $data['items'] : [] as $row) {
            $row = is_array($row) ? $row : [];
            $clean = [];
            foreach (self::ITEM_KEYS as $k) {
                $v = $row[$k] ?? null;
                $clean[$k] = (is_scalar($v) && trim((string) $v) !== '') ? trim((string) $v) : null;
            }
            if (array_filter($clean, fn ($v) => $v !== null)) {
                $items[] = $clean;
            }
        }
        $out['items'] = array_slice($items, 0, self::MAX_ITEMS);

        $age = [];
        foreach (array_keys(self::AGE_CATEGORIES) as $category) {
            $row = is_array($data['age_analysis'][$category] ?? null) ? $data['age_analysis'][$category] : [];
            foreach (self::AGE_KEYS as $k) {
                $v = $row[$k] ?? null;
                $age[$category][$k] = (is_scalar($v) && trim((string) $v) !== '') ? trim((string) $v) : null;
            }
        }
        $out['age_analysis'] = $age;

        $other = [];
        foreach (array_keys(self::OTHER_INFO_METRICS) as $metric) {
            $row = is_array($data['other_info'][$metric] ?? null) ? $data['other_info'][$metric] : [];
            foreach (array_keys(self::OTHER_INFO_PERIODS) as $period) {
                $v = $row[$period] ?? null;
                $other[$metric][$period] = (is_scalar($v) && trim((string) $v) !== '') ? trim((string) $v) : null;
            }
        }
        $out['other_info'] = $other;

        return $out;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        $rules = ['ss' => ['nullable', 'array']];

        foreach (self::SCALARS as $key => $type) {
            $rules["ss.$key"] = match ($type) {
                'd' => ['nullable', 'date'],
                'n' => ['nullable', 'numeric', 'min:0', 'max:9999999999999'],
                'i' => ['nullable', 'integer', 'min:0', 'max:99999999'],
                default => ['nullable', 'string', 'max:255'],
            };
        }

        $rules['ss.items'] = ['nullable', 'array', 'max:' . self::MAX_ITEMS];
        $rules['ss.items.*.name'] = ['nullable', 'string', 'max:150'];
        foreach (self::ITEM_NUMERIC as $k) {
            $rules["ss.items.*.$k"] = ['nullable', 'numeric', 'min:0', 'max:9999999999999'];
        }
        $rules['ss.items.*.qty_in_transit'] = ['nullable', 'string', 'max:50'];

        foreach (array_keys(self::AGE_CATEGORIES) as $category) {
            foreach (self::AGE_KEYS as $k) {
                $rules["ss.age_analysis.$category.$k"] = in_array($k, self::AGE_NUMERIC_INT, true)
                    ? ['nullable', 'integer', 'min:0', 'max:99999999']
                    : ['nullable', 'numeric', 'min:0', 'max:9999999999999'];
            }
        }

        foreach (array_keys(self::OTHER_INFO_METRICS) as $metric) {
            foreach (array_keys(self::OTHER_INFO_PERIODS) as $period) {
                $rules["ss.other_info.$metric.$period"] = ['nullable', 'numeric', 'min:0', 'max:9999999999999'];
            }
        }

        return $rules;
    }
}
