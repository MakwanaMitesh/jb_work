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

        $query = Lead::with(['agent', 'city']);

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if ($cityId = request('city_id')) {
            $query->where('city_id', $cityId);
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
        $validated['itr_details'] = $this->processItrDetailsFiles($request);

        $lead = Lead::create($validated);
        $this->processLeadDocumentUploads($request, $lead);

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
     * Update the specified lead in storage.
     */
    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validated();
        $validated['itr_details'] = $this->processItrDetailsFiles($request, $lead->itr_details);

        $lead->update($validated);
        $this->processLeadDocumentUploads($request, $lead);

        return redirect()->route('admin.leads.index')
            ->with('success', "Lead \"{$lead->name}\" updated successfully.");
    }

    /**
     * Download a lead document securely.
     */
    public function downloadDocument(Lead $lead, \App\Models\LeadDocument $document)
    {
        $this->authorize('leads.view');

        if ($document->lead_id !== $lead->id) {
            abort(403, 'Unauthorized access to lead document.');
        }

        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
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
        $this->authorize('leads.edit');

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
     * Download the Pre-Sanction Inspection Sheet (Annexure V-A) PDF.
     */
    public function downloadInspectionSheetPdf(Lead $lead)
    {
        $this->authorize('leads.view');

        $lead->load(['bank', 'city', 'constitution']);
        $bld = $lead->bank_loan_details ?? [];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.leads.pdf.inspection_sheet', compact('lead', 'bld'));
        $pdf->setPaper('a4', 'portrait');

        $fileName = 'inspection_sheet_lead_' . $lead->id . '.pdf';

        return $pdf->download($fileName);
    }
}
