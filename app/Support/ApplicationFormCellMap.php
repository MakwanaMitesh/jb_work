<?php

namespace App\Support;

/**
 * Where each Application Form field lands in the master workbook
 * (public/upload/Application_form/application.xlsx). Cell references are
 * the top-left cell of the template's merged range. Keys are the field
 * names from ApplicationFormData.
 *
 * Anything in the template that is red-font sample data and is NOT listed
 * here is blanked by the generator regardless (see
 * ApplicationFormGenerator::blankRedSampleCells), so a sample value can
 * never leak into a generated file through an unmapped cell.
 */
class ApplicationFormCellMap
{
    public const SHEET_1 = 'Table 1';
    public const SHEET_2 = 'Table 2';
    public const SHEET_3 = 'Table 3';

    /** Plain text fields: field => cells (written verbatim). */
    public const TABLE_1_TEXT = [
        'enterprise_name' => ['A7', 'D9'],
        'application_serial_no' => ['F7'],
        'branch_name' => ['O7'],
        'category' => ['X7'],
        'business_address' => ['D11'],
        'state' => ['L12'],
        'telephone' => ['D14'],
        'mobile_number' => ['S14'],
        'email' => ['D15'],
        'activity_existing' => ['M16'],
        'activity_proposed' => ['M17'],
        'registration_no_act' => ['M20'],
        'registered_office_address' => ['M21'],
        'applicant_name' => ['B26'],
        'sex' => ['K26'],
        'qualification' => ['S26'],
        'experience_years' => ['Z26'],
        'id_proof_type' => ['B29'],
        'id_proof_number' => ['E29'],
        'address_proof_type' => ['I29'],
        'address_proof_number' => ['L29'],
        'pan_din_number' => ['P29'],
        'bank_relationship' => ['V29'],
    ];

    /** Country code sits in its own cell, shown only alongside a mobile number. */
    public const MOBILE_COUNTRY_CODE_CELL = 'R14';
    public const DATE_OF_BIRTH_CELL = 'H26';
    public const RESIDENTIAL_ADDRESS_CELL = 'M26';

    /** One digit per cell, left to right. */
    public const PINCODE_DIGIT_CELLS = ['W12', 'Y12', 'AA12', 'AC12', 'AD12', 'AF12'];
    public const COMMENCEMENT_DIGIT_CELLS = ['M18', 'O18', 'Q18', 'S18', 'U18', 'Y18', 'AB18', 'AE18'];

    /**
     * Pick-one rows. The template marks the chosen option by restyling its
     * label cell (bold on yellow, style 42) and appending a tick, so each
     * option is a label cell: option => [cell, label text].
     */
    public const TABLE_1_CHOICES = [
        'constitution' => [
            'Proprietary' => ['D10', 'Proprietary'],
            'Partnership' => ['J10', 'Partnership'],
            'Pvt. Ltd.' => ['N10', 'Pvt. Ltd.'],
            'Ltd. Company' => ['Q10', 'Ltd. Company'],
            'Others' => ['W10', 'Any Others (specify)'],
        ],
        'business_premises' => [
            'Rented' => ['S13', 'Rented'],
            'Owned' => ['Y13', 'Owned'],
        ],
        'is_registered' => [
            'Yes' => ['O19', 'Yes'],
            'No' => ['U19', 'No'],
        ],
        'social_category' => [
            'SC' => ['O22', 'SC'],
            'ST' => ['Q22', 'ST'],
            'OBC' => ['T22', 'OBC'],
        ],
        'minority_community' => [
            'Buddhists' => ['E23', 'Buddhists'],
            'Muslims' => ['J23', 'Muslims'],
            'Christians' => ['M23', 'Christians'],
            'Sikhs' => ['Q23', 'Sikhs'],
            'Jains' => ['T23', 'Jains'],
            'Zoroastrians' => ['X23', 'Zoroastrians'],
        ],
    ];

    /** Stand-alone tick cells in the template's demo selection; the label styling carries the choice instead. */
    public const TABLE_1_STRAY_TICKS = ['C10', 'AJ11', 'R13', 'M19', 'Q19', 'M22', 'C23'];

    /** Red-styled cells that are static labels, not sample data. */
    public const STATIC_RED_LABELS = ['State', 'PIN Code', 'Business Premises', 'Mobile No.'];

    public const SELECTED_STYLE = '42';
    public const UNSELECTED_STYLE = '40';

    // ---- Table 2 -------------------------------------------------------

    public const ASSOCIATE_ROWS = [3, 4];
    public const ASSOCIATE_COLUMNS = ['name' => 'A', 'address' => 'C', 'bank' => 'H', 'nature' => 'L', 'interest' => 'Q'];

    public const EXISTING_ROWS = ['savings' => 7, 'current' => 8, 'cash_credit' => 9, 'term_loan' => 10, 'lc_bg' => 11];
    public const EXISTING_COLUMNS = ['bank' => 'E', 'limit' => 'J', 'outstanding' => 'M', 'security' => 'R', 'status' => 'T'];
    public const CUSTOMER_ID_CELL = 'R12';

    public const PROPOSED_ROWS = ['cash_credit' => 16, 'term_loan' => 17, 'lc_bg' => 18];
    public const PROPOSED_COLUMNS = ['amount' => 'F', 'purpose' => 'I', 'security' => 'N'];
    public const PROPOSED_TOTAL_CELL = 'F19';

    /** Working capital: field => cell. FY labels only override the template's header text when entered. */
    public const WORKING_CAPITAL_AMOUNTS = [
        'wc_actual_sales_1' => 'A23',
        'wc_actual_sales_2' => 'B23',
        'wc_projected_sales' => 'D23',
        'wc_inventory' => 'J23',
        'wc_debtors' => 'M23',
        'wc_creditors' => 'P23',
        'wc_promoter_contribution' => 'S23',
        'wc_limits' => 'V23',
    ];
    public const WORKING_CYCLE_CELL = 'G23';
    public const FY_LABEL_CELLS = ['wc_fy_label_1' => 'A22', 'wc_fy_label_2' => 'B22'];

    public const MACHINERY_ROWS = [26, 27];
    public const MACHINERY_COLUMNS = ['type' => 'A', 'purpose' => 'D', 'supplier' => 'H', 'total_cost' => 'K', 'promoter_contribution' => 'O', 'loan_required' => 'U'];
    /** Total-row formulas in the template (they only summed the first row; widened to both). */
    public const MACHINERY_TOTAL_ROW = 28;
    public const MACHINERY_TOTAL_COLUMNS = ['K', 'O', 'U'];
    public const REPAYMENT_CELL = 'O29';

    // ---- Table 3 -------------------------------------------------------

    public const PERIOD_COLUMNS = ['past_year_2' => 'B', 'past_year_1' => 'D', 'present_year' => 'F', 'next_year' => 'I'];
    public const PERIOD_ROWS = ['net_sales' => 4, 'net_profit' => 5, 'net_worth' => 6];

    public const STATUTORY_ROWS = [
        'shops_establishment' => 9, 'msme' => 10, 'drug_license' => 11,
        'sales_tax' => 12, 'income_tax' => 13, 'other_dues' => 14,
    ];
    public const STATUTORY_STATUS_COLUMN = 'E';
    public const STATUTORY_REMARKS_COLUMN = 'H';

    /** Rich-text cells: static lead-in text plus a red value run. */
    public const DECLARATION_DATE_CELL = 'A18';
    public const DECLARATION_PLACE_CELL = 'A19';

    /**
     * Every scalar Application Form field => [sheet, cells it lands in].
     * Together with ApplicationFormData::LEAD_LINKS this gives the full
     * chain: Details Sheet input -> Application Form field -> sheet/cell.
     *
     * @return array<string, array{0: string, 1: array<int, string>}>
     */
    public static function scalarFieldCells(): array
    {
        $map = [];

        foreach (self::TABLE_1_TEXT as $field => $cells) {
            $map[$field] = [self::SHEET_1, $cells];
        }
        foreach (self::TABLE_1_CHOICES as $field => $options) {
            $map[$field] = [self::SHEET_1, array_column($options, 0)];
        }

        $map['constitution_other'] = [self::SHEET_1, [self::TABLE_1_CHOICES['constitution']['Others'][0]]];
        $map['mobile_country_code'] = [self::SHEET_1, [self::MOBILE_COUNTRY_CODE_CELL]];
        $map['date_of_birth'] = [self::SHEET_1, [self::DATE_OF_BIRTH_CELL]];
        $map['residential_address'] = [self::SHEET_1, [self::RESIDENTIAL_ADDRESS_CELL]];
        $map['applicant_mobile'] = [self::SHEET_1, [self::RESIDENTIAL_ADDRESS_CELL]];
        $map['pincode'] = [self::SHEET_1, self::PINCODE_DIGIT_CELLS];
        $map['commencement_date'] = [self::SHEET_1, self::COMMENCEMENT_DIGIT_CELLS];

        $map['customer_id'] = [self::SHEET_2, [self::CUSTOMER_ID_CELL]];
        foreach (self::WORKING_CAPITAL_AMOUNTS as $field => $cell) {
            $map[$field] = [self::SHEET_2, [$cell]];
        }
        $map['wc_working_cycle_months'] = [self::SHEET_2, [self::WORKING_CYCLE_CELL]];
        foreach (self::FY_LABEL_CELLS as $field => $cell) {
            $map[$field] = [self::SHEET_2, [$cell]];
        }
        $map['repayment_period'] = [self::SHEET_2, [self::REPAYMENT_CELL]];
        $map['moratorium_period'] = [self::SHEET_2, [self::REPAYMENT_CELL]];

        $map['declaration_date'] = [self::SHEET_3, [self::DECLARATION_DATE_CELL]];
        $map['declaration_place'] = [self::SHEET_3, [self::DECLARATION_PLACE_CELL]];

        return $map;
    }

    /**
     * Every cell the generator writes, per sheet (row/column groups expanded).
     *
     * @return array<string, array<int, string>>
     */
    public static function writtenCells(): array
    {
        $cells = [self::SHEET_1 => [], self::SHEET_2 => [], self::SHEET_3 => []];

        foreach (self::scalarFieldCells() as [$sheet, $refs]) {
            array_push($cells[$sheet], ...$refs);
        }

        // Demo tick cells: always written (blanked), never filled.
        array_push($cells[self::SHEET_1], ...self::TABLE_1_STRAY_TICKS);

        $grid = function (string $sheet, array $rows, array $columns) use (&$cells) {
            foreach ($rows as $row) {
                foreach ($columns as $col) {
                    $cells[$sheet][] = $col . $row;
                }
            }
        };

        $grid(self::SHEET_2, self::ASSOCIATE_ROWS, self::ASSOCIATE_COLUMNS);
        $grid(self::SHEET_2, self::EXISTING_ROWS, self::EXISTING_COLUMNS);
        $grid(self::SHEET_2, self::PROPOSED_ROWS, self::PROPOSED_COLUMNS);
        $grid(self::SHEET_2, self::MACHINERY_ROWS, self::MACHINERY_COLUMNS);
        $grid(self::SHEET_2, [self::MACHINERY_TOTAL_ROW], self::MACHINERY_TOTAL_COLUMNS);
        $cells[self::SHEET_2][] = self::PROPOSED_TOTAL_CELL;

        $grid(self::SHEET_3, self::PERIOD_ROWS, self::PERIOD_COLUMNS);
        $grid(self::SHEET_3, self::STATUTORY_ROWS, [self::STATUTORY_STATUS_COLUMN, self::STATUTORY_REMARKS_COLUMN]);

        return array_map(fn ($refs) => array_values(array_unique($refs)), $cells);
    }
}
