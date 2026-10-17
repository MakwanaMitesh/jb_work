<?php

namespace App\Services;

use App\Models\Lead;
use App\Support\OpinionReportData;
use App\Services\Concerns\FillsDocxTables;
use App\Services\Concerns\ManipulatesDocxRuns;
use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

/**
 * Generates a Lead-specific Opinion Report / net-worth affidavit (OR) from
 * the master template by copying the .docx file as-is and rewriting only
 * the Lead-specific text inside word/document.xml. Every other zip entry
 * (styles, theme, fonts, settings, numbering, etc.) is left byte-for-byte
 * untouched, and the master template itself is never opened for writing.
 *
 * Policy for this document (stricter than the DPR/CMA generators): every
 * field the template marks as dynamic (yellow-highlighted) is either filled
 * from real Lead data or blanked outright — the template's demo person's
 * name/address/age/etc. must never survive into the generated document,
 * even when no Lead data is available for that field. Static wording that
 * ISN'T a per-lead value (section headings, legal boilerplate, the dotted
 * fill-in-the-blank lines for items the Lead model has no field for at all)
 * is left untouched. All yellow highlighting is stripped from the final
 * output regardless of whether a field was filled or blanked.
 *
 * Several demo values repeat verbatim in more than one place (the
 * deponent's name appears both as the applicant and — with different
 * surrounding text — as the borrower; a stock address string is reused for
 * the residential, office and property-table rows with just a "NEAR "/
 * trailing-period difference); those are disambiguated by widening the
 * needle to include enough surrounding text to be unique, and are listed
 * most-specific-first so a longer needle is consumed before a shorter one
 * that would otherwise match inside it.
 */
class OrGenerator
{
    use FillsDocxTables;
    use ManipulatesDocxRuns;

    private const TEMPLATE_PATH = 'upload/OR/demo_or.docx';
    private const NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    public function generate(Lead $lead): string
    {
        $templatePath = public_path(self::TEMPLATE_PATH);

        if (! is_file($templatePath)) {
            throw new RuntimeException('OR master template not found at ' . self::TEMPLATE_PATH);
        }

        $relativeDir = 'or/' . $lead->id;
        $relativePath = $relativeDir . '/OR.docx';
        $destPath = storage_path('app/public/' . $relativePath);

        if (! is_dir(dirname($destPath))) {
            mkdir(dirname($destPath), 0755, true);
        }

        // Copy the template first — every mutation below happens on the
        // copy only, so the original file is never opened for writing.
        if (! copy($templatePath, $destPath)) {
            throw new RuntimeException('Failed to copy OR template.');
        }

        $zip = new ZipArchive();
        if ($zip->open($destPath) !== true) {
            throw new RuntimeException('Failed to open generated OR document.');
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            throw new RuntimeException('OR template is missing word/document.xml.');
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', self::NS);

        $data = OpinionReportData::for($lead);

        foreach ($this->substringReplacements($data) as $needle => $replacement) {
            $this->replacePhraseAcrossRuns($dom, $xpath, $needle, $replacement);
        }

        $this->fillPropertyTable($dom, $xpath, $data);
        $this->fillOutsideIndia($xpath, $data);
        $this->fillLiquidAssets($dom, $xpath, $data);
        $this->fillNarrativeFields($dom, $xpath, $data);
        $this->fillFamilyMembers($dom, $xpath, $data);
        $this->stripAllHighlighting($xpath);

        $zip->addFromString('word/document.xml', $dom->saveXML());
        $zip->close();

        return $relativePath;
    }

    /**
     * Ordered most-specific-needle-first so a longer template phrase is
     * consumed before a shorter one that is a substring of it. Every entry
     * always fires (no filtering of empty values) — an empty replacement
     * blanks the demo value while leaving its static wrapper text in place.
     * The two dates fall back to today when none was entered (the affidavit
     * is generated "as of now"), as they always have.
     *
     * @param array<string, mixed> $d normalized Opinion Report data
     * @return array<string, string>
     */
    private function substringReplacements(array $d): array
    {
        $age = OpinionReportData::ageFrom($d['date_of_birth']);
        $dob = $d['date_of_birth'] ? Carbon::parse($d['date_of_birth'])->format('d/m/Y') : '';
        $today = Carbon::now();
        $assetsDate = $d['assets_date'] ? Carbon::parse($d['assets_date']) : $today;
        $deponentDate = $d['deponent_date'] ? Carbon::parse($d['deponent_date']) : $today;
        $borrower = (string) $d['borrower_name'];

        return [
            // Deponent (applicant) identity — must run before the bare-name
            // borrower needle below, since "I, SALIM..." contains it.
            'I, SALIM FIROZBHAI LAGADIYA' => 'I, ' . $d['deponent_name'],
            // Borrower identity — the whole highlighted span already
            // includes the "(Name of the borrower)" annotation.
            'SALIM FIROZBHAI LAGADIYA (Name of the borrower)' => trim($borrower . ' (Name of the borrower)'),

            // Father/Spouse name — the template bundles this run with a
            // trailing " aged" that must stay; only the name is replaced.
            'FIROZ NAZARALI LAGADIYA' => (string) $d['father_name'],
            // The static "Son/Daughter/Wife" listing becomes the actual relationship.
            'Son/Daughter/Wife' => (string) $d['relationship'],

            // Age is calculated from the date of birth, never typed or stored.
            'aged  41 (Date of Birth' => 'aged  ' . ($age !== null ? (string) $age : '') . ' (Date of Birth',
            '30/03/1985' => $dob,
            'Place of Birth : RAJULA' => 'Place of Birth : ' . $d['place_of_birth'],

            'BUISNESS' => (string) $d['occupation'],

            // Residential address — the template's highlight includes the
            // "NEAR " landmark prefix and trailing period as part of the
            // value, so the whole span is replaced wholesale.
            'NEAR TATVJYOTI, VAVERA ROAD, RAJULA, AMRELI.' => (string) $d['residential_address'],
            // Office address (with trailing period). The property-table
            // variant has no trailing period and is rewritten separately.
            'TATVJYOTI, VAVERA ROAD, RAJULA, AMRELI.' => (string) $d['office_address'],

            // Bank branch — appears twice (grant clause + notify clause);
            // both get the same value.
            'DHARESHVAR BRANCH' => (string) $d['branch'],

            // "as on <date of swearing>" (dash format) and the deponent's date (slash format).
            '31-03-2026' => $assetsDate->format('d-m-Y'),
            '01/01/2000' => $deponentDate->format('d/m/Y'),
        ];
    }

    // ---- tables ---------------------------------------------------------

    /**
     * Immovable property in India: one table row per entered property
     * (the template's three data rows, plus cloned rows beyond that). The
     * demo property is always blanked first.
     *
     * @param array<string, mixed> $d
     */
    private function fillPropertyTable(DOMDocument $dom, DOMXPath $xpath, array $d): void
    {
        $table = $this->findTableContaining($xpath, 'Ancestral or');
        if (! $table) {
            return;
        }

        $rows = iterator_to_array($xpath->query('./w:tr', $table));
        if (count($rows) < 2) {
            return;
        }

        $props = $this->columnRunProps($xpath, $rows[1]);
        $properties = $d['properties'];

        // Template has data rows 1..n; add rows (copied from the last one) when needed.
        $dataRows = array_slice($rows, 1);
        $lastRow = end($dataRows);
        while (count($dataRows) < count($properties)) {
            $clone = $lastRow->cloneNode(true);
            $lastRow->parentNode->insertBefore($clone, $lastRow->nextSibling);
            $lastRow = $clone;
            $dataRows[] = $clone;
        }

        foreach ($dataRows as $i => $row) {
            $p = $properties[$i] ?? [];
            $values = [
                $p['place'] ?? null,
                $this->joinNonEmpty(' / ', [$p['ownership_origin'] ?? null, $p['ownership_type'] ?? null]),
                $p['encumbered'] ?? null,
                $p['extent_acres'] ?? null,
                $p['market_value'] ?? null,
                $p['case_suit'] ?? null,
                trim(($p['land_type'] ?? '') . (isset($p['ceiling']) ? ' (' . $p['ceiling'] . ')' : '')),
            ];

            foreach ($xpath->query('./w:tc', $row) as $c => $cell) {
                $this->putCellText($dom, $xpath, $cell, $values[$c] ?? null, $props[$c] ?? $props[0] ?? null);
            }
        }
    }

    /**
     * "b. … LOCATED OUTSIDE INDIA AND OWNED BY ME. None": the trailing "None"
     * is the template's placeholder for this field, so it becomes what was
     * entered, or nothing.
     *
     * @param array<string, mixed> $d
     */
    private function fillOutsideIndia(DOMXPath $xpath, array $d): void
    {
        $p = $this->findParagraphContaining($xpath, 'LOCATED OUTSIDE INDIA');
        if (! $p) {
            return;
        }

        $runs = $xpath->query('./w:r', $p);
        $last = $runs->item($runs->length - 1);
        if ($last instanceof DOMElement && trim($this->cellText($xpath, $last)) === 'None') {
            $this->setRunText($last, filled($d['outside_india']) ? ' ' . $this->oneLine($d['outside_india']) : '');
        }
    }

    /**
     * Liquid assets: the Amount cells hold the demo "000" and are blanked;
     * Description keeps the template's own wording (Cash / Equity / Stock)
     * unless one was entered; "If encumbered" starts empty.
     *
     * @param array<string, mixed> $d
     */
    private function fillLiquidAssets(DOMDocument $dom, DOMXPath $xpath, array $d): void
    {
        $table = $this->findTableContaining($xpath, 'If encumbered to what extent');
        if (! $table) {
            return;
        }

        $rows = iterator_to_array($xpath->query('./w:tr', $table));
        $keys = array_keys(OpinionReportData::LIQUID_ASSETS);

        foreach ($keys as $i => $key) {
            $row = $rows[$i + 1] ?? null;
            if (! $row) {
                continue;
            }
            $cells = $xpath->query('./w:tc', $row);
            $amountProps = $this->firstRunProps($xpath, $cells->item(2));
            $given = $d['liquid_assets'][$key];

            if (filled($given['description'])) {
                $this->putCellText($dom, $xpath, $cells->item(1), $given['description'], $this->firstRunProps($xpath, $cells->item(1)));
            }
            $this->putCellText($dom, $xpath, $cells->item(2), $given['amount'], $amountProps);
            $this->putCellText($dom, $xpath, $cells->item(3), $given['encumbered'], $amountProps);
        }
    }

    /**
     * Family members / legal heirs: one row per member (at least the
     * template's two rows, so the layout and SL numbers stay), age
     * calculated from a date of birth when one was given.
     *
     * @param array<string, mixed> $d
     */
    private function fillFamilyMembers(DOMDocument $dom, DOMXPath $xpath, array $d): void
    {
        $table = $this->findTableContaining($xpath, 'RELATION');
        if (! $table) {
            return;
        }

        $rows = iterator_to_array($xpath->query('./w:tr', $table));
        if (count($rows) < 2) {
            return;
        }

        $props = $this->columnRunProps($xpath, $rows[1]);
        $dataRows = array_slice($rows, 1);
        $lastRow = end($dataRows);
        $members = $d['family_members'];

        while (count($dataRows) < count($members)) {
            $clone = $lastRow->cloneNode(true);
            $lastRow->parentNode->insertBefore($clone, $lastRow->nextSibling);
            $lastRow = $clone;
            $dataRows[] = $clone;
        }

        foreach ($dataRows as $i => $row) {
            $m = $members[$i] ?? [];
            $age = OpinionReportData::ageFrom($m['date_of_birth'] ?? null);
            $values = [
                (string) ($i + 1),
                $m['name'] ?? null,
                $age !== null ? (string) $age : ($m['age'] ?? null),
                $m['occupation'] ?? null,
                $m['relation'] ?? null,
                $m['address'] ?? null,
            ];

            foreach ($xpath->query('./w:tc', $row) as $c => $cell) {
                $this->putCellText($dom, $xpath, $cell, $values[$c] ?? null, $props[$c] ?? $props[1] ?? null);
            }
        }
    }

    // ---- paragraphs -----------------------------------------------------

    /**
     * The dotted fill-in lines (assets d–j, l; liabilities; "every year
     * by") are yellow placeholders: replaced with what was entered, and
     * left as the printed blank line when nothing was.
     *
     * @param array<string, mixed> $d
     */
    private function fillNarrativeFields(DOMDocument $dom, DOMXPath $xpath, array $d): void
    {
        $life = $this->joinNonEmpty('; ', [
            $this->oneLine($d['life_insurance']),
            filled($d['life_policy_numbers']) ? 'Policy No(s): ' . $d['life_policy_numbers'] : null,
            filled($d['life_surrender_value']) ? 'Surrender value: ' . $d['life_surrender_value'] : null,
        ]);

        $placeholders = [
            'GOVERNMENT SECURITIES' => $d['gov_securities'],
            'e. SHARES' => $d['shares'],
            'LIFE INSURANCE POLICIES' => $life,
            'g. JEWELLERY' => $d['jewellery'],
            'INVESTMENT IN BUSINESS IN ASSOCIATE FIRM' => $d['associate_investment'],
            'INTANGIBLE ASSETS' => $d['intangible_assets'],
            'INCOME PER MONTH' => $d['income_per_month'],
            'BORROWINGS FROM BANKS' => $d['borrowings_banks'],
            'BORROWINGS FROM MARKET' => $d['borrowings_market'],
            'BORROWINGS FROM OTHER SOURCES' => $d['borrowings_other'],
            'ASSUMED INDIRECT LIABILITY' => $d['indirect_liability'],
            'ANY OTHER ASSET NOT COVERED ABOVE' => $d['other_asset'],
            'notify to the State Bank of India' => $d['notify_by'],
        ];

        foreach ($placeholders as $label => $value) {
            $paragraph = $this->findParagraphContaining($xpath, $label);
            if ($paragraph && filled($value)) {
                $this->replaceYellowRuns($dom, $xpath, $paragraph, $this->oneLine((string) $value));
            }
        }

        // "m. ANY OTHER LIABILITY NOT COVERED ABOVE." has no placeholder run; the answer follows the label.
        $paragraph = $this->findParagraphContaining($xpath, 'ANY OTHER LIABILITY NOT COVERED ABOVE');
        if ($paragraph && filled($d['other_liability'])) {
            $runs = $xpath->query('./w:r', $paragraph);
            $template = $runs->item($runs->length - 1);
            if ($template instanceof DOMElement) {
                $paragraph->insertBefore($this->cloneRunWithText($dom, $template, ' ' . $this->oneLine($d['other_liability'])), $template->nextSibling);
            }
        }
    }

    /**
     * First yellow placeholder run takes the value; any further placeholder
     * runs in the paragraph are removed.
     */
    private function replaceYellowRuns(DOMDocument $dom, DOMXPath $xpath, DOMElement $paragraph, string $value): void
    {
        // Only the placeholder run (dots / underscores), never a yellow value that shares the paragraph
        // (the notify clause also holds the already-filled branch).
        $yellow = array_values(array_filter(
            iterator_to_array($xpath->query('./w:r[w:rPr/w:highlight]', $paragraph)),
            fn (DOMElement $run) => (bool) preg_match('/^[\s.\x{2026}_]+$/u', $this->cellText($xpath, $run))
        ));
        if (! $yellow) {
            return;
        }

        $this->setRunText($yellow[0], $value);
        foreach (array_slice($yellow, 1) as $run) {
            $paragraph->removeChild($run);
        }
    }

    // ---- helpers --------------------------------------------------------

}
