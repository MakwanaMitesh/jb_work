<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentTypeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('document_types.view');

        $query = DocumentType::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $documentTypes = $query->ordered()->paginate(15)->withQueryString();

        return view('admin.document_types.index', compact('documentTypes'));
    }

    public function create(): View
    {
        $this->authorize('document_types.create');

        return view('admin.document_types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('document_types.create');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:100', 'unique:document_types,code'],
            'description' => ['nullable', 'string'],
            'is_required' => ['nullable', 'boolean'],
            'has_front_back' => ['nullable', 'boolean'],
            'allow_multiple' => ['nullable', 'boolean'],
            'allowed_file_types' => ['nullable', 'array'],
            'allowed_file_types.*' => ['string'],
            'max_file_size_kb' => ['required', 'integer', 'min:512', 'max:102400'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['is_required'] = $request->boolean('is_required');
        $validated['has_front_back'] = $request->boolean('has_front_back');
        $validated['allow_multiple'] = $request->boolean('allow_multiple');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        DocumentType::create($validated);

        return redirect()->route('admin.document-types.index')
            ->with('success', 'Document type created successfully.');
    }

    public function edit(DocumentType $documentType): View
    {
        $this->authorize('document_types.edit');

        return view('admin.document_types.edit', compact('documentType'));
    }

    public function update(Request $request, DocumentType $documentType): RedirectResponse
    {
        $this->authorize('document_types.edit');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:100', 'unique:document_types,code,' . $documentType->id],
            'description' => ['nullable', 'string'],
            'is_required' => ['nullable', 'boolean'],
            'has_front_back' => ['nullable', 'boolean'],
            'allow_multiple' => ['nullable', 'boolean'],
            'allowed_file_types' => ['nullable', 'array'],
            'allowed_file_types.*' => ['string'],
            'max_file_size_kb' => ['required', 'integer', 'min:512', 'max:102400'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['is_required'] = $request->boolean('is_required');
        $validated['has_front_back'] = $request->boolean('has_front_back');
        $validated['allow_multiple'] = $request->boolean('allow_multiple');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $documentType->update($validated);

        return redirect()->route('admin.document-types.index')
            ->with('success', 'Document type updated successfully.');
    }

    public function toggleStatus(DocumentType $documentType): RedirectResponse
    {
        $this->authorize('document_types.activate');

        $newStatus = $documentType->status === 'active' ? 'inactive' : 'active';
        $documentType->update(['status' => $newStatus]);

        return redirect()->back()
            ->with('success', "Document type status updated to {$newStatus}.");
    }

    public function destroy(DocumentType $documentType): RedirectResponse
    {
        $this->authorize('document_types.delete');

        $documentType->delete();

        return redirect()->route('admin.document-types.index')
            ->with('success', 'Document type deleted successfully.');
    }
}
