<?php

namespace App\Services;

use App\Models\Lead;
use App\Services\Concerns\FillsDocxTables;
use App\Services\Concerns\ManipulatesDocxRuns;
use App\Support\DprData;
use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

/**
 * Generates a Lead-specific Detailed Project Report (DPR) from the master
 * template by copying the .docx file as-is and rewriting only the
 * Lead-specific text inside word/document.xml. Every other zip entry
 * (styles, theme, fonts, settings, numbering, etc.) is left byte-for-byte
 * untouched, and the master template itself is never opened for writing.
 *
 * The template mixes two kinds of "variable" content:
 *  - Free-floating prose strings (cover page, conclusion paragraph, a couple
 *    of narrative sentences) — handled by exact substring replacement so
 *    surrounding sentence structure/punctuation is preserved untouched.
 *  - Structured table rows (Basic Information, Business Profile, Means of
 *    Finance, Current Loan Details) — handled by locating each row via its
 *    label cell and filling the adjacent value cell, since the value cells
 *    are empty placeholders with nothing to substring-match against.
 *
 * The values come from the lead's DPR (DprData): the lead's own data, kept in
 * step with the DPR tab, plus whatever was entered there. The template's demo
 * customer (Kirti Mini Oil Mill / Panth Creation, its loans, machinery,
 * collateral, figures, process chart) is always cleared; a field with no data
 * is left empty rather than invented. Totals are calculated here from the
 * entered amounts. Static wording, headings and layout are untouched.
 */
class DprGenerator
{
    use FillsDocxTables;
    use ManipulatesDocxRuns;

    private const TEMPLATE_PATH = 'upload/DPR/demo_dpr.docx';
    private const NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    public function generate(Lead $lead): string
    {
        $templatePath = public_path(self::TEMPLATE_PATH);

        if (! is_file($templatePath)) {
            throw new RuntimeException('DPR master template not found at ' . self::TEMPLATE_PATH);
        }

        $relativeDir = 'dpr/' . $lead->id;
        $relativePath = $relativeDir . '/DPR.docx';
        $destPath = storage_path('app/public/' . $relativePath);

        if (! is_dir(dirname($destPath))) {
            mkdir(dirname($destPath), 0755, true);
        }

        // Copy the template first — every mutation below happens on the
        // copy only, so the original file is never opened for writing.
        if (! copy($templatePath, $destPath)) {
            throw new RuntimeException('Failed to copy DPR template.');
        }

        $zip = new ZipArchive();
        if ($zip->open($destPath) !== true) {
            throw new RuntimeException('Failed to open generated DPR document.');
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            throw new RuntimeException('DPR template is missing word/document.xml.');
        }

        $d = DprData::for($lead);

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', self::NS);
        $xpath->registerNamespace('mc', 'http://schemas.openxmlformats.org/markup-compatibility/2006');

        foreach ($this->substringReplacements($d) as $needle => $replacement) {
            $this->replacePhraseAcrossRuns($dom, $xpath, $needle, $replacement);
        }

        $this->fillCoverLocation($xpath, $d);
        $this->fillSimpleLabelRows($dom, $xpath, $d);
        $this->fillBusinessProfileRows($dom, $xpath, $d);
        $this->fillCurrentLoans($dom, $xpath, $d);
        $this->fillRequiredLoan($dom, $xpath, $d);
        $this->fillProjectCostAndFinance($dom, $xpath, $d);
        $this->fillQuotation($dom, $xpath, $d);
        $this->fillManufacturingProcess($dom, $xpath, $d);
        $this->fillCollateral($dom, $xpath, $d);
        $this->fillMarketPotential($dom, $xpath, $d);
        $this->fillFinancialTable($dom, $xpath, $d);
        $this->stripAllHighlighting($xpath);
        $this->embedPhotos($dom, $xpath, $zip, $lead);

        $zip->addFromString('word/document.xml', $dom->saveXML());
        $zip->close();

        return $relativePath;
    }

    /**
     * The demo customer's free-floating strings (cover page, conclusion, the
     * working-capital sentence, the location block). Every entry always fires:
     * an empty replacement blanks the demo value and leaves the surrounding
     * static wording alone. Ordered most-specific-first.
     *
     * @param array<string, mixed> $d
     * @return array<string, string>
     */
    private function substringReplacements(array $d): array
    {
        $term = $this->money($d['term_required']);
        $cc = $this->money($d['cc_required']);
        $constitution = (string) $d['constitution'];

        $needles = [];

        // "(Proprietorship Firm)" and "Proprietorship Firm" lose their words along with the demo value.
        if ($constitution === '') {
            $needles['(Proprietorship Firm)'] = '';
            $needles['Proprietorship Firm'] = '';
        }

        $needles += [
            'Manufacturing of Groundnut Oil' => (string) $d['activity'],
            'Manufacturing of groundnut oil mill' => (string) $d['activity'],
            'KIRTI MINI OIL MILL' => (string) $d['firm_name'],
            'PANTH CREATION' => (string) $d['firm_name'],
            'Proprietorship' => $constitution,

            // Proposed finance (cover) and the conclusion's sanction sentence.
            ' ₹30.00 Lakh' => $term !== '' ? ' ' . $term : '',
            ' ₹10.00 Lakh' => $cc !== '' ? ' ' . $cc : '',
            '₹30.00 lakh' => $term,
            '₹10.00 lakh.' => $cc !== '' ? $cc . '.' : '.',

            // Working-capital sentence.
            '₹10.00 lakh' => $this->money($d['wc_amount']),

            '(name)' => (string) $d['applicant_name'],
            '(address)' => (string) $d['business_address'],
        ];

        // Working-capital components slot into the sentence's own wording as "(amount)".
        if (filled($d['wc_purpose'])) {
            $needles['raw material procurement, power & fuel expenses, labour charges and receivables'] = $this->oneLine((string) $d['wc_purpose']);
        } else {
            foreach ([
                'raw material procurement' => $d['wc_raw_material'],
                'power & fuel expenses' => $d['wc_power_fuel'],
                'labour charges' => $d['wc_labour'],
                'and receivables' => $d['wc_receivables'],
            ] as $phrase => $amount) {
                if (filled($amount)) {
                    $needles[$phrase] = $phrase . ' (' . $this->money($amount) . ')';
                }
            }
        }

        return $needles;
    }

    /**
     * The cover's "Location:" block is two demo lines built from several runs, two
     * of which also hold line breaks (so they can't be matched as plain text). The
     * first line becomes the entered location, the second is emptied.
     *
     * @param array<string, mixed> $d
     */
    private function fillCoverLocation(DOMXPath $xpath, array $d): void
    {
        $paragraph = null;
        foreach ($xpath->query('//w:p') as $p) {
            $text = $this->cellText($xpath, $p);
            if (str_contains($text, 'Location') && str_contains($text, 'Village')) {
                $paragraph = $p;
                break;
            }
        }
        if (! $paragraph) {
            return;
        }

        $runs = iterator_to_array($xpath->query('./w:r', $paragraph));
        $village = $district = null;
        foreach ($runs as $i => $run) {
            $text = ltrim($this->cellText($xpath, $run));
            if ($village === null && str_starts_with($text, 'Village')) {
                $village = $i;
            } elseif ($village !== null && str_starts_with($text, 'District')) {
                $district = $i;
                break;
            }
        }
        if ($village === null || $district === null) {
            return;
        }

        $this->setRunText($runs[$village], $this->oneLine((string) $d['location']));
        foreach ($runs as $i => $run) {
            if ($i > $village && $i < $district) {
                $paragraph->removeChild($run);
            } elseif ($i === $district) {
                $this->setRunText($run, '');
            } elseif ($i > $district) {
                $paragraph->removeChild($run);
            }
        }
    }

    /**
     * Table row label => DPR field, for rows whose value cell is an empty
     * placeholder in the template.
     */
    private const SIMPLE_LABEL_FIELDS = [
        'Pan no.' => 'pan',
        'ADHARCARD NO.' => 'aadhaar',
        'GENDER' => 'gender',
        'MOBILE NO.' => 'mobile',
        'EMAIL ID.' => 'email',
        'EDUCATION' => 'education',
        'MOTHER NAME' => 'mother_name',
        'DATE OF BIRTH' => 'date_of_birth',
        'EXPIRIANCE' => 'experience',
        'HISTRY /PROFILE OF PROPRIETOR' => 'proprietor_history',
        'UDYAM NO.' => 'udyam',
        'GST NO.' => 'gst',
        'FSSAI LICENSE' => 'fssai',
        'NUMBER OF MANPOWER' => 'manpower',
        'CONNECTIVITY' => 'connectivity',
        'BUSINESS PROFILE' => 'business_profile',
    ];

    /**
     * @param array<string, mixed> $d
     */
    private function fillSimpleLabelRows(DOMDocument $dom, DOMXPath $xpath, array $d): void
    {
        foreach ($xpath->query('//w:tr') as $row) {
            $cells = $xpath->query('./w:tc', $row);
            if ($cells->length < 2) {
                continue;
            }

            $field = null;
            foreach ($cells as $cell) {
                $label = trim($this->cellText($xpath, $cell));
                if ($label !== '' && array_key_exists($label, self::SIMPLE_LABEL_FIELDS)) {
                    $field = self::SIMPLE_LABEL_FIELDS[$label];
                    break;
                }
            }

            $valueCell = $cells->item($cells->length - 1);
            if (! $field || trim($this->cellText($xpath, $valueCell)) !== '') {
                continue; // no mapped label, or the cell already has wording of its own
            }

            $value = $field === 'date_of_birth' && $d[$field] ? Carbon::parse($d[$field])->format('d/m/Y') : (string) $d[$field];
            $this->putCellText($dom, $xpath, $valueCell, $this->oneLine($value), $this->firstRunProps($xpath, $valueCell));
        }
    }

    /**
     * The business-profile rows whose value cells carry demo text:
     * activity, land & factory building, ITR filing, and present banking.
     *
     * @param array<string, mixed> $d
     */
    private function fillBusinessProfileRows(DOMDocument $dom, DOMXPath $xpath, array $d): void
    {
        $table = $this->findTableContaining($xpath, 'Nature of the Business Activity');
        if (! $table) {
            return;
        }

        $rowValues = [
            'Nature of the Business Activity' => $d['activity'],
            'Land and Factory Building' => $d['land_and_factory'],
            'ITR Filling' => $d['itr_filing'],
        ];

        foreach ($xpath->query('./w:tr', $table) as $row) {
            $cells = $xpath->query('./w:tc', $row);
            if ($cells->length < 2) {
                continue;
            }
            $rowText = $this->concatCellsText($xpath, $cells);
            $valueCell = $cells->item($cells->length - 1);

            foreach ($rowValues as $label => $value) {
                if (str_contains($rowText, $label)) {
                    $this->putCellText($dom, $xpath, $valueCell, $this->oneLine((string) $value), $this->firstRunProps($xpath, $valueCell));
                }
            }

            if (str_contains($rowText, 'Present Banking')) {
                $this->fillPresentBanking($dom, $xpath, $valueCell, $d);
            }
        }
    }

    /**
     * "<bank> – <account type> / A/C NO. – <number> / IFSC COAD : <code>": three
     * paragraphs in the demo; the static lead-ins stay, the values come from the form.
     *
     * @param array<string, mixed> $d
     */
    private function fillPresentBanking(DOMDocument $dom, DOMXPath $xpath, DOMElement $cell, array $d): void
    {
        $paragraphs = iterator_to_array($xpath->query('./w:p', $cell));
        $lines = [
            $this->joinNonEmpty(' – ', [$d['present_bank'], $d['account_type']]),
            'A/C NO. –' . (filled($d['account_number']) ? ' ' . $d['account_number'] : ''),
            'IFSC COAD :' . (filled($d['ifsc']) ? ' ' . $d['ifsc'] : ''),
        ];

        foreach ($paragraphs as $i => $paragraph) {
            if (isset($lines[$i])) {
                $this->setParagraphText($dom, $xpath, $paragraph, $lines[$i]);
            }
        }
    }

    /**
     * Current loans: one row per loan (the template has three blank rows;
     * more are cloned in before the TOTAL row). The TOTAL row's dashes are
     * the template's own wording and stay.
     *
     * @param array<string, mixed> $d
     */
    private function fillCurrentLoans(DOMDocument $dom, DOMXPath $xpath, array $d): void
    {
        $loans = $d['current_loans'];
        if (! $loans) {
            return;
        }

        $table = $this->findTableContaining($xpath, 'OUTSTANDING AMOUNT');
        if (! $table) {
            return;
        }

        $blankRows = [];
        $totalRow = null;
        foreach ($xpath->query('./w:tr', $table) as $row) {
            $text = trim($this->concatCellsText($xpath, $xpath->query('./w:tc', $row)));
            if (str_contains($text, 'BANK NAME')) {
                continue; // header
            }
            if (str_starts_with($text, 'TOTAL')) {
                $totalRow = $row;
                break;
            }
            $blankRows[] = $row;
        }

        if (! $totalRow || ! $blankRows) {
            return;
        }

        $templateRow = $blankRows[0];
        $props = $this->columnRunProps($xpath, $templateRow);

        foreach ($loans as $i => $loan) {
            $row = $i < count($blankRows) ? $blankRows[$i] : $templateRow->cloneNode(true);
            if ($i >= count($blankRows)) {
                $totalRow->parentNode->insertBefore($row, $totalRow);
            }

            $values = [(string) ($i + 1), $loan['bank_name'], $loan['loan_type'], $this->money($loan['loan_amount']), $this->money($loan['emi']), $this->money($loan['outstanding_amount'])];
            foreach ($xpath->query('./w:tc', $row) as $c => $cell) {
                $this->putCellText($dom, $xpath, $cell, $values[$c] ?? null, $props[$c] ?? null);
            }
        }
    }

    /**
     * Required loan: term loan and cash credit rows; the total is the amount plus the contribution.
     *
     * @param array<string, mixed> $d
     */
    private function fillRequiredLoan(DOMDocument $dom, DOMXPath $xpath, array $d): void
    {
        $table = $this->findTableContaining($xpath, 'REQUIRED LOAN AMOUNT');
        if (! $table) {
            return;
        }

        $rows = [
            'TERM LOAN' => [$d['term_required'], $d['term_contribution']],
            'CASH CREDIT' => [$d['cc_required'], $d['cc_contribution']],
        ];

        foreach ($xpath->query('./w:tr', $table) as $row) {
            $cells = $xpath->query('./w:tc', $row);
            $label = trim($this->cellText($xpath, $cells->item(0)));
            if (! isset($rows[$label]) || $cells->length < 4) {
                continue;
            }

            [$required, $contribution] = $rows[$label];
            $total = $this->sum([$required, $contribution]);
            foreach ([1 => $this->money($required), 2 => $this->money($contribution), 3 => $total === null ? '' : $this->money((string) $total)] as $c => $text) {
                $this->putCellText($dom, $xpath, $cells->item($c), $text, $this->firstRunProps($xpath, $cells->item($c)));
            }
        }
    }

    /**
     * Project cost and means of finance: entered amounts in, totals calculated.
     *
     * @param array<string, mixed> $d
     */
    private function fillProjectCostAndFinance(DOMDocument $dom, DOMXPath $xpath, array $d): void
    {
        $costParts = [$d['cost_land'], $d['cost_building'], $d['cost_machinery'], $d['cost_working_capital']];
        $financeParts = [$d['fin_promoter_capital'], $d['fin_term_loan'], $d['fin_cash_credit']];

        $this->fillLabelledAmounts($dom, $xpath, 'LAND VALUE', [
            'LAND VALUE' => $d['cost_land'],
            'BUIDING & CONSTRUCTION' => $d['cost_building'],
            'PLANT & MACHINARY' => $d['cost_machinery'],
            'WORKING CAPITAL' => $d['cost_working_capital'],
            'TOTAL PROJECT COST' => $this->sum($costParts),
        ]);

        $this->fillLabelledAmounts($dom, $xpath, 'PRAMOTER CAPITAL', [
            'PRAMOTER CAPITAL' => $d['fin_promoter_capital'],
            'TERM LOAN' => $d['fin_term_loan'],
            'CASH CREDIT' => $d['fin_cash_credit'],
            'TOTAL' => $this->sum($financeParts),
        ]);
    }

    /**
     * @param array<string, string|float|null> $labelValues
     */
    private function fillLabelledAmounts(DOMDocument $dom, DOMXPath $xpath, string $tableMarker, array $labelValues): void
    {
        $table = $this->findTableContaining($xpath, $tableMarker);
        if (! $table) {
            return;
        }

        foreach ($xpath->query('./w:tr', $table) as $row) {
            $cells = $xpath->query('./w:tc', $row);
            if ($cells->length < 2) {
                continue;
            }
            $label = trim($this->cellText($xpath, $cells->item(0)));
            if (! array_key_exists($label, $labelValues)) {
                continue;
            }

            $value = $labelValues[$label];
            $valueCell = $cells->item($cells->length - 1);
            $this->putCellText($dom, $xpath, $valueCell, $value === null ? '' : $this->money((string) $value), $this->firstRunProps($xpath, $valueCell));
        }
    }

    /**
     * Quotation summary: one row per machine (the template's demo row is
     * cloned for more), total and "Term Loan Required" rows calculated.
     *
     * @param array<string, mixed> $d
     */
    private function fillQuotation(DOMDocument $dom, DOMXPath $xpath, array $d): void
    {
        $table = $this->findTableContaining($xpath, 'Term Loan Required');
        if (! $table) {
            return;
        }

        $rows = iterator_to_array($xpath->query('./w:tr', $table));
        if (count($rows) < 4) {
            return;
        }

        [, $demoRow, $totalRow, $termRow] = $rows;
        $props = $this->columnRunProps($xpath, $demoRow);
        $machines = $d['machinery'];

        $dataRows = [$demoRow];
        for ($i = 1; $i < count($machines); $i++) {
            $clone = $demoRow->cloneNode(true);
            $totalRow->parentNode->insertBefore($clone, $totalRow);
            $dataRows[] = $clone;
        }

        foreach ($dataRows as $i => $row) {
            $m = $machines[$i] ?? [];
            $values = [isset($machines[$i]) ? ($i + 1) . '.' : '', $m['particulars'] ?? null, $m['qty'] ?? null, $this->money($m['amount'] ?? null)];
            foreach ($xpath->query('./w:tc', $row) as $c => $cell) {
                $this->putCellText($dom, $xpath, $cell, $values[$c] ?? null, $props[$c] ?? null);
            }
        }

        $total = $this->sum(array_column($machines, 'amount'));
        $this->putCellText($dom, $xpath, $xpath->query('./w:tc', $totalRow)->item(3), $total === null ? '' : $this->money((string) $total), $this->firstRunProps($xpath, $xpath->query('./w:tc', $totalRow)->item(3)));
        $this->putCellText($dom, $xpath, $xpath->query('./w:tc', $termRow)->item(3), $this->money($d['term_required']), $this->firstRunProps($xpath, $xpath->query('./w:tc', $termRow)->item(3)));
    }

    /**
     * Manufacturing process: the demo groundnut chart (steps, arrows, output)
     * is always cleared; the entered process replaces it. The "CHART :" and
     * "PROCESS :" headings stay.
     *
     * @param array<string, mixed> $d
     */
    private function fillManufacturingProcess(DOMDocument $dom, DOMXPath $xpath, array $d): void
    {
        $processParagraph = null;
        foreach ($xpath->query('//w:p') as $p) {
            if (! str_starts_with(trim($this->cellText($xpath, $p)), 'PROCESS')) {
                continue;
            }
            $processParagraph = $p;

            // The demo's arrow shapes belong to its five-step chart.
            foreach (iterator_to_array($xpath->query('./w:r[.//w:drawing or .//w:pict or mc:AlternateContent]', $p)) as $run) {
                $p->removeChild($run);
            }

            $runs = iterator_to_array($xpath->query('./w:r', $p));
            $labelRun = null;
            foreach ($runs as $run) {
                if (str_contains($this->cellText($xpath, $run), 'PROCESS')) {
                    $labelRun = $run;
                    break;
                }
            }
            if (! $labelRun) {
                return;
            }

            // Keep "PROCESS :", drop the demo steps after it.
            $label = $this->cellText($xpath, $labelRun);
            $this->setRunText($labelRun, preg_match('/^(.*?PROCESS\s*:)/s', $label, $m) ? $m[1] : $label);
            foreach ($runs as $run) {
                if ($run !== $labelRun && $this->follows($run, $labelRun)) {
                    $p->removeChild($run);
                }
            }
            if (filled($d['process_steps'])) {
                $p->appendChild($this->cloneRunWithText($dom, $labelRun, ' ' . $this->oneLine((string) $d['process_steps'])));
            }
            break;
        }

        $this->replacePhraseAcrossRuns($dom, $xpath, 'PEANUT OIL + PEANUT CAKE', $this->oneLine((string) $d['process_output']));

        if (filled($d['process_description']) && $processParagraph instanceof DOMElement) {
            $output = $processParagraph->nextSibling;
            if ($output instanceof DOMElement && $output->localName === 'p') {
                $clone = $output->cloneNode(true);
                $this->setParagraphText($dom, $xpath, $clone, $this->oneLine((string) $d['process_description']));
                $output->parentNode->insertBefore($clone, $output->nextSibling);
            }
        }
    }

    /** Whether $run comes after $anchor within their shared parent. */
    private function follows(DOMElement $run, DOMElement $anchor): bool
    {
        for ($n = $anchor->nextSibling; $n; $n = $n->nextSibling) {
            if ($n === $run) {
                return true;
            }
        }

        return false;
    }

    /**
     * Collateral property / CGTMSE: one row per property, demo rows cleared.
     *
     * @param array<string, mixed> $d
     */
    private function fillCollateral(DOMDocument $dom, DOMXPath $xpath, array $d): void
    {
        $table = $this->findTableContaining($xpath, 'TYPE OF PROPERTY');
        if (! $table) {
            return;
        }

        $rows = iterator_to_array($xpath->query('./w:tr', $table));
        if (count($rows) < 2) {
            return;
        }

        $props = $this->columnRunProps($xpath, $rows[1]);
        $dataRows = array_slice($rows, 1);
        $last = end($dataRows);
        $items = $d['collateral'];

        while (count($dataRows) < count($items)) {
            $clone = $last->cloneNode(true);
            $last->parentNode->insertBefore($clone, $last->nextSibling);
            $last = $clone;
            $dataRows[] = $clone;
        }

        foreach ($dataRows as $i => $row) {
            $c = $items[$i] ?? [];
            $values = [isset($items[$i]) ? (string) ($i + 1) : '', $c['type'] ?? null, $c['address'] ?? null, $this->money($c['value'] ?? null)];
            foreach ($xpath->query('./w:tc', $row) as $idx => $cell) {
                $this->putCellText($dom, $xpath, $cell, $values[$idx] ?? null, $props[$idx] ?? null);
            }
        }
    }

    /**
     * The market-potential paragraph is the template's own prose; it is
     * replaced only when a market analysis was entered.
     *
     * @param array<string, mixed> $d
     */
    private function fillMarketPotential(DOMDocument $dom, DOMXPath $xpath, array $d): void
    {
        if (! filled($d['market_potential'])) {
            return;
        }

        $paragraph = $this->findParagraphContaining($xpath, 'global groundnut oil market');
        if ($paragraph) {
            $this->setParagraphText($dom, $xpath, $paragraph, $this->oneLine((string) $d['market_potential']));
        }
    }

    /**
     * Financial details in brief: seven rows by three year columns.
     *
     * @param array<string, mixed> $d
     */
    private function fillFinancialTable(DOMDocument $dom, DOMXPath $xpath, array $d): void
    {
        $table = $this->findTableContaining($xpath, 'NTREST AND DEPRICIATION');
        if (! $table) {
            return;
        }

        $rowKeys = ['SALES' => 'sales', 'PURCHASE' => 'purchase', 'DEPRICIATION' => 'depreciation', 'NET PROFIT' => 'net_profit',
            'CAPITAL' => 'capital', 'INVESTMENT' => 'investment', 'FIXED ASSETS' => 'fixed_assets'];
        $periods = array_keys(DprData::FINANCIAL_PERIODS);

        foreach ($xpath->query('./w:tr', $table) as $row) {
            $cells = $xpath->query('./w:tc', $row);
            $label = strtoupper(trim($this->cellText($xpath, $cells->item(0))));
            $key = null;
            foreach ($rowKeys as $prefix => $k) {
                if (str_starts_with($label, $prefix)) {
                    $key = $k;
                    break;
                }
            }
            if (! $key) {
                continue;
            }

            foreach ($periods as $i => $period) {
                $cell = $cells->item($i + 1);
                $this->putCellText($dom, $xpath, $cell, $d['financials'][$key][$period], $this->firstRunProps($xpath, $cell));
            }
        }
    }

    /**
     * The "Photos of new machinery" / "Photos of final products" sections: the lead's
     * uploads of the matching Lead Documents types go in under their headings, two to a
     * row. A section with no uploads is left as the template has it.
     */
    private function embedPhotos(DOMDocument $dom, DOMXPath $xpath, ZipArchive $zip, Lead $lead): void
    {
        $headings = ['machinery' => 'PHOTOS OF NEW MACHINARY', 'final_product' => 'PHOTOS OF FINAL PRODUCTS'];
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        $rels = $zip->getFromName('word/_rels/document.xml.rels');
        $types = $zip->getFromName('[Content_Types].xml');
        if ($rels === false || $types === false) {
            return;
        }

        $nextId = 1000;
        $count = 0;
        $extensions = [];

        foreach ($headings as $section => $heading) {
            $anchor = $this->findParagraphContaining($xpath, $heading);
            if (! $anchor) {
                continue;
            }

            $images = [];
            $documents = \App\Models\LeadDocument::where('lead_id', $lead->id)
                ->whereHas('documentType', fn ($q) => $q->where('code', DprData::PHOTO_DOCUMENTS[$section]['code']))
                ->orderBy('id')->get();
            foreach ($documents as $document) {
                $bytes = $disk->exists($document->file_path) ? $disk->get($document->file_path) : null;
                $info = $bytes === null ? false : @getimagesizefromstring($bytes);
                $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif'][$info[2] ?? 0] ?? null;
                if ($ext === null || $info[0] < 1 || $info[1] < 1) {
                    continue; // not an image Word can place (e.g. a PDF)
                }

                $count++;
                $part = 'media/dpr_photo_' . $count . '.' . $ext;
                $zip->addFromString('word/' . $part, $bytes);
                $rels = str_replace('</Relationships>', '<Relationship Id="rIdDprPhoto' . $count . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="' . $part . '"/></Relationships>', $rels);
                $extensions[$ext] = true;

                // Fit within 3.2" x 2.6" (9525 EMU per pixel), never enlarging.
                $scale = min(2926080 / ($info[0] * 9525), 2377440 / ($info[1] * 9525), 1);
                $images[] = ['rid' => 'rIdDprPhoto' . $count, 'cx' => (int) round($info[0] * 9525 * $scale), 'cy' => (int) round($info[1] * 9525 * $scale), 'id' => $nextId++, 'name' => 'DPR photo ' . $count];
            }

            $after = $anchor;
            foreach (array_chunk($images, 2) as $pair) {
                $xml = '<w:p xmlns:w="' . self::NS . '" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><w:pPr><w:jc w:val="center"/></w:pPr>';
                foreach ($pair as $i => $img) {
                    $xml .= ($i ? '<w:r><w:t xml:space="preserve">  </w:t></w:r>' : '')
                        . '<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="' . $img['cx'] . '" cy="' . $img['cy'] . '"/><wp:docPr id="' . $img['id'] . '" name="' . $img['name'] . '"/>'
                        . '<wp:cNvGraphicFramePr><a:graphicFrameLocks noChangeAspect="1"/></wp:cNvGraphicFramePr><a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
                        . '<pic:pic><pic:nvPicPr><pic:cNvPr id="0" name="' . $img['name'] . '"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="' . $img['rid'] . '"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
                        . '<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $img['cx'] . '" cy="' . $img['cy'] . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic>'
                        . '</a:graphicData></a:graphic></wp:inline></w:drawing></w:r>';
                }
                $fragment = $dom->createDocumentFragment();
                $fragment->appendXML($xml . '</w:p>');
                $inserted = $fragment->firstChild;
                $after->parentNode->insertBefore($fragment, $after->nextSibling);
                $after = $inserted;
            }
        }

        if ($count === 0) {
            return;
        }

        foreach (array_keys($extensions) as $ext) {
            if (! str_contains($types, 'Extension="' . $ext . '"')) {
                $types = str_replace('<Override ', '<Default Extension="' . $ext . '" ContentType="image/' . ($ext === 'jpg' ? 'jpeg' : $ext) . '"/><Override ', $types);
            }
        }
        $zip->addFromString('word/_rels/document.xml.rels', $rels);
        $zip->addFromString('[Content_Types].xml', $types);
    }

    // ---- helpers --------------------------------------------------------

    /**
     * Replaces a paragraph's runs with one run carrying the paragraph's first
     * run formatting.
     */
    private function setParagraphText(DOMDocument $dom, DOMXPath $xpath, DOMElement $paragraph, string $text, bool $keepFormatting = true): void
    {
        $first = $xpath->query('./w:r', $paragraph)->item(0);
        $template = $first instanceof DOMElement ? $first->cloneNode(true) : null;

        foreach (iterator_to_array($xpath->query('./w:r', $paragraph)) as $run) {
            $paragraph->removeChild($run);
        }

        if ($text !== '') {
            $paragraph->appendChild($template ? $this->cloneRunWithText($dom, $template, $text) : $this->cloneRunWithText($dom, $dom->createElementNS($this->wordNs(), 'w:r'), $text));
        }
    }

    /**
     * "₹" with Indian digit grouping for a number; text that isn't a number is shown as typed; '' for nothing.
     */
    private function money(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        $n = DprData::amount($value);

        return $n === null ? trim($value) : '₹' . $this->indian($n);
    }

    private function indian(float $n): string
    {
        $n = round($n, 2);
        $whole = (string) (int) floor($n);
        $fraction = $n - floor($n) > 0 ? ltrim(number_format($n - floor($n), 2), '0') : '';

        if (strlen($whole) > 3) {
            $head = substr($whole, 0, -3);
            $whole = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $head) . ',' . substr($whole, -3);
        }

        return $whole . $fraction;
    }

    /**
     * Sum of whichever parts are numbers; null when none is.
     *
     * @param array<int, ?string> $parts
     */
    private function sum(array $parts): ?float
    {
        $numbers = array_filter(array_map(fn ($p) => DprData::amount($p), $parts), fn ($n) => $n !== null);

        return $numbers ? array_sum($numbers) : null;
    }
}
