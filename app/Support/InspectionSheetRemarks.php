<?php

namespace App\Support;

use App\Models\Lead;
use Carbon\Carbon;

/**
 * Builds the narrative "Inspection Remarks / Observations" paragraph printed
 * under the Pre-Sanction Inspection Sheet (Annexure V-A).
 *
 * {@see buildParagraph()} is the single, centralized template — it is the
 * only place the wording lives. Everything else (the initial pre-fill on the
 * Lead form, the realtime "Regenerate" preview endpoint, and the PDF's
 * fallback when no remarks were saved) funnels through it, so changing the
 * wording only ever needs to happen here.
 *
 * A clause is dropped, not left blank, when its underlying data is empty —
 * the paragraph never contains "undefined", null, or a bare "{{token}}".
 */
class InspectionSheetRemarks
{
    /**
     * Generate the paragraph for a persisted (or in-memory) Lead.
     */
    public static function generate(?Lead $lead, array $bld = []): string
    {
        if (! $lead) {
            return '';
        }

        return self::buildParagraph([
            'applicant_name' => $lead->name,
            'business_name' => $lead->business_name ?: $lead->firm_name,
            'business_constitution' => $lead->constitution_of_business ?: $lead->constitution?->name,
            'business_address' => ($bld['office_organization_address'] ?? '') ?: ($lead->business_address ?: $lead->address),
            'business_activity' => $lead->introduction ?: $lead->business_activity,
            'business_experience' => ($bld['years_in_business'] ?? '') ?: $lead->business_experience,
            'employee_count' => $lead->no_of_manpower,
            'machinery_details' => $lead->term_loan_machinery_details,
            'machinery_amount' => $lead->term_loan_amount,
            'city' => $lead->city?->name,
            'visit_date' => ! empty($bld['visit_office_date']) ? Carbon::parse($bld['visit_office_date'])->format('d/m/Y') : '',
            'tvr_result' => $bld['office_tvr_result'] ?? '',
        ]);
    }

    /**
     * Generate the paragraph from raw form field values (used by the
     * realtime "Regenerate from Lead Data" preview while creating/editing a
     * Lead, before anything is saved). Keys mirror the Create/Edit form's
     * field names.
     */
    public static function generateFromInputs(array $data): string
    {
        return self::buildParagraph([
            'applicant_name' => $data['name'] ?? '',
            'business_name' => ($data['business_name'] ?? '') ?: ($data['firm_name'] ?? ''),
            'business_constitution' => $data['constitution_name'] ?? '',
            'business_address' => $data['business_address'] ?? '',
            'business_activity' => $data['business_activity'] ?? '',
            'business_experience' => $data['business_experience'] ?? '',
            'employee_count' => $data['no_of_manpower'] ?? '',
            'machinery_details' => $data['term_loan_machinery_details'] ?? '',
            'machinery_amount' => $data['term_loan_amount'] ?? '',
            'city' => $data['city_name'] ?? '',
            'visit_date' => ! empty($data['visit_office_date']) ? Carbon::parse($data['visit_office_date'])->format('d/m/Y') : '',
            'tvr_result' => $data['office_tvr_result'] ?? '',
        ]);
    }

    /**
     * The centralized template. Builds each clause only when its data is
     * present, then joins them into the final paragraph.
     */
    private static function buildParagraph(array $f): string
    {
        $clean = fn ($v) => trim((string) $v);
        $oneLine = fn ($v) => preg_replace('/\s*[\r\n]+\s*/', ', ', trim((string) $v));

        $name = $clean($f['applicant_name'] ?? '');
        $firm = $clean($f['business_name'] ?? '');
        $constitution = $clean($f['business_constitution'] ?? '');
        $address = $oneLine($f['business_address'] ?? '');
        $activity = $clean($f['business_activity'] ?? '');
        $experience = $clean($f['business_experience'] ?? '');
        $employeeCount = $clean($f['employee_count'] ?? '');
        $machinery = $clean($f['machinery_details'] ?? '');
        $machineryAmount = $clean($f['machinery_amount'] ?? '');
        $city = $clean($f['city'] ?? '');
        $visitDate = $clean($f['visit_date'] ?? '');
        $tvr = $clean($f['tvr_result'] ?? '');

        if (! $name && ! $firm) {
            return '';
        }

        $p1 = 'Visited the firm ' . ($firm ?: $name) . ($constitution ? ", a {$constitution} concern," : '') . ($visitDate ? " on {$visitDate}" : '') . '.';
        if ($address) {
            $p1 .= " The unit is located at {$address}";
        }
        if ($name) {
            $p1 .= ($address ? ' and met' : ' Met') . " proprietor Mr {$name}" . ($firm ? " of {$firm}" : '');
        }
        $p1 .= '.';

        $p2 = [];
        if ($activity) {
            $p2[] = "The firm is engaged in {$activity} work.";
        }
        if ($experience) {
            $p2[] = "He has approximately {$experience} of experience in this business line.";
        }
        $p2[] = 'The firm procures its raw materials from local suppliers/parties.';
        if ($employeeCount) {
            $p2[] = "The firm currently employs {$employeeCount}.";
        }

        $p3 = $machinery
            ? ($name ?: 'The applicant') . " is purchasing {$machinery}" . ($machineryAmount ? ", for {$machineryAmount}/-" : '') . '.'
            : '';

        $p4 = $city ? "The proprietor has been residing in {$city}." : '';

        $p5 = 'There is board of firm sighted and checked light bill and old sales bill.';
        if (strcasecmp($tvr, 'Positive') === 0) {
            $p5 .= ' Overall visit found positive and satisfactory response.';
        } elseif (strcasecmp($tvr, 'Negative') === 0) {
            $p5 .= ' Overall visit found negative response.';
        }

        return implode("\n", array_filter([$p1, implode(' ', $p2), $p3, $p4, $p5]));
    }
}
