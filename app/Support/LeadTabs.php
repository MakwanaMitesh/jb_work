<?php

namespace App\Support;

/**
 * The eleven tabs of the Lead form, keyed by their permission module name (the `{module}` in the
 * existing `{module}.view` / `{module}.update` permissions — see PermissionSeeder::MODULES).
 *
 * This is the one place that maps those keys to everything else that names a tab differently:
 * the label shown on the form, PendencyService's module key (two differ), and the tab button id
 * in _form.blade.php. Order matches the order of the tabs on the form.
 */
class LeadTabs
{
    /**
     * @var array<string, array{label: string, pendency: string, dom: string}>
     */
    public const TABS = [
        'details_sheet' => ['label' => 'Details Sheet', 'pendency' => 'details_sheet', 'dom' => 'kyc'],
        'bank_loan' => ['label' => 'Pre Sanction', 'pendency' => 'bank_loan', 'dom' => 'bank-loan'],
        'application_form' => ['label' => 'Application Form', 'pendency' => 'application_form', 'dom' => 'application-form'],
        'documents' => ['label' => 'Documents', 'pendency' => 'documents', 'dom' => 'documents'],
        'sbi_stock_statement' => ['label' => 'Stock Statement', 'pendency' => 'stock_statement', 'dom' => 'stock-statement'],
        'or' => ['label' => 'Opinion Report', 'pendency' => 'opinion_report', 'dom' => 'opinion-report'],
        'cma' => ['label' => 'CMA', 'pendency' => 'cma', 'dom' => 'cma'],
        'dpr' => ['label' => 'DPR', 'pendency' => 'dpr', 'dom' => 'dpr'],
        'bank_details' => ['label' => 'Bank Details', 'pendency' => 'bank_details', 'dom' => 'bank'],
        'business_details' => ['label' => 'Business Details', 'pendency' => 'business_details', 'dom' => 'business'],
        'loan_details' => ['label' => 'Loan Details', 'pendency' => 'loan_details', 'dom' => 'loan'],
    ];

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_keys(self::TABS);
    }

    public static function label(string $key): string
    {
        return self::TABS[$key]['label'] ?? $key;
    }

    /**
     * @param array<int, string>|null $keys
     * @return array<int, string>
     */
    public static function labels(?array $keys): array
    {
        return array_map(fn ($k) => self::label($k), self::sorted($keys ?? []));
    }

    /**
     * Keeps only known tab keys, in form order.
     *
     * @param array<int, string> $keys
     * @return array<int, string>
     */
    public static function sorted(array $keys): array
    {
        return array_values(array_intersect(self::keys(), $keys));
    }

    public static function pendencyKey(string $key): string
    {
        return self::TABS[$key]['pendency'] ?? $key;
    }

    public static function keyForPendency(string $pendencyKey): ?string
    {
        foreach (self::TABS as $key => $tab) {
            if ($tab['pendency'] === $pendencyKey) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Tab key for a PendencyService anchor like '#bank-tab' (null when it names no Lead tab).
     */
    public static function keyForAnchor(?string $anchor): ?string
    {
        if (! $anchor || ! preg_match('/^#(.+)-tab$/', $anchor, $m)) {
            return null;
        }
        foreach (self::TABS as $key => $tab) {
            if ($tab['dom'] === $m[1]) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Permissions a role needs to work a tab: every action the module defines when it may edit the
     * tab, only the read actions when it may only look — plus every subsection "view", since a
     * subsection the role can't see would leave holes in a tab it is meant to fill in.
     *
     * @return array<int, string>
     */
    public static function permissionsFor(string $key, bool $editable): array
    {
        $readActions = ['view', 'download'];
        $actions = array_keys(\Database\Seeders\PermissionSeeder::MODULES[$key] ?? []);
        if (! $editable) {
            $actions = array_values(array_intersect($actions, $readActions));
        } elseif ($key === 'documents') {
            // Document deletion is a destructive admin action, not part of filling the tab in.
            $actions = array_values(array_diff($actions, ['delete', 'download_all']));
        }

        $names = array_map(fn ($a) => "{$key}.{$a}", $actions);
        foreach (array_keys(\Database\Seeders\PermissionSeeder::LEAD_MODULE_SUBSECTIONS[$key] ?? []) as $sub) {
            $names[] = "{$key}.{$sub}.view";
        }

        if ($key === 'bank_loan') {
            // The Pre Sanction tab owns the SBI inspection sheet generated from it.
            $names = array_merge($names, $editable ? ['inspection_sheet.generate', 'inspection_sheet.download'] : ['inspection_sheet.download']);
        }

        return $names;
    }
}
