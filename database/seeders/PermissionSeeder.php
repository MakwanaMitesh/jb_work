<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Module => granular actions available for that module.
     *
     * Only actions that make sense for a given module are listed, so the
     * permission matrix stays meaningful rather than a blind cross-product.
     *
     * @var array<string, array<string, string>>
     */
    public const MODULES = [
        'dashboard' => [
            'view' => 'View dashboard',
        ],
        'pendency' => [
            'view' => 'View Pendency dashboard',
        ],
        'employees' => [
            'view' => 'View employees',
            'create' => 'Create employees',
            'edit' => 'Edit employees',
            'delete' => 'Delete employees',
            'activate' => 'Activate or deactivate employees',
            'assign_role' => 'Assign role to employees',
        ],
        'agents' => [
            'view' => 'View agents',
            'create' => 'Create agents',
            'edit' => 'Edit agents',
            'delete' => 'Delete agents',
            'assign' => 'Assign agents',
        ],
        'agent' => [
            'view' => 'View agents',
            'create' => 'Create agents',
            'edit' => 'Edit agents',
            'delete' => 'Delete agents',
            'activate' => 'Activate or deactivate agents',
        ],
        'city' => [
            'view' => 'View cities',
            'create' => 'Create cities',
            'edit' => 'Edit cities',
            'delete' => 'Delete cities',
            'activate' => 'Activate or deactivate cities',
        ],
        'tasks' => [
            'view' => 'View tasks',
            'create' => 'Create tasks',
            'edit' => 'Edit tasks',
            'delete' => 'Delete tasks',
            'assign' => 'Assign tasks',
            'change_status' => 'Change task status',
            'approve' => 'Approve tasks',
        ],
        'eod' => [
            'view' => 'View EOD reports',
            'create' => 'Create EOD reports',
            'edit' => 'Edit EOD reports',
            'delete' => 'Delete EOD reports',
            'approve' => 'Approve EOD reports',
        ],
        'documents' => [
            'view' => 'View documents',
            'upload' => 'Upload documents',
            'download' => 'Download documents',
            'download_all' => 'Download all uploaded documents',
            'delete' => 'Delete documents',
        ],
        // The five Lead tabs that previously had no permission of their own — every field on them
        // saved as part of the Lead itself, visible to anyone who could open the Lead form. See
        // LeadModuleAccess, which both these actions and the "view" ones below are enforced through.
        'details_sheet' => [
            'view' => 'View Lead Details Sheet tab',
            'update' => 'Edit Lead Details Sheet fields',
        ],
        'bank_loan' => [
            'view' => 'View Lead Bank Loan tab',
            'update' => 'Edit Lead Bank Loan fields',
        ],
        'bank_details' => [
            'view' => 'View Lead Bank Details tab',
            'update' => 'Edit Lead Bank Details fields',
        ],
        'business_details' => [
            'view' => 'View Lead Business Details tab',
            'update' => 'Edit Lead Business Details fields',
        ],
        'loan_details' => [
            'view' => 'View Lead Loan Details tab',
            'update' => 'Edit Lead Loan Details fields',
        ],
        'cma' => [
            'view' => 'View CMA profile form',
            'create' => 'Create CMA profile form',
            'update' => 'Update CMA profile form',
            'generate' => 'Generate CMA report',
            'download' => 'Download CMA report',
        ],
        'dpr' => [
            'view' => 'View DPR form',
            'create' => 'Create DPR form',
            'update' => 'Update DPR form',
            'generate' => 'Generate DPR document',
            'download' => 'Download DPR document',
        ],
        'or' => [
            'view' => 'View OR (opinion report) form',
            'create' => 'Create OR (opinion report) form',
            'update' => 'Update OR (opinion report) form',
            'generate' => 'Generate OR (opinion report) document',
            'download' => 'Download OR (opinion report) document',
        ],
        'sbi_stock_statement' => [
            'view' => 'View SBI Stock Statement form',
            'create' => 'Create SBI Stock Statement form',
            'update' => 'Update SBI Stock Statement form',
            'generate' => 'Generate SBI Stock Statement',
            'download' => 'Download SBI Stock Statement',
        ],
        'application_form' => [
            'view' => 'View application form',
            'create' => 'Create application form',
            'update' => 'Update application form',
            'generate' => 'Generate application form workbook',
            'download' => 'Download application form workbook',
        ],
        'inspection_sheet' => [
            'generate' => 'Generate inspection sheet report',
            'download' => 'Download inspection sheet report',
        ],
        'document_types' => [
            'view' => 'View document master',
            'create' => 'Create document master',
            'edit' => 'Edit document master',
            'delete' => 'Delete document master',
            'activate' => 'Activate or deactivate document master',
        ],
        'leads' => [
            'view' => 'View leads',
            'view_own' => 'View only own assigned leads',
            'view_all' => 'View all leads',
            'create' => 'Create leads',
            'edit' => 'Edit leads',
            'delete' => 'Delete leads',
            'assign' => 'Assign leads',
            'change_status' => 'Change lead status',
        ],
        'rough_leads' => [
            'view' => 'View rough leads',
            'create' => 'Create rough leads',
            'edit' => 'Edit rough leads',
            'delete' => 'Delete rough leads',
            'change_status' => 'Change rough lead status',
            'convert' => 'Convert rough lead to main lead',
        ],
        'visits' => [
            'view' => 'View visits',
            'create' => 'Create visits',
            'edit' => 'Edit visits',
            'delete' => 'Delete visits',
        ],
        'loan_products' => [
            'view' => 'View loan products',
            'create' => 'Create loan products',
            'edit' => 'Edit loan products',
            'delete' => 'Delete loan products',
        ],
        'constitutions' => [
            'view' => 'View customer constitutions',
            'create' => 'Create customer constitutions',
            'edit' => 'Edit customer constitutions',
            'delete' => 'Delete customer constitutions',
        ],
        'loan_product_config' => [
            'view' => 'View loan product configurations',
            'edit' => 'Edit loan product configurations',
        ],
        'reports' => [
            'view' => 'View reports',
            'export' => 'Export reports',
        ],
        'case_processing' => [
            'view' => 'View case processing',
            'create' => 'Create cases',
            'edit' => 'Edit cases',
            'delete' => 'Delete cases',
            'change_status' => 'Change case status',
            'approve' => 'Approve cases',
        ],
        'status_management' => [
            'view' => 'View statuses',
            'create' => 'Create statuses',
            'edit' => 'Edit statuses',
            'delete' => 'Delete statuses',
        ],
        'contact_enquiry' => [
            'view' => 'View contact enquiries',
            'update' => 'Update contact enquiries',
            'delete' => 'Delete contact enquiries',
        ],
    ];

    /**
     * Module => its subsections, where the module's own UI already has a fixed, named set of them
     * (see each module's data class / view for the source). Only "view" exists at this level —
     * subsections are about which parts of an already-visible, already-authorized module a role
     * can see, not a separate action matrix. Keyed exactly like each module's own code: DPR's keys
     * match DprData's sub-tabs (dpr-s-{key}), OR's match OpinionReportData's fields, and so on — see
     * App\Support\LeadModuleAccess, the single place this list is read from for enforcement.
     *
     * Two modules are deliberately absent: Documents has no fixed subsection list (its "sections"
     * are whichever Document Master types exist, which is admin-configured data, not code), and
     * Application Form's own sub-tabs are literally labelled "Table 1/2/3" in its current UI — kept
     * as-is rather than invented into something more descriptive.
     *
     * @var array<string, array<string, string>>
     */
    public const LEAD_MODULE_SUBSECTIONS = [
        'details_sheet' => [
            'basic_details' => 'Basic Details',
            'itr_details' => 'ITR Details',
            'bank_details' => 'Bank Details',
        ],
        'bank_loan' => [
            'visit_applicant_details' => 'Visit & Applicant Details',
            'branch_signoff_details' => 'Branch & Sign-off Details',
        ],
        'application_form' => [
            't1' => 'Table 1',
            't2' => 'Table 2',
            't3' => 'Table 3',
        ],
        'bank_details' => [
            'bank_account_details' => 'Bank Account Details',
        ],
        'business_details' => [
            'business_profile' => 'Business Profile',
            'business_address_location' => 'Business Address and Location',
            'business_activity' => 'Business Activity',
            'registration_business_information' => 'Registration and Business Information',
            'infrastructure_manpower' => 'Infrastructure and Manpower',
        ],
        'loan_details' => [
            'requested_loan_amount' => 'Requested Loan Amount',
            'cash_credit_requirement' => 'Cash Credit (CC) Requirement',
            'term_loan_requirement' => 'Term Loan Requirement',
            'existing_loan_facility_details' => 'Existing Loan / Facility Details',
        ],
        'sbi_stock_statement' => [
            'inventories' => 'INVENTORIES',
            'book_debts' => 'BOOK DEBTS',
            'other_information' => 'OTHER INFORMATION',
        ],
        'or' => [
            'affidavit_personal_details' => 'Affidavit / Personal Details',
            'bank_borrower_details' => 'Bank and Borrower Details',
            'properties_india' => 'Immovable Properties in India',
            'properties_outside_india' => 'Immovable Properties Outside India',
            'liquid_assets' => 'Liquid Assets',
            'government_securities' => 'Government Securities',
            'shares' => 'Shares',
            'life_insurance_policies' => 'Life Insurance Policies',
            'jewellery' => 'Jewellery',
            'associate_investments' => 'Investments in Associate Firms',
            'intangible_assets' => 'Intangible Assets',
            'monthly_income' => 'Monthly Income',
            'liabilities' => 'Liabilities',
            'other_assets_liabilities' => 'Other Assets and Liabilities',
            'declaration_family_members' => 'Declaration and Family Members',
            'deponent_details' => 'Deponent Details',
        ],
        'cma' => [
            'business_applicant_profile' => 'Business / Applicant Profile',
            'profile_infrastructure' => 'Business Profile & Infrastructure',
        ],
        'dpr' => [
            'basic' => 'Basic Information',
            'business' => 'Business Profile',
            'loans' => 'Current Loan Details',
            'required' => 'Required Loan Amount',
            'cost' => 'Project Cost',
            'finance' => 'Means of Finance',
            'quotation' => 'Quotation Summary for New Machinery',
            'collateral' => 'Details of Collateral Property / CGTMSE',
            'financials' => 'Financial Details in Brief',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::MODULES as $module => $actions) {
            foreach ($actions as $action => $description) {
                Permission::firstOrCreate(
                    ['name' => "{$module}.{$action}", 'guard_name' => 'web'],
                    ['module' => $module, 'description' => $description],
                );
            }
        }

        foreach (self::LEAD_MODULE_SUBSECTIONS as $module => $subsections) {
            foreach ($subsections as $key => $label) {
                Permission::firstOrCreate(
                    ['name' => "{$module}.{$key}.view", 'guard_name' => 'web'],
                    ['module' => $module, 'description' => "View {$label}"],
                );
            }
        }
    }
}
