<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankController extends Controller
{
    /**
     * Display a listing of banks.
     */
    public function index(): View
    {
        if (auth()->user()?->can('banks.view')) {
            $this->authorize('banks.view');
        }

        $query = Bank::query();

        if ($search = request('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $banks = $query->orderBy('sort_order')->orderBy('name')->paginate(15);

        return view('admin.banks.index', compact('banks'));
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        if (auth()->user()?->can('banks.create')) {
            $this->authorize('banks.create');
        }

        return view('admin.banks.create');
    }

    /**
     * Store bank record.
     */
    public function store(Request $request): RedirectResponse
    {
        if (auth()->user()?->can('banks.create')) {
            $this->authorize('banks.create');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:banks,name'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $bank = Bank::create($validated);

        return redirect()->route('admin.banks.index')
            ->with('success', "Bank \"{$bank->name}\" created successfully.");
    }

    /**
     * Show edit form.
     */
    public function edit(Bank $bank): View
    {
        if (auth()->user()?->can('banks.edit')) {
            $this->authorize('banks.edit');
        }

        return view('admin.banks.edit', compact('bank'));
    }

    /**
     * Update bank record.
     */
    public function update(Request $request, Bank $bank): RedirectResponse
    {
        if (auth()->user()?->can('banks.edit')) {
            $this->authorize('banks.edit');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:banks,name,' . $bank->id],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $bank->update($validated);

        return redirect()->route('admin.banks.index')
            ->with('success', "Bank \"{$bank->name}\" updated successfully.");
    }

    /**
     * Toggle status.
     */
    public function toggleStatus(Bank $bank): RedirectResponse
    {
        if (auth()->user()?->can('banks.edit')) {
            $this->authorize('banks.edit');
        }

        $newStatus = $bank->status === 'active' ? 'inactive' : 'active';
        $bank->update(['status' => $newStatus]);

        return redirect()->back()
            ->with('success', "Bank \"{$bank->name}\" status changed to {$newStatus}.");
    }

    /**
     * Remove bank record.
     */
    public function destroy(Bank $bank): RedirectResponse
    {
        if (auth()->user()?->can('banks.delete')) {
            $this->authorize('banks.delete');
        }

        $bank->delete();

        return redirect()->route('admin.banks.index')
            ->with('success', "Bank \"{$bank->name}\" deleted successfully.");
    }
}
