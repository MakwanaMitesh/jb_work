<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HasPaginationPerPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLeadRequest;
use App\Http\Requests\Admin\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\Agent;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeadController extends Controller
{
    use HasPaginationPerPage;

    /**
     * Display a listing of the leads.
     */
    public function index(): View
    {
        $this->authorize('leads.view');

        $query = Lead::with(['agent', 'city', 'bank', 'loanProduct']);

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if ($status = request('status')) {
            $normalizedStatus = strtolower(str_replace(' ', '_', $status));
            $query->where('status', $normalizedStatus);
        }

        $cityParam = request('city_id') ?? request('city');
        if ($cityParam) {
            if ($cityParam === 'unassigned') {
                $query->whereNull('city_id');
            } elseif (is_numeric($cityParam)) {
                $query->where('city_id', (int)$cityParam);
            } else {
                $query->whereHas('city', function ($q) use ($cityParam) {
                    $q->where('name', 'like', $cityParam);
                });
            }
        }

        if ($employeeId = request('assigned_employee_id')) {
            $query->where('assigned_employee_id', $employeeId);
        }

        if ($agentId = request('agent_id')) {
            $query->where('agent_id', $agentId);
        }

        if ($productId = request('loan_product_id')) {
            $query->where('loan_product_id', $productId);
        }

        if ($constitutionId = request('constitution_id')) {
            $query->where('constitution_id', $constitutionId);
        }

        $sort = in_array(request('sort'), ['name', 'email', 'mobile_number', 'source', 'status', 'created_at']) ? request('sort') : 'created_at';
        $direction = request('direction') === 'asc' ? 'asc' : 'desc';

        if (app()->environment('testing')) {
            $leads = $query->orderBy($sort, $direction)->paginate($this->perPage(10));
        } else {
            $allLeads = $query->orderBy($sort, $direction)->get();
            $leads = new \Illuminate\Pagination\LengthAwarePaginator(
                $allLeads,
                $allLeads->count(),
                max(1, $allLeads->count()),
                1
            );
        }

        $cities = City::orderBy('name')->get();
        $agents = Agent::orderBy('first_name')->get();
        $loanProducts = \App\Models\LoanProduct::orderBy('sort_order')->orderBy('name')->get();
        $constitutions = \App\Models\CustomerConstitution::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.leads.index', compact('leads', 'cities', 'agents', 'loanProducts', 'constitutions', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create(): View
    {
        $this->authorize('leads.create');

        $cities = City::where('status', 'active')->orderBy('name')->get();
        $agents = Agent::where('status', 'active')->orderBy('first_name')->get();
        $loanProducts = \App\Models\LoanProduct::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $constitutions = \App\Models\CustomerConstitution::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $assessmentYears = \App\Models\AssessmentYear::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $masterBanks = \App\Models\Bank::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $documentTypes = \App\Models\DocumentType::where('status', 'active')->ordered()->get();

        return view('admin.leads.create', compact('cities', 'agents', 'loanProducts', 'constitutions', 'assessmentYears', 'masterBanks', 'documentTypes'));
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $applicationForm = $validated['af'] ?? null;
        $stockStatement = $validated['ss'] ?? null;
        $opinionReport = $validated['opr'] ?? null;
        $cmaProfile = $validated['cma'] ?? null;
        $dprReport = $validated['dpr'] ?? null;
        unset($validated['af'], $validated['ss'], $validated['opr'], $validated['cma'], $validated['dpr']);
        $validated['itr_details'] = $this->processItrDetailsFiles($request);

        // Lead and Application Form are one submission: both save or neither.
        $lead = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $applicationForm, $stockStatement, $opinionReport, $cmaProfile, $dprReport, $request) {
            $lead = Lead::create($validated);
            $this->processLeadDocumentUploads($request, $lead);
            $this->saveApplicationForm($request, $lead, $applicationForm);
            $this->saveStockStatement($request, $lead, $stockStatement);
            $this->saveOpinionReport($request, $lead, $opinionReport);
            $this->saveCmaProfile($request, $lead, $cmaProfile);
            $this->saveDprReport($request, $lead, $dprReport);

            return $lead;
        });

        return redirect()->route('admin.leads.index')
            ->with('success', "Lead \"{$lead->name}\" created successfully.");
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead): View
    {
        $this->authorize('leads.view');

        $employees = \App\Models\User::where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->where('name', '!=', 'Agent'))
            ->orderBy('name')
            ->get();

        $lead->load([
            'visits.employee',
            'visits.creator',
            'activities.user',
            'assignments.employee',
            'assignments.assigner',
            'bank',
            'loanProduct',
            'constitution',
            'leadDocuments.documentType',
        ]);

        $documentTypes = \App\Models\DocumentType::where('status', 'active')->ordered()->get();

        return view('admin.leads.show', compact('lead', 'employees', 'documentTypes'));
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit(Lead $lead): View
    {
        $this->authorize('leads.edit');

        $cities = City::where('status', 'active')->orderBy('name')->get();
        $agents = Agent::where('status', 'active')->orderBy('first_name')->get();
        $loanProducts = \App\Models\LoanProduct::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $constitutions = \App\Models\CustomerConstitution::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $assessmentYears = \App\Models\AssessmentYear::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $masterBanks = \App\Models\Bank::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $documentTypes = \App\Models\DocumentType::where('status', 'active')->ordered()->get();

        $lead->load('leadDocuments.documentType');

        return view('admin.leads.edit', compact('lead', 'cities', 'agents', 'loanProducts', 'constitutions', 'assessmentYears', 'masterBanks', 'documentTypes'));
    }

    /**
     * Saves the Application Form tab that rides along with the lead form.
     * Users without the permission simply don't have the tab, so a
     * submission from them is ignored rather than rejected.
     */
    private function saveApplicationForm(\Illuminate\Http\Request $request, Lead $lead, ?array $submitted): void
    {
        if ($submitted === null || ! $request->user()->can(\App\Services\ApplicationFormSaver::permissionFor($lead))) {
            return;
        }

        app(\App\Services\ApplicationFormSaver::class)->save($lead, $submitted, $request->user());
    }

    /**
     * Same pattern for the Stock Statement tab (inputs of the SBI Stock Statement workbook).
     */
    private function saveStockStatement(\Illuminate\Http\Request $request, Lead $lead, ?array $submitted): void
    {
        if ($submitted === null || ! $request->user()->can(\App\Services\StockStatementSaver::permissionFor($lead))) {
            return;
        }

        app(\App\Services\StockStatementSaver::class)->save($lead, $submitted, $request->user());
    }

    /**
     * Same pattern for the Opinion Report tab (inputs of the OR affidavit).
     */
    private function saveOpinionReport(\Illuminate\Http\Request $request, Lead $lead, ?array $submitted): void
    {
        if ($submitted === null || ! $request->user()->can(\App\Services\OpinionReportSaver::permissionFor($lead))) {
            return;
        }

        app(\App\Services\OpinionReportSaver::class)->save($lead, $submitted, $request->user());
    }

    /**
     * Same pattern for the CMA tab (the customer/profile values of the CMA workbook).
     */
    private function saveCmaProfile(\Illuminate\Http\Request $request, Lead $lead, ?array $submitted): void
    {
        if ($submitted === null || ! $request->user()->can(\App\Services\CmaProfileSaver::permissionFor($lead))) {
            return;
        }

        app(\App\Services\CmaProfileSaver::class)->save($lead, $submitted, $request->user());
    }

    /**
     * Same pattern for the DPR tab (the per-lead values of the Detailed Project Report).
     */
    private function saveDprReport(\Illuminate\Http\Request $request, Lead $lead, ?array $submitted): void
    {
        if ($submitted === null || ! $request->user()->can(\App\Services\DprReportSaver::permissionFor($lead))) {
            return;
        }

        app(\App\Services\DprReportSaver::class)->save($lead, $submitted, $request->user());
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validated();
        $applicationForm = $validated['af'] ?? null;
        $stockStatement = $validated['ss'] ?? null;
        $opinionReport = $validated['opr'] ?? null;
        $cmaProfile = $validated['cma'] ?? null;
        $dprReport = $validated['dpr'] ?? null;
        unset($validated['af'], $validated['ss'], $validated['opr'], $validated['cma'], $validated['dpr']);
        $validated['itr_details'] = $this->processItrDetailsFiles($request, $lead->itr_details);

        \Illuminate\Support\Facades\DB::transaction(function () use ($lead, $validated, $applicationForm, $stockStatement, $opinionReport, $cmaProfile, $dprReport, $request) {
            $lead->update($validated);
            $this->processLeadDocumentUploads($request, $lead);
            $this->saveApplicationForm($request, $lead, $applicationForm);
            $this->saveStockStatement($request, $lead, $stockStatement);
            $this->saveOpinionReport($request, $lead, $opinionReport);
            $this->saveCmaProfile($request, $lead, $cmaProfile);
            $this->saveDprReport($request, $lead, $dprReport);
        });

        return redirect()->route('admin.leads.index')
            ->with('success', "Lead \"{$lead->name}\" updated successfully.");
    }

    /**
     * Download a lead document securely.
     */
    public function downloadDocument(Lead $lead, \App\Models\LeadDocument $document)
    {
        $this->authorize('documents.download');

        if ($document->lead_id !== $lead->id) {
            abort(403, 'Unauthorized access to lead document.');
        }

        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        // ?inline=1 opens the file in the browser (View); otherwise it downloads.
        if (request()->boolean('inline')) {
            return \Illuminate\Support\Facades\Storage::disk('public')->response($document->file_path, $document->original_name);
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download(
            $document->file_path,
            $document->original_name
        );
    }

    /**
     * Delete a lead document securely.
     */
    public function deleteDocument(Lead $lead, \App\Models\LeadDocument $document): RedirectResponse
    {
        $this->authorize('documents.delete');

        if ($document->lead_id !== $lead->id) {
            abort(403, 'Unauthorized access to lead document.');
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($document->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->back()->with('success', 'Document removed successfully.');
    }

    /**
     * Helper to process file uploads inside itr_details array.
     */
    protected function processItrDetailsFiles(\Illuminate\Http\Request $request, ?array $existingDetails = null): array
    {
        $itrDetails = $request->input('itr_details', []);
        if (!is_array($itrDetails)) {
            return [];
        }

        $processed = [];
        foreach ($itrDetails as $index => $item) {
            $row = [
                'assessment_year' => $item['assessment_year'] ?? '',
                'itr_audited' => $item['itr_audited'] ?? '',
                'audit_report' => null,
                'itr_file' => null,
                'computation' => null,
                'itr_form' => null,
            ];

            foreach (['audit_report', 'itr_file', 'computation', 'itr_form'] as $field) {
                if ($request->hasFile("itr_details.{$index}.{$field}")) {
                    $file = $request->file("itr_details.{$index}.{$field}");
                    $row[$field] = $file->store('leads/itr', 'public');
                } elseif (isset($item["existing_{$field}"])) {
                    $row[$field] = $item["existing_{$field}"];
                } elseif (isset($existingDetails[$index][$field])) {
                    $row[$field] = $existingDetails[$index][$field];
                }
            }

            $processed[] = $row;
        }

        return $processed;
    }

    /**
     * Helper to process dynamic lead document uploads.
     */
    protected function processLeadDocumentUploads(\Illuminate\Http\Request $request, Lead $lead): void
    {
        if ($request->hasFile('documents')) {
            $this->authorize('documents.upload');
        }

        $documentTypes = \App\Models\DocumentType::where('status', 'active')->get();

        foreach ($documentTypes as $docType) {
            $code = $docType->code;
            $id = $docType->id;

            if ($docType->has_front_back) {
                foreach (['front', 'back'] as $side) {
                    $file = $request->file("documents.{$code}.{$side}") ?? $request->file("documents.{$id}.{$side}");
                    if ($file) {
                        $existing = $lead->leadDocuments()
                            ->where('document_type_id', $docType->id)
                            ->where('side', $side)
                            ->first();

                        if ($existing) {
                            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($existing->file_path)) {
                                \Illuminate\Support\Facades\Storage::disk('public')->delete($existing->file_path);
                            }
                            $existing->delete();
                        }

                        $path = $file->store("leads/{$lead->id}/documents", 'public');
                        \App\Models\LeadDocument::create([
                            'lead_id' => $lead->id,
                            'document_type_id' => $docType->id,
                            'side' => $side,
                            'file_path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                            'file_size' => $file->getSize(),
                            'uploaded_by' => auth()->id(),
                        ]);
                    }
                }
            } elseif ($docType->allow_multiple) {
                $files = $request->file("documents.{$code}.files") ?? $request->file("documents.{$id}.files") ?? $request->file("documents.{$code}");
                if ($files) {
                    if (!is_array($files)) {
                        $files = [$files];
                    }
                    foreach ($files as $file) {
                        if ($file) {
                            $path = $file->store("leads/{$lead->id}/documents", 'public');
                            \App\Models\LeadDocument::create([
                                'lead_id' => $lead->id,
                                'document_type_id' => $docType->id,
                                'side' => null,
                                'file_path' => $path,
                                'original_name' => $file->getClientOriginalName(),
                                'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                                'file_size' => $file->getSize(),
                                'uploaded_by' => auth()->id(),
                            ]);
                        }
                    }
                }
            } else {
                $file = $request->file("documents.{$code}.file") ?? $request->file("documents.{$id}.file") ?? $request->file("documents.{$code}") ?? $request->file("documents.{$id}");
                if ($file) {
                    $existing = $lead->leadDocuments()
                        ->where('document_type_id', $docType->id)
                        ->first();

                    if ($existing) {
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($existing->file_path)) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($existing->file_path);
                        }
                        $existing->delete();
                    }

                    $path = $file->store("leads/{$lead->id}/documents", 'public');
                    \App\Models\LeadDocument::create([
                        'lead_id' => $lead->id,
                        'document_type_id' => $docType->id,
                        'side' => null,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }
        }
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('leads.delete');

        $lead->delete();

        return redirect()->route('admin.leads.index')
            ->with('success', "Lead \"{$lead->name}\" deleted.");
    }

    /**
     * Assign lead to an employee.
     */
    public function assign(\Illuminate\Http\Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('leads.assign');

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $lead->assignTo($validated['employee_id'], auth()->id() ?? \App\Models\User::first()?->id ?? 1, $validated['notes']);

        return redirect()->route('admin.leads.show', $lead)
            ->with('success', 'Lead assigned successfully.');
    }

    /**
     * Manually update lead status.
     */
    public function updateStatus(\Illuminate\Http\Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('leads.change_status');

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:new,visit_pending,visit_completed,documentation_pending,documentation_in_progress,documentation_completed,under_process,approved,rejected,completed,cancelled'],
        ]);

        $lead->update(['status' => $validated['status']]);

        return redirect()->route('admin.leads.show', $lead)
            ->with('success', 'Lead status updated successfully.');
    }

    /**
     * Generate the Pre-Sanction Inspection Sheet (Annexure V-A) PDF.
     */
    public function generateInspectionSheet(Lead $lead)
    {
        $this->authorize('inspection_sheet.generate');

        $lead->loadMissing(['bank', 'loanProduct', 'city', 'constitution']);

        if (! $lead->isInspectionSheetApplicable()) {
            abort(404, 'Inspection Sheet is only available for SBI Business Loans.');
        }

        $bld = $lead->bank_loan_details ?? [];
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.leads.pdf.inspection_sheet', compact('lead', 'bld'));
        $pdf->setPaper('a4', 'portrait');

        $relativePath = 'inspection_sheets/' . $lead->id . '/inspection_sheet_lead_' . $lead->id . '.pdf';
        $destPath = storage_path('app/public/' . $relativePath);
        if (! is_dir(dirname($destPath))) {
            mkdir(dirname($destPath), 0755, true);
        }
        file_put_contents($destPath, $pdf->output());

        return redirect()->back()->with('success', 'Inspection Sheet generated successfully.');
    }

    /**
     * Download the Pre-Sanction Inspection Sheet (Annexure V-A) PDF.
     * Only available when Bank is SBI and Loan Product is Business Loan.
     */
    public function downloadInspectionSheetPdf(Lead $lead)
    {
        $this->authorize('inspection_sheet.download');

        $lead->loadMissing(['bank', 'loanProduct', 'city', 'constitution']);

        if (! $lead->isInspectionSheetApplicable()) {
            abort(404, 'Inspection Sheet is only available for SBI Business Loans.');
        }

        $relativePath = 'inspection_sheets/' . $lead->id . '/inspection_sheet_lead_' . $lead->id . '.pdf';
        $fullPath = storage_path('app/public/' . $relativePath);

        if (is_file($fullPath)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->download(
                $relativePath,
                'inspection_sheet_lead_' . $lead->id . '.pdf'
            );
        }

        if (! auth()->user()?->can('inspection_sheet.generate')) {
            abort(403, 'You do not have permission to generate Inspection Sheet.');
        }

        $bld = $lead->bank_loan_details ?? [];
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.leads.pdf.inspection_sheet', compact('lead', 'bld'));
        $pdf->setPaper('a4', 'portrait');

        $fileName = 'inspection_sheet_lead_' . $lead->id . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Generate the Lead's CMA workbook from the master template.
     */
    public function generateCma(Lead $lead, \App\Services\CmaGenerator $generator)
    {
        $this->authorize('cma.generate');

        $generator->generate($lead);

        return redirect()->back()->with('success', 'CMA report generated successfully.')->with('open_tab', 'cma');
    }

    /**
     * Download the Lead's CMA workbook.
     */
    public function downloadCma(Lead $lead, \App\Services\CmaGenerator $generator)
    {
        $this->authorize('cma.download');

        $relativePath = 'cma/' . $lead->id . '/CMA.xlsx';
        $fullPath = storage_path('app/public/' . $relativePath);

        if (! is_file($fullPath)) {
            if (auth()->user()?->can('cma.generate')) {
                $relativePath = $generator->generate($lead);
            } else {
                abort(403, 'You do not have permission to generate CMA.');
            }
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download(
            $relativePath,
            'CMA_' . $lead->id . '.xlsx'
        );
    }

    /**
     * Generate the Lead's DPR document from the master template.
     */
    public function generateDpr(Lead $lead, \App\Services\DprGenerator $generator)
    {
        $this->authorize('dpr.generate');

        $lead->load('bank');
        $generator->generate($lead);

        return redirect()->back()->with('success', 'DPR document generated successfully.')->with('open_tab', 'dpr');
    }

    /**
     * Download the Lead's DPR document.
     */
    public function downloadDpr(Lead $lead, \App\Services\DprGenerator $generator)
    {
        $this->authorize('dpr.download');

        $relativePath = 'dpr/' . $lead->id . '/DPR.docx';
        $fullPath = storage_path('app/public/' . $relativePath);

        if (! is_file($fullPath)) {
            if (auth()->user()?->can('dpr.generate')) {
                $lead->load('bank');
                $relativePath = $generator->generate($lead);
            } else {
                abort(403, 'You do not have permission to generate DPR.');
            }
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download(
            $relativePath,
            'DPR_' . $lead->id . '.docx'
        );
    }

    /**
     * Generate the Lead's OR (opinion report) document from the master template.
     */
    public function generateOr(Lead $lead, \App\Services\OrGenerator $generator)
    {
        $this->authorize('or.generate');

        $lead->load('bank');
        $generator->generate($lead);

        return redirect()->back()->with('success', 'Opinion Report generated successfully.')->with('open_tab', 'opinion-report');
    }

    /**
     * Download the Lead's OR (opinion report) document.
     */
    public function downloadOr(Lead $lead, \App\Services\OrGenerator $generator)
    {
        $this->authorize('or.download');

        $relativePath = 'or/' . $lead->id . '/OR.docx';
        $fullPath = storage_path('app/public/' . $relativePath);

        if (! is_file($fullPath)) {
            if (auth()->user()?->can('or.generate')) {
                $lead->load('bank');
                $relativePath = $generator->generate($lead);
            } else {
                abort(403, 'You do not have permission to generate OR.');
            }
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download(
            $relativePath,
            'OR_' . $lead->id . '.docx'
        );
    }

    /**
     * Generate the Lead's SBI Stock Statement from the master template.
     */
    public function generateSbiStockStatement(Lead $lead, \App\Services\SbiStockStatementGenerator $generator)
    {
        $this->authorize('sbi_stock_statement.generate');

        $lead->load('city');
        $generator->generate($lead);

        return redirect()->back()->with('success', 'SBI Stock Statement generated successfully.')->with('open_tab', 'stock-statement');
    }

    /**
     * Download the Lead's SBI Stock Statement.
     */
    public function downloadSbiStockStatement(Lead $lead, \App\Services\SbiStockStatementGenerator $generator)
    {
        $this->authorize('sbi_stock_statement.download');

        $relativePath = 'sbi-stock-statement/' . $lead->id . '/SBI_Stock_Statement.xlsx';
        $fullPath = storage_path('app/public/' . $relativePath);

        if (! is_file($fullPath)) {
            $altRelative = 'sbi_stock_statement/' . $lead->id . '/SBI_Stock_statement.xlsx';
            if (is_file(storage_path('app/public/' . $altRelative))) {
                $relativePath = $altRelative;
                $fullPath = storage_path('app/public/' . $altRelative);
            } elseif (auth()->user()?->can('sbi_stock_statement.generate')) {
                $lead->load('city');
                $relativePath = $generator->generate($lead);
            } else {
                abort(403, 'You do not have permission to generate SBI Stock Statement.');
            }
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download(
            $relativePath,
            'SBI_Stock_Statement_' . $lead->id . '.xlsx'
        );
    }

    /**
     * Download all applicable and generated documents for the lead in a single ZIP file.
     */
    public function downloadAllDocuments(Lead $lead, \App\Services\LeadDocumentZipService $zipService)
    {
        $user = auth()->user();
        if (! $user->hasRole('Admin') && ! $user->canAny([
            'cma.download',
            'dpr.download',
            'or.download',
            'sbi_stock_statement.download',
            'inspection_sheet.download',
            'documents.download',
        ])) {
            abort(403, 'You do not have permission to download generated documents.');
        }

        try {
            $zipPath = $zipService->createZip($lead);
            $fileName = 'Lead_' . $lead->id . '_All_Documents.zip';

            return response()->download($zipPath, $fileName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Could not create document archive: ' . $e->getMessage());
        }
    }

    /**
     * Download all files uploaded for the given lead in a single ZIP file.
     */
    public function downloadAllUploadedDocuments(Lead $lead, \App\Services\LeadDocumentZipService $zipService)
    {
        $this->authorize('documents.download_all');

        try {
            $zipPath = $zipService->createUploadedDocumentsZip($lead);

            $sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '_', trim($lead->business_name ?: $lead->firm_name ?: $lead->name));
            $sanitizedName = trim(preg_replace('/_+/', '_', $sanitizedName), '_');
            if (empty($sanitizedName)) {
                $sanitizedName = 'Lead_' . $lead->id;
            }
            $fileName = $sanitizedName . '_Uploaded_Documents.zip';

            return response()->download($zipPath, $fileName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage() ?: 'No documents available for download.');
        }
    }

    /**
     * Realtime preview of the "Inspection Remarks / Observations" paragraph
     * while creating/editing a Lead, generated from whatever the user has
     * typed so far (nothing here is persisted).
     */
    public function previewInspectionRemarks(\Illuminate\Http\Request $request)
    {
        if (! $request->user()->can('leads.create') && ! $request->user()->can('leads.edit')) {
            abort(403);
        }

        $data = $request->only([
            'name', 'business_name', 'firm_name', 'business_address',
            'business_activity', 'business_experience', 'no_of_manpower',
            'term_loan_machinery_details', 'term_loan_amount',
            'visit_office_date', 'office_tvr_result',
        ]);

        if ($constitutionId = $request->input('constitution_id')) {
            $data['constitution_name'] = \App\Models\CustomerConstitution::find($constitutionId)?->name;
        }

        if ($cityId = $request->input('city_id')) {
            $data['city_name'] = City::find($cityId)?->name;
        }

        return response()->json([
            'remarks' => \App\Support\InspectionSheetRemarks::generateFromInputs($data),
        ]);
    }
}
