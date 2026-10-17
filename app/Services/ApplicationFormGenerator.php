<?php

namespace App\Services;

use App\Models\ApplicationForm;
use App\Models\Lead;
use App\Services\Concerns\RecalculatesXlsxOnOpen;
use App\Services\Concerns\WritesXlsxCells;
use App\Support\ApplicationFormCellMap as Map;
use App\Support\ApplicationFormData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

/**
 * Builds a Lead's Application Form workbook from the master template.
 *
 * The master is only ever read: it is copied to a temp file, the copy's
 * worksheet XML is edited cell by cell (styles, merges, column widths,
 * page setup, images and every static string stay byte-for-byte as they
 * were), and the result is stored on the private disk. Red-font sample
 * values are first blanked wholesale and then overwritten only where the
 * Lead's Application Form has data, so an empty field is an empty cell.
 */
class ApplicationFormGenerator
{
    use RecalculatesXlsxOnOpen;
    use WritesXlsxCells;

    public const TEMPLATE_PATH = 'upload/Application_form/application.xlsx';
    public const DISK = 'local';

    public static function relativePath(Lead $lead): string
    {
        return 'application_form/' . $lead->id . '/Application_Form.xlsx';
    }

    /**
     * Generates (or regenerates) the workbook and records it on the form.
     * Returns the path on the private disk.
     */
    public function generate(Lead $lead): string
    {
        $data = ApplicationFormData::for($lead);
        $contents = $this->build($data);

        $relative = self::relativePath($lead);
        Storage::disk(self::DISK)->put($relative, $contents);

        ApplicationForm::updateOrCreate(
            ['lead_id' => $lead->id],
            ['generated_path' => $relative, 'generated_at' => now()]
        );

        return $relative;
    }

    /**
     * @param array<string, mixed> $data normalized Application Form data
     */
    public function build(array $data): string
    {
        $template = public_path(self::TEMPLATE_PATH);
        if (! is_file($template)) {
            throw new RuntimeException('Application Form master template not found at ' . self::TEMPLATE_PATH);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'appform');
        try {
            if (! copy($template, $tmp)) {
                throw new RuntimeException('Failed to copy the Application Form template.');
            }

            $zip = new ZipArchive();
            if ($zip->open($tmp) !== true) {
                throw new RuntimeException('Failed to open the generated Application Form workbook.');
            }

            $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
            $stylesXml = $zip->getFromName('xl/styles.xml');
            if ($sharedXml === false || $stylesXml === false) {
                $zip->close();
                throw new RuntimeException('Application Form template is missing expected parts.');
            }

            $sheetParts = $this->sheetParts($zip);
            $strings = $this->sharedStringTexts($sharedXml);
            $redStyles = $this->redStyleIds($stylesXml);

            $sheets = [];
            foreach ([Map::SHEET_1, Map::SHEET_2, Map::SHEET_3] as $name) {
                $xml = $zip->getFromName($sheetParts[$name]);
                $sheets[$name] = $this->blankRedSampleCells($xml, $redStyles, $strings);
            }

            $sheets[Map::SHEET_1] = $this->fillTable1($sheets[Map::SHEET_1], $data);
            $sheets[Map::SHEET_2] = $this->fillTable2($sheets[Map::SHEET_2], $data);
            $sheets[Map::SHEET_3] = $this->fillTable3($sheets[Map::SHEET_3], $data, $this->siList($sharedXml));

            foreach ($sheets as $name => $xml) {
                $zip->addFromString($sheetParts[$name], $xml);
            }

            $this->forceRecalculation($zip);
            $zip->close();

            return (string) file_get_contents($tmp);
        } finally {
            @unlink($tmp);
        }
    }

    // ---- Table 1 -------------------------------------------------------

    private function fillTable1(string $xml, array $d): string
    {
        foreach (Map::TABLE_1_STRAY_TICKS as $ref) {
            $xml = $this->blankCell($xml, $ref);
        }

        foreach (Map::TABLE_1_TEXT as $field => $cells) {
            foreach ($cells as $ref) {
                $xml = $this->putText($xml, $ref, $d[$field]);
            }
        }

        $hasMobile = filled($d['mobile_number']);
        $xml = $this->putText($xml, Map::MOBILE_COUNTRY_CODE_CELL, $hasMobile ? $d['mobile_country_code'] : null);

        $xml = $this->putNumber($xml, Map::DATE_OF_BIRTH_CELL, $d['date_of_birth'] ? $this->excelSerial($d['date_of_birth']) : null);

        $address = trim((string) $d['residential_address']);
        $mobile = trim((string) $d['applicant_mobile']);
        $xml = $this->putText($xml, Map::RESIDENTIAL_ADDRESS_CELL, trim($address . ($mobile !== '' ? ' Mob: ' . $mobile : '')));

        $xml = $this->putDigits($xml, Map::PINCODE_DIGIT_CELLS, preg_match('/^\d{6}$/', (string) $d['pincode']) ? $d['pincode'] : '');
        $xml = $this->putDigits($xml, Map::COMMENCEMENT_DIGIT_CELLS, $d['commencement_date'] ? Carbon::parse($d['commencement_date'])->format('dmY') : '');

        foreach (Map::TABLE_1_CHOICES as $field => $options) {
            foreach ($options as $option => [$ref, $label]) {
                $xml = $this->putChoiceLabel($xml, $ref, $label, $d[$field] === $option, $option === 'Others' ? $d['constitution_other'] : null);
            }
        }

        return $xml;
    }

    /**
     * One cell per option: the chosen option is bold-on-yellow with a tick,
     * the others are plain text in the template's ordinary label style.
     */
    private function putChoiceLabel(string $xml, string $ref, string $label, bool $selected, ?string $other): string
    {
        return $this->rewriteCell($xml, $ref, function (?string $style) use ($ref, $label, $selected, $other) {
            $base = ($style === null || $style === Map::SELECTED_STYLE) ? Map::UNSELECTED_STYLE : $style;
            $text = $selected ? $label . (filled($other) ? ': ' . trim($other) : '') . ' √' : $label;

            return $this->cellOpen($ref, $selected ? Map::SELECTED_STYLE : $base)
                . ' t="inlineStr"><is><t xml:space="preserve">' . $this->xmlEscape($text) . '</t></is></c>';
        });
    }

    /**
     * @param array<int, string> $cells
     */
    private function putDigits(string $xml, array $cells, string $digits): string
    {
        $chars = str_split($digits);
        foreach ($cells as $i => $ref) {
            $xml = $this->putText($xml, $ref, $chars[$i] ?? null);
        }

        return $xml;
    }

    // ---- Table 2 -------------------------------------------------------

    private function fillTable2(string $xml, array $d): string
    {
        foreach (Map::ASSOCIATE_ROWS as $i => $row) {
            foreach (Map::ASSOCIATE_COLUMNS as $key => $col) {
                $xml = $this->putText($xml, $col . $row, $d['associate_concerns'][$i][$key] ?? null);
            }
        }

        foreach (Map::EXISTING_ROWS as $facility => $row) {
            foreach (Map::EXISTING_COLUMNS as $key => $col) {
                $value = $d['existing_facilities'][$facility][$key];
                $xml = in_array($key, ['limit', 'outstanding'], true)
                    ? $this->putSmart($xml, $col . $row, $value)
                    : $this->putText($xml, $col . $row, $value);
            }
        }
        $xml = $this->putText($xml, Map::CUSTOMER_ID_CELL, $d['customer_id']);

        $total = 0.0;
        $anyAmount = false;
        foreach (Map::PROPOSED_ROWS as $facility => $row) {
            foreach (Map::PROPOSED_COLUMNS as $key => $col) {
                $value = $d['proposed_facilities'][$facility][$key];
                $xml = $key === 'amount' ? $this->putSmart($xml, $col . $row, $value) : $this->putText($xml, $col . $row, $value);
            }
            $amount = $d['proposed_facilities'][$facility]['amount'];
            if (is_numeric($amount)) {
                $anyAmount = true;
                $total += (float) $amount;
            }
        }
        $xml = $this->putNumber($xml, Map::PROPOSED_TOTAL_CELL, $anyAmount ? $this->plain($total) : null);

        foreach (Map::WORKING_CAPITAL_AMOUNTS as $field => $ref) {
            $xml = $this->putSmart($xml, $ref, $d[$field]);
        }
        $xml = $this->putText($xml, Map::WORKING_CYCLE_CELL, $d['wc_working_cycle_months']);
        foreach (Map::FY_LABEL_CELLS as $field => $ref) {
            if (filled($d[$field])) {
                $xml = $this->putText($xml, $ref, $d[$field]);
            }
        }

        $sums = array_fill_keys(Map::MACHINERY_TOTAL_COLUMNS, null);
        foreach (Map::MACHINERY_ROWS as $i => $row) {
            foreach (Map::MACHINERY_COLUMNS as $key => $col) {
                $value = $d['machinery'][$i][$key] ?? null;
                $isAmount = in_array($key, ['total_cost', 'promoter_contribution', 'loan_required'], true);
                $xml = $isAmount ? $this->putSmart($xml, $col . $row, $value) : $this->putText($xml, $col . $row, $value);
                if ($isAmount && is_numeric($value)) {
                    $sums[$col] = ($sums[$col] ?? 0) + (float) $value;
                }
            }
        }

        // Template totals are formulas; keep them when there is something to
        // add up, otherwise leave the cell empty rather than showing 0.
        [$first, $last] = [Map::MACHINERY_ROWS[0], Map::MACHINERY_ROWS[array_key_last(Map::MACHINERY_ROWS)]];
        foreach ($sums as $col => $sum) {
            $ref = $col . Map::MACHINERY_TOTAL_ROW;
            $xml = $sum === null
                ? $this->blankCell($xml, $ref)
                : $this->putFormula($xml, $ref, "SUM({$col}{$first}:{$col}{$last})", $this->plain($sum));
        }

        $repayment = collect([
            filled($d['repayment_period']) ? 'Repayment: ' . trim($d['repayment_period']) : null,
            filled($d['moratorium_period']) ? 'Moratorium: ' . trim($d['moratorium_period']) : null,
        ])->filter()->implode(', ');

        return $this->putText($xml, Map::REPAYMENT_CELL, $repayment);
    }

    // ---- Table 3 -------------------------------------------------------

    /**
     * @param array<int, string> $siList raw <si> bodies of sharedStrings.xml
     */
    private function fillTable3(string $xml, array $d, array $siList): string
    {
        foreach (Map::PERIOD_COLUMNS as $period => $col) {
            foreach (Map::PERIOD_ROWS as $key => $row) {
                $xml = $this->putSmart($xml, $col . $row, $d['performance'][$period][$key]);
            }
        }

        foreach (Map::STATUTORY_ROWS as $obligation => $row) {
            $status = $d['statutory'][$obligation]['status'];
            $xml = $this->putText($xml, Map::STATUTORY_STATUS_COLUMN . $row, $status === null ? null : strtoupper($status));
            $xml = $this->putText($xml, Map::STATUTORY_REMARKS_COLUMN . $row, $d['statutory'][$obligation]['remarks']);
        }

        $date = $d['declaration_date'] ? Carbon::parse($d['declaration_date'])->format('d/m/Y') : '';
        $xml = $this->putRichValue($xml, Map::DECLARATION_DATE_CELL, $date, $siList);

        return $this->putRichValue($xml, Map::DECLARATION_PLACE_CELL, (string) $d['declaration_place'], $siList);
    }

    /**
     * "Date: " / "Place:" cells are rich text: static lead-in plus a red
     * value run. Rebuild the same runs inline with only the red run's text
     * swapped, so the lead-in text and run formatting stay as designed.
     *
     * @param array<int, string> $siList
     */
    private function putRichValue(string $xml, string $ref, string $value, array $siList): string
    {
        return $this->rewriteCell($xml, $ref, function (?string $style, string $inner) use ($ref, $value, $siList) {
            if (! preg_match('/<v>(\d+)<\/v>/', $inner, $m) || ! isset($siList[(int) $m[1]])) {
                throw new RuntimeException("Application Form template cell {$ref} is not the expected rich-text cell.");
            }

            $runs = preg_replace_callback(
                '/(<color rgb="FFFF0000"\/>.*?<t[^>]*>)(.*?)(<\/t>)/s',
                fn (array $r) => $r[1] . $this->xmlEscape($value) . $r[3],
                $siList[(int) $m[1]],
                1,
                $replaced
            );

            if (! $replaced) {
                throw new RuntimeException("Application Form template cell {$ref} has no red value run.");
            }

            return $this->cellOpen($ref, $style) . ' t="inlineStr"><is>' . $runs . '</is></c>';
        });
    }

    // ---- Red sample data ----------------------------------------------

    /**
     * Blanks every red-font cell that holds a value, except the few red
     * cells that are really static labels. Structure-driven on purpose:
     * it keys off the template's own formatting rather than a hand-kept
     * list of sample cells.
     *
     * @param array<string, true> $redStyles
     * @param array<int, string> $strings
     */
    private function blankRedSampleCells(string $xml, array $redStyles, array $strings): string
    {
        return preg_replace_callback(
            '/<c r="([A-Z]+\d+)"([^>\/]*)>(.*?)<\/c>/s',
            function (array $m) use ($redStyles, $strings) {
                if (! preg_match('/\ss="(\d+)"/', $m[2], $sm) || ! isset($redStyles[$sm[1]])) {
                    return $m[0];
                }

                if (str_contains($m[2], 't="s"') && preg_match('/<v>(\d+)<\/v>/', $m[3], $vm)
                    && in_array(trim($strings[(int) $vm[1]] ?? ''), Map::STATIC_RED_LABELS, true)) {
                    return $m[0];
                }

                return '<c r="' . $m[1] . '" s="' . $sm[1] . '"/>';
            },
            $xml
        ) ?? $xml;
    }

    // ---- Workbook plumbing ---------------------------------------------

    /**
     * Sheet name => worksheet part, read from the workbook itself.
     *
     * @return array<string, string>
     */
    private function sheetParts(ZipArchive $zip): array
    {
        $workbook = simplexml_load_string((string) $zip->getFromName('xl/workbook.xml'));
        $rels = simplexml_load_string((string) $zip->getFromName('xl/_rels/workbook.xml.rels'));
        if ($workbook === false || $rels === false) {
            throw new RuntimeException('Application Form template workbook is unreadable.');
        }

        $targets = [];
        foreach ($rels->Relationship as $rel) {
            $targets[(string) $rel['Id']] = ltrim((string) $rel['Target'], '/');
        }

        $parts = [];
        foreach ($workbook->sheets->sheet as $sheet) {
            $rid = (string) $sheet->attributes('r', true)['id'];
            $target = $targets[$rid] ?? null;
            $parts[(string) $sheet['name']] = str_starts_with((string) $target, 'xl/') ? $target : 'xl/' . $target;
        }

        foreach ([Map::SHEET_1, Map::SHEET_2, Map::SHEET_3] as $name) {
            if (! isset($parts[$name])) {
                throw new RuntimeException("Application Form template has no sheet named {$name}.");
            }
        }

        return $parts;
    }

    /**
     * @return array<int, string>
     */
    private function siList(string $sharedXml): array
    {
        preg_match_all('/<si>(.*?)<\/si>/s', $sharedXml, $m);

        return $m[1];
    }

    /**
     * @return array<int, string> shared string index => plain text
     */
    private function sharedStringTexts(string $sharedXml): array
    {
        return array_map(function (string $si) {
            preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $si, $t);

            return html_entity_decode(implode('', $t[1]), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        }, $this->siList($sharedXml));
    }

    /**
     * Style (cellXfs) indexes whose font is red.
     *
     * @return array<string, true>
     */
    private function redStyleIds(string $stylesXml): array
    {
        $styles = simplexml_load_string($stylesXml);
        $redFonts = [];
        $index = 0;
        foreach ($styles->fonts->font as $font) {
            if (isset($font->color) && strtoupper((string) $font->color['rgb']) === 'FFFF0000') {
                $redFonts[$index] = true;
            }
            $index++;
        }

        $red = [];
        $index = 0;
        foreach ($styles->cellXfs->xf as $xf) {
            if (isset($redFonts[(int) $xf['fontId']])) {
                $red[(string) $index] = true;
            }
            $index++;
        }

        return $red;
    }

    private function excelSerial(string $date): int
    {
        return (int) Carbon::parse($date)->startOfDay()->diffInDays(Carbon::create(1899, 12, 30), true);
    }

    private function plain(float|int $n): string
    {
        return (string) (floor($n) == $n ? (int) $n : $n);
    }
}
