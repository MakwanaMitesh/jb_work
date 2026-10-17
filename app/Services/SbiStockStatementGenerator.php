<?php

namespace App\Services;

use App\Models\Lead;
use App\Services\Concerns\ManipulatesXlsxSharedStrings;
use App\Services\Concerns\RecalculatesXlsxOnOpen;
use App\Services\Concerns\WritesXlsxCells;
use App\Support\StockStatementData;
use Carbon\Carbon;
use RuntimeException;
use ZipArchive;

/**
 * Generates a Lead-specific SBI Stock Statement (Annexure-FSM-3) from the
 * master template by copying the .xlsx file as-is and rewriting only
 * specific cells. Every other zip entry (styles, theme, fonts, printer
 * settings, calc chain, etc.) is left byte-for-byte untouched, and the
 * master template itself is never opened for writing.
 *
 * The template has two kinds of demo content:
 *  - A handful of shared-string prose values (company name, statement
 *    date, place, signature date, a CC-limit summary line) — replaced by
 *    substring match within xl/sharedStrings.xml, preserving whatever
 *    static wrapper text surrounds each value.
 *  - Two fully worked example stock/book-debt rows (item name + qty/rate
 *    columns, most of which are formula-derived from just two raw input
 *    cells per row) plus the summary table rows that total them. None of
 *    this has any corresponding Lead data (Leads don't track itemized
 *    inventory or individual debtors), so every cell in these rows —
 *    including the formulas — is cleared to a bare, styled-but-empty cell.
 *    Clearing the formula (not just its cached value) is necessary: a
 *    formula like "=B18*C18" against blank inputs still evaluates to 0,
 *    which would violate the "never show 0" requirement.
 *
 * The blue, non-formula cells are the form's inputs (StockStatementData): after
 * the demo content is cleared, whatever the Lead's Stock Statement holds is
 * written back into those cells, and the template's own formulas are re-laid
 * over them (with fresh cached values) wherever their inputs exist. Cells
 * whose inputs are missing stay empty instead of showing 0.
 */
class SbiStockStatementGenerator
{
    use ManipulatesXlsxSharedStrings;
    use RecalculatesXlsxOnOpen;
    use WritesXlsxCells;

    private const TEMPLATE_PATH = 'upload/SBI_stock_statement/SBI_Stock_statement.xlsx';

    /**
     * Worksheet cells (by A1 reference) holding the two demo item rows, the
     * demo debtor row, and the summary-table rows calculated from them.
     * None have a corresponding Lead data source, so all are cleared.
     */
    private const CELLS_TO_CLEAR = [
        // Row 18: demo raw-material item "sopari (20kg bori)" — name +
        // opening/in/out/closing qty, unit rate and value columns.
        'A18', 'B18', 'C18', 'D18', 'E18', 'F18', 'G18', 'H18', 'I18', 'J18', 'K18', 'L18', 'M18', 'N18',
        // Row 19: demo item "soft drinks(car)", same columns, plus the two
        // "Total book debts" helper cells that sum rows 18+19.
        'A19', 'B19', 'C19', 'D19', 'E19', 'F19', 'G19', 'H19', 'I19', 'J19', 'K19', 'L19', 'M19', 'N19', 'O19', 'Q19',
        // Row 43: demo debtor "other debtors" (Number / Amount).
        'B43', 'H43', 'J43',
        // Row 51: demo "Year end:" sundry-creditors placeholder.
        'I51',
        // Summary table rows 109 (Raw materials) and 111 (Book-debts),
        // both entirely formula-derived from the rows above.
        'F109', 'H109', 'I109', 'J109', 'N109',
        'F111', 'H111', 'I111', 'J111', 'N111',
        // Row 112: Total of (i) to (vi), derived from 106–111.
        'F112', 'H112', 'J112', 'L112', 'N112',
        // Sub-limit constants (blue "0") and the book-debt margin text ("50% "):
        // inputs now, so they start empty like every other demo value.
        'L109', 'L111', 'I111',
    ];

    public function generate(Lead $lead): string
    {
        $templatePath = public_path(self::TEMPLATE_PATH);

        if (! is_file($templatePath)) {
            throw new RuntimeException('SBI Stock Statement master template not found at ' . self::TEMPLATE_PATH);
        }

        $relativeDir = 'sbi-stock-statement/' . $lead->id;
        $relativePath = $relativeDir . '/SBI_Stock_Statement.xlsx';
        $destPath = storage_path('app/public/' . $relativePath);

        if (! is_dir(dirname($destPath))) {
            mkdir(dirname($destPath), 0755, true);
        }

        // Copy the template first — every mutation below happens on the
        // copy only, so the original file is never opened for writing.
        if (! copy($templatePath, $destPath)) {
            throw new RuntimeException('Failed to copy SBI Stock Statement template.');
        }

        $zip = new ZipArchive();
        if ($zip->open($destPath) !== true) {
            throw new RuntimeException('Failed to open generated SBI Stock Statement workbook.');
        }

        $sharedStrings = $zip->getFromName('xl/sharedStrings.xml');
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sharedStrings === false || $sheet === false) {
            $zip->close();
            throw new RuntimeException('SBI Stock Statement template is missing expected worksheet parts.');
        }

        $data = StockStatementData::for($lead);

        foreach ($this->substringReplacements($data) as $needle => $replacement) {
            $sharedStrings = $this->replaceWithinTextTags($sharedStrings, $needle, $replacement);
        }
        $sharedStrings = $this->fillBlankRuns($sharedStrings, $data);

        foreach (self::CELLS_TO_CLEAR as $ref) {
            $sheet = $this->clearWorksheetCell($sheet, $ref);
        }
        $sheet = $this->fillInputs($sheet, $data);
        $sheet = $this->fillAgeAnalysis($sheet, $data);
        $sheet = $this->fillOtherInfo($sheet, $data);

        $zip->addFromString('xl/sharedStrings.xml', $sharedStrings);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $this->forceRecalculation($zip);
        $zip->close();

        return $relativePath;
    }

    /**
     * Each needle is unique within the template's shared strings (verified
     * by inspection), so a plain substring match is safe. Every entry
     * always fires (no filtering of empty values): this template's own
     * instructions say unavailable fields must be left BLANK, not shown as
     * the template's placeholder-looking demo text, so an empty
     * replacement here genuinely blanks the value rather than leaving
     * "city name" / "SUVARN SALES AGENCY" visible. The statement date falls
     * back to today when none was entered (it has always done so).
     *
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function substringReplacements(array $data): array
    {
        $date = $data['statement_date'] ? Carbon::parse($data['statement_date']) : Carbon::now();

        return [
            'SUVARN SALES AGENCY' => (string) $data['company_name'],
            '31/07/2026' => $date->format('d/m/Y'),
            '9,75,000' => (string) $data['cc_limit'],
            'city name' => (string) $data['place'],
            '11/11/1111' => $date->format('d/m/Y'),
        ];
    }

    /**
     * The two underscore blanks in the summary heading ("dated ____" and
     * "(Rs. ____ /-)") are blue fill-ins. They are filled from the form and
     * otherwise left as the printed blanks they are. Addressed by shared
     * string index because the two runs overlap as plain substrings.
     *
     * @param array<string, mixed> $data
     */
    private function fillBlankRuns(string $xml, array $data): string
    {
        $date = $data['statement_date'] ? Carbon::parse($data['statement_date'])->format('d/m/Y') : null;

        return $this->replaceUnderscoreRuns($xml, [
            109 => $date,
            110 => $data['summary_amount'],
        ]);
    }

    /**
     * @param array<int, ?string> $indexToValue shared string index => replacement (null keeps the blank)
     */
    private function replaceUnderscoreRuns(string $xml, array $indexToValue): string
    {
        $i = -1;

        return preg_replace_callback('/<si>(.*?)<\/si>/s', function (array $m) use (&$i, $indexToValue) {
            $i++;
            $value = $indexToValue[$i] ?? null;
            if ($value === null || $value === '') {
                return $m[0];
            }

            $escaped = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

            return '<si>' . preg_replace_callback('/(<t[^>]*>)([^<]*?)(_{3,})([^<]*?)(<\/t>)/s', fn (array $t) => $t[1] . $t[2] . $escaped . $t[4] . $t[5], $m[1], 1) . '</si>';
        }, $xml) ?? $xml;
    }

    // ---- blue input cells ----------------------------------------------

    /** Inventory rows; the formula columns are re-laid by fillInputs(). */
    private const ITEM_ROWS = [18, 19];

    /**
     * The template's blue, non-formula worksheet cells: exactly what the
     * Stock Statement form collects. Blue formula cells (values, closing
     * stock, totals, drawing power) are deliberately absent.
     */
    public const INPUT_CELLS = [
        'A18', 'B18', 'C18', 'E18', 'F18', 'H18', 'I18', 'L18',
        'A19', 'B19', 'C19', 'E19', 'F19', 'H19', 'I19', 'L19',
        'H43', 'J43',
        'I109', 'L109', 'I111', 'L111',
        ...self::NON_BLUE_INPUT_CELLS,
    ];

    /**
     * Part B.1 (age analysis), the rest of Major Debtors (rows 43-44) and
     * Part C (Sales/Production/Consumption, Sundry Creditors) are bordered
     * cells with no formula in the master — genuine inputs, but never
     * styled blue like the rest of the form's inputs. Two (B43, I51) hold
     * demo text rather than being blank; CELLS_TO_CLEAR removes it first.
     */
    public const NON_BLUE_INPUT_CELLS = [
        'B43', 'L43', 'N43', 'B44', 'H44', 'J44', 'L44', 'N44',
        'H32', 'I32', 'J32', 'K32', 'N32', 'O32',
        'H33', 'I33', 'J33', 'K33', 'N33', 'O33',
        'H34', 'I34', 'J34', 'K34', 'N34', 'O34',
        'F47', 'I47', 'L47', 'F48', 'I48', 'L48', 'F49', 'I49', 'L49', 'F50', 'I50', 'L50', 'I51',
    ];

    /**
     * In the master these are formulas (20% / 85% / 25% / 115% of the opening
     * stock, demo logic); a real statement types them, so the form collects
     * them and they are written as values. Their dependent value and closing
     * formulas stay formulas.
     */
    public const FORMULA_CELLS_MADE_INPUTS = ['E18', 'F18', 'H18', 'I18', 'E19', 'F19', 'H19', 'I19'];

    /** Blue text runs inside shared strings (statement heading, company, limit, summary blanks, place, date). */
    public const INPUT_TEXT_RUNS = [
        'N2' => 'statement_date',
        'D4' => 'company_name',
        'G6' => 'cc_limit',
        'B101' => 'statement_date',
        'L101' => 'summary_amount',
        'D115' => 'place',
        'D116' => 'statement_date',
    ];

    /**
     * @param array<string, mixed> $d
     */
    private function fillInputs(string $xml, array $d): string
    {
        $num = fn ($v) => is_numeric($v) ? (float) $v : null;
        $nRow = [];

        foreach (self::ITEM_ROWS as $i => $r) {
            $item = $d['items'][$i] ?? [];
            $b = $num($item['opening_qty'] ?? null);
            $c = $num($item['opening_rate'] ?? null);
            $e = $num($item['in_qty'] ?? null);
            $f = $num($item['in_rate'] ?? null);
            $h = $num($item['out_qty'] ?? null);
            $in = $num($item['out_rate'] ?? null);

            $xml = $this->putText($xml, "A{$r}", $item['name'] ?? null);
            $xml = $this->putText($xml, "L{$r}", $item['qty_in_transit'] ?? null);
            foreach (['B' => $b, 'C' => $c, 'E' => $e, 'F' => $f, 'H' => $h, 'I' => $in] as $col => $v) {
                $xml = $this->putNumber($xml, "{$col}{$r}", $v === null ? null : $this->plain($v));
            }

            // The template's value/closing formulas, only where their inputs exist.
            if ($b !== null && $c !== null) {
                $xml = $this->putFormula($xml, "D{$r}", "B{$r}*C{$r}", $this->plain($b * $c));
            }
            if ($e !== null && $f !== null) {
                $xml = $this->putFormula($xml, "G{$r}", "E{$r}*F{$r}", $this->plain($e * $f));
            }
            if ($h !== null && $in !== null) {
                $xml = $this->putFormula($xml, "J{$r}", "H{$r}*I{$r}", $this->plain($h * $in));
            }
            $closingQty = ($b !== null || $e !== null || $h !== null) ? ($b ?? 0) + ($e ?? 0) - ($h ?? 0) : null;
            if ($closingQty !== null) {
                $xml = $this->putFormula($xml, "K{$r}", "B{$r}+E{$r}-H{$r}", $this->plain($closingQty));
            }
            if ($in !== null) {
                $xml = $this->putFormula($xml, "M{$r}", "I{$r}", $this->plain($in));
            }
            if ($closingQty !== null && $in !== null) {
                $nRow[$r] = $closingQty * $in;
                $xml = $this->putFormula($xml, "N{$r}", "K{$r}*M{$r}", $this->plain($nRow[$r]));
            }
        }

        // Part B: Major Debtors, rows 43 and 44 — the master's "other debtors" demo label on row 43 is
        // cleared like every other demo value, and filled from the form like any other named debtor.
        $debtor1Up6 = $num($d['debtor_amount']);
        $xml = $this->putText($xml, 'B43', $d['debtor_name']);
        $xml = $this->putNumber($xml, 'H43', $d['debtor_number']);
        $xml = $this->putNumber($xml, 'J43', $debtor1Up6 === null ? null : $this->plain($debtor1Up6));
        $xml = $this->putNumber($xml, 'L43', $d['debtor_more6_number']);
        $more6 = $num($d['debtor_more6_amount']);
        $xml = $this->putNumber($xml, 'N43', $more6 === null ? null : $this->plain($more6));

        $debtor2Up6 = $num($d['debtor2_up6_amount']);
        $xml = $this->putText($xml, 'B44', $d['debtor2_name']);
        $xml = $this->putNumber($xml, 'H44', $d['debtor2_up6_number']);
        $xml = $this->putNumber($xml, 'J44', $debtor2Up6 === null ? null : $this->plain($debtor2Up6));
        $xml = $this->putNumber($xml, 'L44', $d['debtor2_more6_number']);
        $more6b = $num($d['debtor2_more6_amount']);
        $xml = $this->putNumber($xml, 'N44', $more6b === null ? null : $this->plain($more6b));

        // The template's own book-debts total (F111) sums both rows' "up to 6 months" amount.
        $debtorAmount = ($debtor1Up6 !== null || $debtor2Up6 !== null) ? ($debtor1Up6 ?? 0) + ($debtor2Up6 ?? 0) : null;

        // Summary: (iv) finished goods from the inventory, (vi) book debts from the debtors.
        $stockValue = $nRow ? array_sum($nRow) : null;
        $margin = $num($d['margin_stock']);
        $stockAdvance = $stockDraw = $stockMargin = null;

        if ($stockValue !== null) {
            $xml = $this->putFormula($xml, 'F109', 'SUM(N18:N19)', $this->plain($stockValue));
        }
        $xml = $this->putNumber($xml, 'I109', $margin === null ? null : $this->plain($margin / 100));
        if ($stockValue !== null && $margin !== null) {
            $stockMargin = round($stockValue * $margin / 100);
            $stockAdvance = $stockValue - $stockMargin;
            $xml = $this->putFormula($xml, 'H109', 'ROUND(F109*I109,0)', $this->plain($stockMargin));
            $xml = $this->putFormula($xml, 'J109', 'F109-H109', $this->plain($stockAdvance));
            $xml = $this->putFormula($xml, 'N109', 'J109', $this->plain($stockAdvance));
        }
        $subStock = $num($d['sub_limit_stock']);
        $xml = $this->putNumber($xml, 'L109', $subStock === null ? null : $this->plain($subStock));

        $debtMargin = $debtAdvance = null;
        if ($debtorAmount !== null) {
            $debtMargin = round($debtorAmount * 0.5);
            $debtAdvance = $debtorAmount - $debtMargin;
            $xml = $this->putFormula($xml, 'F111', 'SUM(J43:K44)', $this->plain($debtorAmount));
            $xml = $this->putFormula($xml, 'H111', 'ROUND(F111*50%,0)', $this->plain($debtMargin));
            $xml = $this->putFormula($xml, 'J111', 'F111-H111', $this->plain($debtAdvance));
            $xml = $this->putFormula($xml, 'N111', 'J111', $this->plain($debtAdvance));
        }
        $xml = $this->putText($xml, 'I111', $d['margin_book_debts']);
        $subDebt = $num($d['sub_limit_book_debts']);
        $xml = $this->putNumber($xml, 'L111', $subDebt === null ? null : $this->plain($subDebt));

        // Row 112 totals.
        $totals = [
            'F' => [$stockValue, $debtorAmount],
            'H' => [$stockMargin, $debtMargin],
            'J' => [$stockAdvance, $debtAdvance],
            'L' => [$subStock, $subDebt],
            'N' => [$stockAdvance, $debtAdvance],
        ];
        $ranges = ['F' => 'F106:G111', 'H' => 'H106:H111', 'J' => 'J106:K111', 'L' => 'L106:M111', 'N' => 'N106:O111'];
        foreach ($totals as $col => $parts) {
            $present = array_filter($parts, fn ($v) => $v !== null);
            if ($present) {
                $xml = $this->putFormula($xml, "{$col}112", "SUM({$ranges[$col]})", $this->plain(array_sum($present)));
            }
        }

        return $xml;
    }

    /** Part B.1: age analysis of book debts (rows 32-34, by category), with row 35's Total calculated. */
    private const AGE_ROWS = ['govt' => 32, 'associates' => 33, 'others' => 34];

    /**
     * @param array<string, mixed> $d
     */
    private function fillAgeAnalysis(string $xml, array $d): string
    {
        $num = fn ($v) => is_numeric($v) ? (float) $v : null;
        $totals = array_fill_keys(['H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'], []);

        foreach (self::AGE_ROWS as $category => $r) {
            $row = $d['age_analysis'][$category] ?? [];
            $up6n = $num($row['up6_number'] ?? null);
            $up6a = $num($row['up6_amount'] ?? null);
            $more6n = $num($row['more6_number'] ?? null);
            $more6a = $num($row['more6_amount'] ?? null);
            $billsn = $num($row['bills_number'] ?? null);
            $billsa = $num($row['bills_amount'] ?? null);

            $xml = $this->putNumber($xml, "H{$r}", $up6n === null ? null : $this->plain($up6n));
            $xml = $this->putNumber($xml, "I{$r}", $up6a === null ? null : $this->plain($up6a));
            $xml = $this->putNumber($xml, "J{$r}", $more6n === null ? null : $this->plain($more6n));
            $xml = $this->putNumber($xml, "K{$r}", $more6a === null ? null : $this->plain($more6a));
            $xml = $this->putNumber($xml, "N{$r}", $billsn === null ? null : $this->plain($billsn));
            $xml = $this->putNumber($xml, "O{$r}", $billsa === null ? null : $this->plain($billsa));

            // Total book debts (L, M): up to 6 months plus more than 6 months, only once either is entered.
            if ($up6n !== null || $more6n !== null) {
                $totalN = ($up6n ?? 0) + ($more6n ?? 0);
                $xml = $this->putFormula($xml, "L{$r}", "H{$r}+J{$r}", $this->plain($totalN));
                $totals['L'][] = $totalN;
            }
            if ($up6a !== null || $more6a !== null) {
                $totalA = ($up6a ?? 0) + ($more6a ?? 0);
                $xml = $this->putFormula($xml, "M{$r}", "I{$r}+K{$r}", $this->plain($totalA));
                $totals['M'][] = $totalA;
            }
            foreach (['H' => $up6n, 'I' => $up6a, 'J' => $more6n, 'K' => $more6a, 'N' => $billsn, 'O' => $billsa] as $col => $v) {
                if ($v !== null) {
                    $totals[$col][] = $v;
                }
            }
        }

        // Row 35: Total, calculated from rows 32-34 — never entered.
        foreach ($totals as $col => $values) {
            if ($values) {
                $xml = $this->putFormula($xml, "{$col}35", "SUM({$col}32:{$col}34)", $this->plain(array_sum($values)));
            }
        }

        return $xml;
    }

    /** Part C: Sales / Production / Consumption, by reporting period (rows 47-50; columns F, I, L). */
    private const OTHER_INFO_ROWS = ['sales' => 47, 'production' => 48, 'consumption_raw_material' => 49, 'consumption_stores' => 50];
    private const OTHER_INFO_COLUMNS = ['projected' => 'F', 'upto_last_month' => 'I', 'during_month' => 'L'];

    /**
     * @param array<string, mixed> $d
     */
    private function fillOtherInfo(string $xml, array $d): string
    {
        $num = fn ($v) => is_numeric($v) ? (float) $v : null;

        foreach (self::OTHER_INFO_ROWS as $metric => $r) {
            foreach (self::OTHER_INFO_COLUMNS as $period => $col) {
                $v = $num($d['other_info'][$metric][$period] ?? null);
                $xml = $this->putNumber($xml, "{$col}{$r}", $v === null ? null : $this->plain($v));
            }
        }

        $yearEnd = $num($d['sundry_creditors_year_end']);
        $xml = $this->putNumber($xml, 'I51', $yearEnd === null ? null : $this->plain($yearEnd));

        return $xml;
    }

    private function plain(float|int $n): string
    {
        $n = round($n, 2);

        return (string) (floor($n) == $n ? (int) $n : $n);
    }
}
