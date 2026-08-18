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
            'delete' => 'Delete documents',
        ],
        'leads' => [
            'view' => 'View leads',
            'create' => 'Create leads',
            'edit' => 'Edit leads',
            'delete' => 'Delete leads',
            'assign' => 'Assign leads',
            'change_status' => 'Change lead status',
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
