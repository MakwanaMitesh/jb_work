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
    }
}
