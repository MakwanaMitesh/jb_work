<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerConstitution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerConstitutionController extends Controller
{
    /**
     * Display a listing.
     */
    public function index(): View
    {
        $this->authorize('constitutions.view');

        $query = CustomerConstitution::query();

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $constitutions = $query->orderBy('sort_order')->orderBy('name')->paginate(15);

        return view('admin.constitutions.index', compact('constitutions'));
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        $this->authorize('constitutions.create');

        return view('admin.constitutions.create');
    }

    /**
     * Store record.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('constitutions.create');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50', 'unique:customer_constitutions,code'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $constitution = CustomerConstitution::create($validated);

        return redirect()->route('admin.constitutions.index')
            ->with('success', "Constitution \"{$constitution->name}\" created successfully.");
    }

    /**
     * Show details.
     */
    public function show(CustomerConstitution $constitution): View
    {
        $this->authorize('constitutions.view');

        return view('admin.constitutions.show', compact('constitution'));
    }

    /**
     * Show edit form.
     */
    public function edit(CustomerConstitution $constitution): View
    {
        $this->authorize('constitutions.edit');

        return view('admin.constitutions.edit', compact('constitution'));
    }

    /**
     * Update record.
     */
    public function update(Request $request, CustomerConstitution $constitution): RedirectResponse
    {
        $this->authorize('constitutions.edit');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50', 'unique:customer_constitutions,code,' . $constitution->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $constitution->update($validated);

        return redirect()->route('admin.constitutions.index')
            ->with('success', "Constitution \"{$constitution->name}\" updated successfully.");
    }

    /**
     * Toggle status.
     */
    public function toggleStatus(CustomerConstitution $constitution): RedirectResponse
    {
        $this->authorize('constitutions.edit');

        $newStatus = $constitution->status === 'active' ? 'inactive' : 'active';
        $constitution->update(['status' => $newStatus]);

        return redirect()->back()
            ->with('success', "Constitution \"{$constitution->name}\" status updated to {$newStatus}.");
    }
}
