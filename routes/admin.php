<?php

use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\ApplicationFormController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserPermissionController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\RoughLeadController;
use App\Http\Controllers\Admin\VisitController;
use App\Http\Controllers\Admin\LoanProductController;
use App\Http\Controllers\Admin\CustomerConstitutionController;
use App\Http\Controllers\Admin\AssessmentYearController;
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\ContactEnquiryController;
use App\Http\Controllers\Admin\DocumentTypeController;
use App\Http\Controllers\Admin\PendencyController;
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
    Route::post('leads/inspection-remarks/preview', [LeadController::class, 'previewInspectionRemarks'])->name('leads.inspection-remarks.preview');

    Route::resource('leads', LeadController::class)
        ->parameters(['leads' => 'lead']);

    // Pendency: a read-only, computed-on-request view over existing Lead data (see
    // PendencyService) — no new tables, no data entered here.
    Route::get('pendency', [PendencyController::class, 'index'])->name('pendency.index');
    Route::get('pendency/{lead}', [PendencyController::class, 'show'])->name('pendency.show');

    Route::get('leads/{lead}/documents/{document}/download', [LeadController::class, 'downloadDocument'])->name('leads.documents.download');
    Route::delete('leads/{lead}/documents/{document}', [LeadController::class, 'deleteDocument'])->name('leads.documents.destroy');
    Route::post('leads/{lead}/inspection-sheet/generate', [LeadController::class, 'generateInspectionSheet'])->name('leads.inspection-sheet.generate');
    Route::get('leads/{lead}/inspection-sheet/pdf', [LeadController::class, 'downloadInspectionSheetPdf'])->name('leads.inspection-sheet.pdf');
    Route::post('leads/{lead}/cma/generate', [LeadController::class, 'generateCma'])->name('leads.cma.generate');
    Route::get('leads/{lead}/cma', [LeadController::class, 'downloadCma'])->name('leads.cma.download');
    Route::post('leads/{lead}/dpr/generate', [LeadController::class, 'generateDpr'])->name('leads.dpr.generate');
    Route::get('leads/{lead}/dpr', [LeadController::class, 'downloadDpr'])->name('leads.dpr.download');
    Route::post('leads/{lead}/or/generate', [LeadController::class, 'generateOr'])->name('leads.or.generate');
    Route::get('leads/{lead}/or', [LeadController::class, 'downloadOr'])->name('leads.or.download');
    Route::post('leads/{lead}/sbi-stock-statement/generate', [LeadController::class, 'generateSbiStockStatement'])->name('leads.sbi-stock-statement.generate');
    Route::get('leads/{lead}/sbi-stock-statement', [LeadController::class, 'downloadSbiStockStatement'])->name('leads.sbi-stock-statement.download');
    Route::get('leads/{lead}/application-form', [ApplicationFormController::class, 'show'])->name('leads.application-form.show');
    Route::post('leads/{lead}/application-form/generate', [ApplicationFormController::class, 'generate'])->name('leads.application-form.generate');
    Route::get('leads/{lead}/application-form/download', [ApplicationFormController::class, 'download'])->name('leads.application-form.download');
    Route::get('leads/{lead}/download-all', [LeadController::class, 'downloadAllDocuments'])->name('leads.download-all');
    Route::get('leads/{lead}/uploaded-documents/download-all', [LeadController::class, 'downloadAllUploadedDocuments'])->name('leads.uploaded-documents.download-all');

    Route::post('leads/{lead}/visits', [VisitController::class, 'store'])->name('leads.visits.store');
    Route::put('leads/{lead}/visits/{visit}', [VisitController::class, 'update'])->name('leads.visits.update');

    // Rough Leads — quick initial enquiry records, convertible into Main Leads.
    Route::post('rough-leads/{rough_lead}/convert', [RoughLeadController::class, 'convert'])->name('rough-leads.convert');
    Route::post('rough-leads/{rough_lead}/status', [RoughLeadController::class, 'updateStatus'])->name('rough-leads.status');
    Route::resource('rough-leads', RoughLeadController::class)
        ->parameters(['rough-leads' => 'rough_lead']);

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

    // Contact Enquiries Management
    Route::post('contact-enquiries/{contact_enquiry}/status', [ContactEnquiryController::class, 'updateStatus'])->name('contact-enquiries.status');
    Route::resource('contact-enquiries', ContactEnquiryController::class)
        ->except(['create', 'store', 'edit'])
        ->parameters(['contact-enquiries' => 'contact_enquiry']);
});
