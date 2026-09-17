<?php

use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserPermissionController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\VisitController;
use App\Http\Controllers\Admin\LoanProductController;
use App\Http\Controllers\Admin\CustomerConstitutionController;
use App\Http\Controllers\Admin\AssessmentYearController;
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\DocumentTypeController;
use Illuminate\Support\Facades\Route;

// Role & Permission management, and per-user direct permissions.
// Authorization is enforced per-action in the controllers via Policies
// (App\Policies\RolePolicy / App\Policies\UserPolicy) — Admin only.
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('roles', RoleController::class)
        ->except(['show', 'destroy']);

    Route::patch('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])
        ->name('roles.toggle-status');

    Route::get('users', [UserPermissionController::class, 'index'])->name('users.index');
    Route::get('users/{user}/permissions', [UserPermissionController::class, 'edit'])->name('users.permissions.edit');
    Route::put('users/{user}/permissions', [UserPermissionController::class, 'update'])->name('users.permissions.update');

    // Employee Management. Every action is additionally gated inside
    // EmployeeController via $this->authorize('employees.*') — the granular
    // permissions created in Step 3 — so access works for any user holding
    // that permission, not just a hardcoded "Admin" role check.
    Route::resource('employees', EmployeeController::class)
        ->except(['show'])
        ->parameters(['employees' => 'employee']);
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::patch('employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])
        ->name('employees.toggle-status');

    // Agent Management. Every action is additionally gated inside
    // AgentController via $this->authorize('agent.*')
    Route::resource('agents', AgentController::class)
        ->except(['show'])
        ->parameters(['agents' => 'agent']);
    Route::get('agents/{agent}', [AgentController::class, 'show'])->name('agents.show');
    Route::patch('agents/{agent}/toggle-status', [AgentController::class, 'toggleStatus'])
        ->name('agents.toggle-status');

    // City Management. Gated by city.* permissions inside CityController.
    // City Management. Gated by city.* permissions inside CityController.
    Route::resource('cities', CityController::class)
        ->except(['show'])
        ->parameters(['cities' => 'city']);
    Route::patch('cities/{city}/toggle-status', [CityController::class, 'toggleStatus'])
        ->name('cities.toggle-status');

    // Lead Management
    Route::post('leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
    Route::post('leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.status');

    Route::resource('leads', LeadController::class)
        ->parameters(['leads' => 'lead']);

    Route::get('leads/{lead}/documents/{document}/download', [LeadController::class, 'downloadDocument'])->name('leads.documents.download');
    Route::delete('leads/{lead}/documents/{document}', [LeadController::class, 'deleteDocument'])->name('leads.documents.destroy');
    Route::get('leads/{lead}/inspection-sheet/pdf', [LeadController::class, 'downloadInspectionSheetPdf'])->name('leads.inspection-sheet.pdf');

    Route::post('leads/{lead}/visits', [VisitController::class, 'store'])->name('leads.visits.store');
    Route::put('leads/{lead}/visits/{visit}', [VisitController::class, 'update'])->name('leads.visits.update');

    // Loan Products Management
    Route::patch('loan-products/{loan_product}/toggle-status', [LoanProductController::class, 'toggleStatus'])->name('loan-products.toggle-status');
    Route::get('loan-products/{loan_product}/config', [LoanProductController::class, 'editConfig'])->name('loan-products.config.edit');
    Route::post('loan-products/{loan_product}/config', [LoanProductController::class, 'updateConfig'])->name('loan-products.config.update');
    Route::resource('loan-products', LoanProductController::class)->parameters(['loan-products' => 'loan_product']);

    // Customer Constitutions Management
    Route::patch('constitutions/{constitution}/toggle-status', [CustomerConstitutionController::class, 'toggleStatus'])->name('constitutions.toggle-status');
    Route::resource('constitutions', CustomerConstitutionController::class);

    // Assessment Years Management
    Route::patch('assessment-years/{assessment_year}/toggle-status', [AssessmentYearController::class, 'toggleStatus'])->name('assessment-years.toggle-status');
    Route::resource('assessment-years', AssessmentYearController::class)->parameters(['assessment-years' => 'assessment_year']);

    // Banks Management
    Route::patch('banks/{bank}/toggle-status', [BankController::class, 'toggleStatus'])->name('banks.toggle-status');
    Route::resource('banks', BankController::class);

    // Document Master Management
    Route::patch('document-types/{document_type}/toggle-status', [DocumentTypeController::class, 'toggleStatus'])->name('document-types.toggle-status');
    Route::resource('document-types', DocumentTypeController::class)->parameters(['document-types' => 'document_type']);
});
