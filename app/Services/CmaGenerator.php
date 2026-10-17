<?php

namespace App\Services;

use App\Models\Lead;
use App\Services\Concerns\ManipulatesXlsxSharedStrings;
use App\Services\Concerns\WritesXlsxCells;
use App\Support\CmaProfileData;
use RuntimeException;
use ZipArchive;

/**
 * Generates a Lead-specific CMA workbook from the master template by copying
 * the .xlsx file as-is and rewriting only the shared-string text runs that
 * hold the demo firm/proprietor name and address. Every other zip entry
 * (styles, formulas, merged cells, printer settings, calc chain, etc.) is
 * left byte-for-byte untouched, and the master template itself is never
 * opened for writing.
 *
 * The values come from the lead's CMA profile (CmaProfileData): the lead's own
 * data, kept in step with the CMA tab, plus whatever was entered there. The
 * workbook's financial statements and formulas are never touched.
 */
class CmaGenerator
{
    use ManipulatesXlsxSharedStrings;
    use WritesXlsxCells;

    private const TEMPLATE_PATH = 'upload/CMA/deml_cma.xlsx';

    /**
     * Exact substrings in the template's shared strings that represent the
     * demo firm name, proprietor name, and the two address lines. Matched
     * as plain substrings (not full-string equality) so that surrounding
     * noise in the template (": ", "FOR ", stray spaces, "Address :" prefix)
     * is preserved untouched around the replaced value.
     */
    private const FIRM_NAME_NEEDLE = 'R R ENTERPRISE';
    private const PROPRIETOR_NAME_NEEDLE = 'BHAVESHKUMAR RAVJIBHAI SOJITRA';
    private const ADDRESS_LINE1_NEEDLES = [
        'BLOCK NO 203, GOTHAN BRIDGE THI AAGAL UMRA CHOKDI NI BAJUMA',
        '173/A-2, 5TH FLOOR, SOUTH BABRIK INDUSTRIAL,',
    ];
    private const ADDRESS_LINE2_NEEDLE = 'VELANJA ROAD SURAT, GUJARAT, 395004';

    /**
     * The "profile" worksheet has one row per field, each holding a shared
     * string that is NOT reused anywhere else in the workbook (verified by
     * cross-referencing every worksheet). So instead of substring matching,
     * these are addressed by their exact shared-string index and rewritten
     * to "<prefix><value>", or cleared entirely when there's no matching
     * Lead column (or the Lead's value is blank).
     */
    private const PROFILE_FIELD_INDEXES = [
        257 => ['prefix' => ': ', 'field' => 'activity'],
        // The Title sheet's "BUSINESS ACTIVITY :" line prints the same activity (its own string).
        256 => ['prefix' => ' ', 'field' => 'activity'],
        310 => ['prefix' => ': ', 'field' => 'pan'],
        312 => ['prefix' => ': ', 'field' => 'udyam_no'],
        313 => ['prefix' => ': ', 'field' => 'mobile'],
        314 => ['prefix' => ': ', 'field' => 'email'],
        336 => ['prefix' => ': ', 'field' => 'business_experience'],
        // ": Surat" is one shared string used by both "Current Area Of
        // Operation" (F24) and "-Location" (F31); F31 is written separately below.
        315 => ['prefix' => ': ', 'field' => 'area_of_operation'],
        296 => ['prefix' => ':  ', 'field' => 'manpower'],
        334 => ['prefix' => ':    ', 'field' => 'space'],
        30 => ['prefix' => ':    ', 'field' => 'connectivity'],
        33 => ['prefix' => ': ', 'field' => 'electricity_water'],
        35 => ['prefix' => ': ', 'field' => 'existing_facility'],
    ];

    /** The "profile" worksheet part, and the cell that shares string 315 with F24. */
    private const PROFILE_SHEET_PART = 'xl/worksheets/sheet3.xml';
    private const LOCATION_CELL = 'F31';

    public function generate(Lead $lead): string
    {
        $templatePath = public_path(self::TEMPLATE_PATH);

        if (! is_file($templatePath)) {
            throw new RuntimeException('CMA master template not found at ' . self::TEMPLATE_PATH);
        }

        $relativeDir = 'cma/' . $lead->id;
        $relativePath = $relativeDir . '/CMA.xlsx';
        $destPath = storage_path('app/public/' . $relativePath);

        if (! is_dir(dirname($destPath))) {
            mkdir(dirname($destPath), 0755, true);
        }

        // Copy the template first — every mutation below happens on the
        // copy only, so the original file is never opened for writing.
        if (! copy($templatePath, $destPath)) {
            throw new RuntimeException('Failed to copy CMA template.');
        }

        $profile = CmaProfileData::for($lead);
        [$firmName, $proprietorName, $addressLine1, $addressLine2] = $this->resolveReplacements($profile);

        $zip = new ZipArchive();
        if ($zip->open($destPath) !== true) {
            throw new RuntimeException('Failed to open generated CMA workbook.');
        }

        $sharedStrings = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStrings === false) {
            $zip->close();
            throw new RuntimeException('CMA template is missing xl/sharedStrings.xml.');
        }

        $sharedStrings = $this->replaceWithinTextTags($sharedStrings, self::FIRM_NAME_NEEDLE, $firmName);
        $sharedStrings = $this->replaceWithinTextTags($sharedStrings, self::PROPRIETOR_NAME_NEEDLE, $proprietorName);
        foreach (self::ADDRESS_LINE1_NEEDLES as $needle) {
            $sharedStrings = $this->replaceWithinTextTags($sharedStrings, $needle, $addressLine1);
        }
        $sharedStrings = $this->replaceWithinTextTags($sharedStrings, self::ADDRESS_LINE2_NEEDLE, $addressLine2);

        $indexReplacements = [];
        foreach (self::PROFILE_FIELD_INDEXES as $index => $config) {
            $value = trim((string) $profile[$config['field']]);
            $indexReplacements[$index] = $value === '' ? '' : $config['prefix'] . $value;
        }
        $indexReplacements[316] = $this->resolveCapitalRequirement($lead);

        $sharedStrings = $this->replaceByIndex($sharedStrings, $indexReplacements);

        $zip->addFromString('xl/sharedStrings.xml', $sharedStrings);

        $location = trim((string) $profile['location']);
        $sheet = $zip->getFromName(self::PROFILE_SHEET_PART);
        if ($sheet !== false && str_contains($sheet, 'r="' . self::LOCATION_CELL . '"')) {
            $zip->addFromString(self::PROFILE_SHEET_PART, $this->putText($sheet, self::LOCATION_CELL, $location === '' ? '' : ': ' . $location));
        }
        $zip->close();

        return $relativePath;
    }

    /**
     * @param array<string, ?string> $profile
     * @return array{0:string,1:string,2:string,3:string} [firmName, proprietorName, addressLine1, addressLine2]
     */
    private function resolveReplacements(array $profile): array
    {
        [$addressLine1, $addressLine2] = $this->splitAddress(trim((string) $profile['address']));

        return [
            trim((string) $profile['firm_name']),
            trim((string) $profile['proprietor_name']),
            $addressLine1,
            $addressLine2,
        ];
    }

    /**
     * The template splits the address across two lines/cells. Leads store a
     * single free-text address field, so if the user entered it on multiple
     * lines we use the first line as line 1 and the rest (joined) as line 2;
     * otherwise the whole address goes on line 1 and line 2 is left blank.
     *
     * @return array{0:string,1:string}
     */
    private function splitAddress(string $address): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $address) ?: [$address];
        $lines = array_values(array_filter(array_map('trim', $lines), fn ($l) => $l !== ''));

        if (count($lines) === 0) {
            return ['', ''];
        }

        if (count($lines) === 1) {
            return [$lines[0], ''];
        }

        return [$lines[0], implode(', ', array_slice($lines, 1))];
    }

    /**
     * "Capital Requirement" in the template is a single free-text cell
     * ("Term Loan: Rs.44.50 lakh"). Leads track this as separate amount
     * fields, so we build the closest equivalent from whichever ones are
     * filled in, preferring the term loan, without inventing a unit
     * (lakh/crore) conversion the Lead data doesn't specify.
     */
    private function resolveCapitalRequirement(Lead $lead): string
    {
        $parts = [];

        if ($termLoan = trim((string) $lead->term_loan_amount)) {
            $parts[] = 'Term Loan: Rs.' . $termLoan;
        }
        if ($cc = trim((string) $lead->cc_amount)) {
            $parts[] = 'CC: Rs.' . $cc;
        }

        if (empty($parts) && ($required = trim((string) $lead->required_loan_amount))) {
            $parts[] = 'Rs.' . $required;
        }

        return empty($parts) ? '' : ': ' . implode(', ', $parts);
    }

}
