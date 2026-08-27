<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanProduct;
use App\Models\CustomerConstitution;
use App\Models\LoanProductConstitution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanProductController extends Controller
{
    /**
     * Display a listing of loan products.
     */
    public function index(): View
    {
        $this->authorize('loan_products.view');

        $query = LoanProduct::query();

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('sort_order')->orderBy('name')->paginate(15);

        return view('admin.loan_products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $this->authorize('loan_products.create');

        return view('admin.loan_products.create');
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('loan_products.create');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50', 'unique:loan_products,code'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $product = LoanProduct::create($validated);

        return redirect()->route('admin.loan-products.index')
            ->with('success', "Loan Product \"{$product->name}\" created successfully.");
    }

    /**
     * Display product details.
     */
    public function show(LoanProduct $loanProduct): View
    {
        $this->authorize('loan_products.view');
        
        $loanProduct->load('constitutions');

        return view('admin.loan_products.show', compact('loanProduct'));
    }

    /**
     * Show the form for editing.
     */
    public function edit(LoanProduct $loanProduct): View
    {
        $this->authorize('loan_products.edit');

        return view('admin.loan_products.edit', compact('loanProduct'));
    }

    /**
     * Update product.
     */
    public function update(Request $request, LoanProduct $loanProduct): RedirectResponse
    {
        $this->authorize('loan_products.edit');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50', 'unique:loan_products,code,' . $loanProduct->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $loanProduct->update($validated);

        return redirect()->route('admin.loan-products.index')
            ->with('success', "Loan Product \"{$loanProduct->name}\" updated successfully.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(LoanProduct $loanProduct): RedirectResponse
    {
        $this->authorize('loan_products.edit');

        $newStatus = $loanProduct->status === 'active' ? 'inactive' : 'active';
        $loanProduct->update(['status' => $newStatus]);

        return redirect()->back()
            ->with('success', "Loan Product \"{$loanProduct->name}\" status updated to {$newStatus}.");
    }

    /**
     * Show config page for matching constitutions.
     */
    public function editConfig(LoanProduct $loanProduct): View
    {
        $this->authorize('loan_product_config.view');

        $constitutions = CustomerConstitution::orderBy('sort_order')->orderBy('name')->get();
        $assignedConstitutionIds = $loanProduct->constitutions()->wherePivot('status', 'active')->pluck('customer_constitutions.id')->toArray();

        return view('admin.loan_products.config', compact('loanProduct', 'constitutions', 'assignedConstitutionIds'));
    }

    /**
     * Update configuration mappings.
     */
    public function updateConfig(Request $request, LoanProduct $loanProduct): RedirectResponse
    {
        $this->authorize('loan_product_config.edit');

        $validated = $request->validate([
            'constitutions' => ['nullable', 'array'],
            'constitutions.*' => ['exists:customer_constitutions,id'],
        ]);

        $selectedIds = $validated['constitutions'] ?? [];

        // We will sync relationships: disable existing records not selected, activate or create selected ones.
        // To maintain unique constraint and auditability, we can delete inactive configurations or update status to inactive.
        // A clean way is to delete ones not selected, or use toggle-status style. Let's do:
        LoanProductConstitution::where('loan_product_id', $loanProduct->id)->delete();

        foreach ($selectedIds as $id) {
            LoanProductConstitution::create([
                'loan_product_id' => $loanProduct->id,
                'constitution_id' => $id,
                'status' => 'active',
            ]);
        }

        return redirect()->route('admin.loan-products.show', $loanProduct)
            ->with('success', 'Loan Product configuration updated successfully.');
    }
}
