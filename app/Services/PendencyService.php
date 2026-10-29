<?php

namespace App\Services;

use App\Models\DocumentType;
use App\Models\Lead;
use App\Models\User;
use App\Support\ApplicationFormData;
use App\Support\CmaProfileData;
use App\Support\DprData;
use App\Support\LeadModuleAccess;
use App\Support\OpinionReportData;
use App\Support\StockStatementData;

/**
 * Computes, for a Lead, which of its modules' subsections are still pending versus already
 * filled in — entirely from data that already exists (the Lead's own columns, the DPR/CMA/OR/
 * Stock Statement "for the lead" resolvers, the Application Form's own required-field list, the
 * Document Master's required/optional flags, and the existing hasGenerated*()/
 * isInspectionSheetApplicable() checks on Lead). No new columns, tables or generators.
 *
 * A subsection's status is one of:
 *  - 'complete': its defining field(s) have a value, or its required generation/upload step is done.
 *  - 'pending':  none of its defining fields have a value yet (or the step isn't done).
 *  - 'na':       the subsection does not apply to this lead at all (e.g. the SBI Business Loan
 *                Inspection Sheet module, for a lead whose bank/loan product doesn't call for it,
 *                or a Document Master type with nothing marked required). 'na' subsections never
 *                count as pending — only "nothing entered where something could have been" does.
 *
 * Where the codebase already encodes a real list of required fields (Application Form's
 * REQUIRED_FOR_GENERATION, the Document Master's is_required flag, Lead's own `required`
 * validation), that is used verbatim. Everywhere else (DPR/CMA/OR/Stock Statement's own
 * sub-tabs, Bank/Business/Loan Details), the rule is presence-based: a subsection with no
 * "required" list of its own is pending only when every one of the fields that belong to it is
 * still empty, never a count of individually-missing optional fields.
 */
class PendencyService
{
    /**
     * @param User|null $user when given, modules the user lacks "{module}.view" for are dropped
     *        entirely (not shown as "na" — the user shouldn't learn the module exists at all), and
     *        subsections they lack "{module}.{subsection}.view" for are dropped the same way. Pass
     *        null for an unfiltered, "sees everything" result (used by code that already enforces
     *        access itself, and by tests of the pendency rules in isolation from permissions).
     * @return array{modules: array<int, array<string, mixed>>, pending: int, completed: int}
     */
    public function forLead(Lead $lead, ?User $user = null): array
    {
        $lead->loadMissing(['dprReport', 'cmaProfile', 'opinionReport', 'stockStatement', 'leadDocuments.documentType']);

        $modules = array_filter([
            $this->detailsSheet($lead),
            $this->bankLoan($lead),
            $this->applicationForm($lead),
            $this->documents($lead),
            $this->stockStatement($lead),
            $this->opinionReport($lead),
            $this->cma($lead),
            $this->dpr($lead),
            $this->bankDetails($lead),
            $this->businessDetails($lead),
            $this->loanDetails($lead),
        ]);

        if ($user) {
            $modules = array_filter($modules, fn ($m) => LeadModuleAccess::canViewModule($user, $m['key']));
            foreach ($modules as &$m) {
                // Documents has no fixed subsection permission list (its "subsections" are whichever
                // Document Master types are required, not a code-level set) — documents.view alone
                // governs it, same as before.
                if (! isset(\Database\Seeders\PermissionSeeder::LEAD_MODULE_SUBSECTIONS[$m['key']])) {
                    continue;
                }
                $m['subsections'] = array_values(array_filter(
                    $m['subsections'],
                    fn ($sub) => LeadModuleAccess::canViewSubsection($user, $m['key'], $sub['key'])
                ));
            }
            unset($m);
        }

        $pending = 0;
        $completed = 0;
        foreach ($modules as &$module) {
            $module['pending_count'] = 0;
            $module['completed_count'] = 0;
            foreach ($module['subsections'] as $sub) {
                if ($sub['status'] === 'pending') {
                    $module['pending_count']++;
                } elseif ($sub['status'] === 'complete') {
                    $module['completed_count']++;
                }
            }
            $pending += $module['pending_count'];
            $completed += $module['completed_count'];
        }

        return ['modules' => array_values($modules), 'pending' => $pending, 'completed' => $completed];
    }

    /**
     * Lightweight per-lead totals for the dashboard's lead table (no full subsection detail). See
     * forLead() for what passing $user does.
     *
     * @param \Illuminate\Support\Collection<int, Lead> $leads
     * @return array<int, array{lead: Lead, pending: int, completed: int, pending_modules: array<int, string>}>
     */
    public function summaries(\Illuminate\Support\Collection $leads, ?User $user = null): array
    {
        return $leads->map(function (Lead $lead) use ($user) {
            $result = $this->forLead($lead, $user);
            $pendingModules = array_values(array_map(
                fn ($m) => $m['label'],
                array_filter($result['modules'], fn ($m) => $m['pending_count'] > 0)
            ));

            return ['lead' => $lead, 'pending' => $result['pending'], 'completed' => $result['completed'], 'pending_modules' => $pendingModules];
        })->all();
    }

    // ---- helpers ---------------------------------------------------------

    private function filled(mixed ...$values): bool
    {
        foreach ($values as $v) {
            if (is_array($v)) {
                if (! empty($v)) {
                    return true;
                }

                continue;
            }
            if (is_scalar($v) && trim((string) $v) !== '') {
                return true;
            }
        }

        return false;
    }

    private function sub(string $key, string $label, bool $complete, ?string $anchor = null, ?string $note = null): array
    {
        return ['key' => $key, 'label' => $label, 'status' => $complete ? 'complete' : 'pending', 'anchor' => $anchor, 'note' => $note];
    }

    private function module(string $key, string $label, string $tab, array $subsections, bool $applicable = true, ?string $naReason = null): ?array
    {
        if (! $applicable) {
            return ['key' => $key, 'label' => $label, 'tab' => $tab, 'applicable' => false, 'na_reason' => $naReason, 'subsections' => []];
        }

        return ['key' => $key, 'label' => $label, 'tab' => $tab, 'applicable' => true, 'na_reason' => null, 'subsections' => $subsections];
    }

    // ---- A. Details Sheet -------------------------------------------------

    private function detailsSheet(Lead $lead): array
    {
        $itrRows = collect($lead->itr_details ?? [])->filter(fn ($r) => $this->filled($r['assessment_year'] ?? null, $r['itr_audited'] ?? null));
        $bankRows = collect($lead->bank_details ?? [])->filter(fn ($r) => $this->filled($r['bank_name'] ?? null, $r['account_number'] ?? null));

        return $this->module('details_sheet', 'Details Sheet', 'kyc-panel', [
            // name/mobile_number are the only fields the app itself requires to save a Lead at all,
            // so this is "complete" for every saved lead; the rest of the KYC fields are optional here.
            $this->sub('basic_details', 'Basic Details', $this->filled($lead->name, $lead->mobile_number), '#kyc-tab'),
            $this->sub('itr_details', 'ITR Details', $itrRows->isNotEmpty(), '#kyc-tab'),
            $this->sub('bank_details', 'Bank Details', $bankRows->isNotEmpty(), '#bank-tab'),
        ]);
    }

    // ---- B. Bank Loan (the SBI Business Loan Inspection Sheet) -----------

    private function bankLoan(Lead $lead): array
    {
        if (! $lead->isInspectionSheetApplicable()) {
            return $this->module('bank_loan', 'Bank Loan', 'bank-loan-panel', [], false,
                'Applies only when the Bank is SBI and the Loan Product is Business Loan (Lead::isInspectionSheetApplicable).');
        }

        $bld = (array) ($lead->bank_loan_details ?? []);

        return $this->module('bank_loan', 'Bank Loan', 'bank-loan-panel', [
            $this->sub('visit_applicant_details', 'Visit & Applicant Details',
                $this->filled($bld['applicant_coapplicant_guarantor_name'] ?? null, $bld['office_organization_address'] ?? null, $bld['years_in_business'] ?? null), '#bank-loan-tab'),
            $this->sub('branch_signoff_details', 'Branch & Sign-off Details',
                $this->filled($bld['branch_name'] ?? null, $bld['file_no'] ?? null), '#bank-loan-tab'),
            $this->sub('inspection_sheet_generated', 'Inspection Sheet Generated', $lead->hasGeneratedInspectionSheet(), '#bank-loan-tab',
                'Uses Lead::hasGeneratedInspectionSheet() — the existing PDF generation check.'),
        ]);
    }

    // ---- C. Application Form ----------------------------------------------

    private function applicationForm(Lead $lead): array
    {
        $resolved = ApplicationFormData::for($lead);

        $missing = array_filter(
            array_keys(ApplicationFormData::REQUIRED_FOR_GENERATION),
            fn ($field) => ! $this->filled($resolved[$field] ?? null)
        );

        return $this->module('application_form', 'Application Form', 'application-form-panel', [
            $this->sub('application_form', 'Application Form', empty($missing), '#application-form-tab',
                empty($missing) ? null : 'Missing: ' . implode(', ', array_map(fn ($f) => ApplicationFormData::REQUIRED_FOR_GENERATION[$f], $missing))),
        ]);
    }

    // ---- D. Documents -------------------------------------------------------

    private function documents(Lead $lead): array
    {
        $requiredTypes = DocumentType::where('status', 'active')->where('is_required', true)->ordered()->get();
        if ($requiredTypes->isEmpty()) {
            return $this->module('documents', 'Documents', 'documents-panel', [], false,
                'No document type in the Document Master is currently marked required.');
        }

        $uploadedTypeIds = $lead->leadDocuments->pluck('document_type_id')->unique();
        $subsections = $requiredTypes->map(
            fn (DocumentType $type) => $this->sub('doc_' . $type->id, $type->name, $uploadedTypeIds->contains($type->id), '#documents-tab')
        )->all();

        return $this->module('documents', 'Documents', 'documents-panel', $subsections);
    }

    // ---- E. Stock Statement --------------------------------------------------

    private function stockStatement(Lead $lead): array
    {
        $d = StockStatementData::for($lead);
        $hasDebtors = $this->filled($d['debtor_name'], $d['debtor_amount'], $d['debtor2_name'], $d['debtor2_up6_amount'])
            || collect($d['age_analysis'] ?? [])->flatten()->filter()->isNotEmpty();
        $hasOtherInfo = collect($d['other_info'] ?? [])->flatten()->filter()->isNotEmpty() || $this->filled($d['sundry_creditors_year_end']);

        return $this->module('stock_statement', 'Stock Statement', 'stock-statement-panel', [
            $this->sub('inventories', 'INVENTORIES', collect($d['items'] ?? [])->isNotEmpty(), '#stock-statement-tab'),
            $this->sub('book_debts', 'BOOK DEBTS', $hasDebtors, '#stock-statement-tab'),
            $this->sub('other_information', 'OTHER INFORMATION', $hasOtherInfo, '#stock-statement-tab'),
        ]);
    }

    // ---- F. Opinion Report ----------------------------------------------------

    private function opinionReport(Lead $lead): array
    {
        $d = OpinionReportData::for($lead);

        return $this->module('opinion_report', 'Opinion Report', 'opinion-report-panel', [
            $this->sub('affidavit_personal_details', 'Affidavit / Personal Details',
                $this->filled($d['deponent_name'], $d['relationship'], $d['father_name'], $d['date_of_birth'], $d['place_of_birth'], $d['occupation']), '#opinion-report-tab'),
            $this->sub('bank_borrower_details', 'Bank and Borrower Details', $this->filled($d['branch'], $d['borrower_name'], $d['assets_date']), '#opinion-report-tab'),
            $this->sub('properties_india', 'Immovable Properties in India', collect($d['properties'] ?? [])->isNotEmpty(), '#opinion-report-tab'),
            $this->sub('properties_outside_india', 'Immovable Properties Outside India', $this->filled($d['outside_india']), '#opinion-report-tab'),
            $this->sub('liquid_assets', 'Liquid Assets', collect($d['liquid_assets'] ?? [])->flatten()->filter()->isNotEmpty(), '#opinion-report-tab'),
            $this->sub('government_securities', 'Government Securities', $this->filled($d['gov_securities']), '#opinion-report-tab'),
            $this->sub('shares', 'Shares', $this->filled($d['shares']), '#opinion-report-tab'),
            $this->sub('life_insurance_policies', 'Life Insurance Policies', $this->filled($d['life_insurance'], $d['life_policy_numbers'], $d['life_surrender_value']), '#opinion-report-tab'),
            $this->sub('jewellery', 'Jewellery', $this->filled($d['jewellery']), '#opinion-report-tab'),
            $this->sub('associate_investments', 'Investments in Associate Firms', $this->filled($d['associate_investment']), '#opinion-report-tab'),
            $this->sub('intangible_assets', 'Intangible Assets', $this->filled($d['intangible_assets']), '#opinion-report-tab'),
            $this->sub('monthly_income', 'Monthly Income', $this->filled($d['income_per_month']), '#opinion-report-tab'),
            $this->sub('liabilities', 'Liabilities', $this->filled($d['borrowings_banks'], $d['borrowings_market'], $d['borrowings_other'], $d['indirect_liability']), '#opinion-report-tab'),
            $this->sub('other_assets_liabilities', 'Other Assets and Liabilities', $this->filled($d['other_asset'], $d['other_liability']), '#opinion-report-tab'),
            $this->sub('declaration_family_members', 'Declaration and Family Members', collect($d['family_members'] ?? [])->isNotEmpty(), '#opinion-report-tab'),
            $this->sub('deponent_details', 'Deponent Details', $this->filled($d['deponent_date']), '#opinion-report-tab'),
        ]);
    }

    // ---- G. CMA ------------------------------------------------------------

    private function cma(Lead $lead): array
    {
        $d = CmaProfileData::for($lead);

        return $this->module('cma', 'CMA', 'cma-panel', [
            $this->sub('business_applicant_profile', 'Business / Applicant Profile',
                $this->filled($d['firm_name'], $d['proprietor_name'], $d['pan'], $d['address']), '#cma-tab'),
            $this->sub('profile_infrastructure', 'Business Profile & Infrastructure',
                $this->filled($d['business_experience'], $d['area_of_operation'], $d['manpower'], $d['space'], $d['connectivity']), '#cma-tab'),
            $this->sub('cma_generated', 'CMA Workbook Generated', $lead->hasGeneratedCma(), '#cma-tab',
                'Uses Lead::hasGeneratedCma() — the existing generation check.'),
            // "Existing/Projected Financial Information", "Working Capital Assessment" and "CMA
            // Calculations and Summary" are pure formulas in the workbook (fund/Production/INCOME/
            // trading/pnl/bs/FA/DSCR/Ratio/repayment/all ratio sheets) with no per-lead input field
            // anywhere in CmaProfileData — there is nothing a lead could leave incomplete there, so
            // they are intentionally left out rather than represented as always-pending or faked.
        ]);
    }

    // ---- H. DPR -------------------------------------------------------------

    private function dpr(Lead $lead): array
    {
        $d = DprData::for($lead);

        return $this->module('dpr', 'DPR', 'dpr-panel', [
            $this->sub('basic', 'Basic Information', $this->filled($d['firm_name'], $d['applicant_name'], $d['pan']), '#dpr-tab'),
            $this->sub('business', 'Business Profile', $this->filled($d['activity'], $d['business_profile'], $d['udyam']), '#dpr-tab'),
            $this->sub('loans', 'Current Loan Details', collect($d['current_loans'] ?? [])->isNotEmpty(), '#dpr-tab'),
            $this->sub('required', 'Required Loan Amount', $this->filled($d['term_required'], $d['cc_required']), '#dpr-tab'),
            $this->sub('cost', 'Project Cost', $this->filled($d['cost_land'], $d['cost_building'], $d['cost_machinery'], $d['cost_working_capital']), '#dpr-tab'),
            $this->sub('finance', 'Means of Finance', $this->filled($d['fin_promoter_capital'], $d['fin_term_loan'], $d['fin_cash_credit']), '#dpr-tab'),
            $this->sub('quotation', 'Quotation Summary for New Machinery', collect($d['machinery'] ?? [])->isNotEmpty(), '#dpr-tab'),
            $this->sub('collateral', 'Details of Collateral Property / CGTMSE', collect($d['collateral'] ?? [])->isNotEmpty(), '#dpr-tab'),
            $this->sub('financials', 'Financial Details in Brief', collect($d['financials'] ?? [])->flatten()->filter()->isNotEmpty(), '#dpr-tab'),
            $this->sub('dpr_generated', 'DPR Document Generated', $lead->hasGeneratedDpr(), '#dpr-tab',
                'Uses Lead::hasGeneratedDpr() — the existing generation check.'),
        ]);
    }

    // ---- I. Bank Details ----------------------------------------------------

    private function bankDetails(Lead $lead): array
    {
        $rows = collect($lead->bank_details ?? [])->filter(fn ($r) => $this->filled($r['bank_name'] ?? null, $r['account_number'] ?? null));

        return $this->module('bank_details', 'Bank Details', 'bank-panel', [
            // The template has one repeatable table (bank name / A/C number / type / IFSC) — there is
            // no separate "Branch Details" field distinct from it, so this is a single subsection.
            $this->sub('bank_account_details', 'Bank Account Details', $rows->isNotEmpty(), '#bank-tab'),
        ]);
    }

    // ---- J. Business Details -------------------------------------------------

    private function businessDetails(Lead $lead): array
    {
        return $this->module('business_details', 'Business Details', 'business-panel', [
            $this->sub('business_profile', 'Business Profile', $this->filled($lead->business_name, $lead->firm_name, $lead->constitution_of_business, $lead->introduction), '#business-tab'),
            $this->sub('business_address_location', 'Business Address and Location', $this->filled($lead->business_address, $lead->business_location, $lead->connectivity), '#business-tab'),
            $this->sub('business_activity', 'Business Activity', $this->filled($lead->business_activity, $lead->business_experience), '#business-tab'),
            $this->sub('registration_business_information', 'Registration and Business Information', $this->filled($lead->gst_number, $lead->gst_id), '#business-tab'),
            $this->sub('infrastructure_manpower', 'Infrastructure and Manpower', $this->filled($lead->area_of_premises, $lead->land_and_factory_building, $lead->no_of_manpower), '#business-tab'),
        ]);
    }

    // ---- K. Loan Details -----------------------------------------------------

    private function loanDetails(Lead $lead): array
    {
        $currentLoans = collect($lead->current_loans ?? [])->filter(fn ($r) => $this->filled($r['bank_name'] ?? null, $r['loan_amount'] ?? null));

        return $this->module('loan_details', 'Loan Details', 'loan-panel', [
            $this->sub('requested_loan_amount', 'Requested Loan Amount', $this->filled($lead->required_loan_amount), '#loan-tab'),
            $this->sub('cash_credit_requirement', 'Cash Credit (CC) Requirement', $this->filled($lead->cc_amount, $lead->cc_details), '#loan-tab'),
            $this->sub('term_loan_requirement', 'Term Loan Requirement', $this->filled($lead->term_loan_amount, $lead->term_loan_machinery_details), '#loan-tab'),
            $this->sub('existing_loan_facility_details', 'Existing Loan / Facility Details', $currentLoans->isNotEmpty(), '#loan-tab'),
            // No distinct "Loan Purpose" or "Repayment" field exists outside the CC/Term details and
            // each current loan's own tenure/EMI columns above — nothing separate to track here.
        ]);
    }
}
