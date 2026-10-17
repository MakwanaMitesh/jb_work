<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoughLeadRequest;
use App\Http\Requests\Admin\UpdateRoughLeadRequest;
use App\Models\City;
use App\Models\Lead;
use App\Models\RoughLead;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoughLeadController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('rough_leads.view');

        $query = RoughLead::with(['city', 'assignedEmployee']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $roughLeads = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('admin.rough_leads.index', compact('roughLeads'));
    }

    public function create(): View
    {
        $this->authorize('rough_leads.create');

        $cities = City::where('status', 'active')->orderBy('name')->get();
        $employees = $this->employeeOptions();

        return view('admin.rough_leads.create', compact('cities', 'employees'));
    }

    public function store(StoreRoughLeadRequest $request): RedirectResponse
    {
        $roughLead = RoughLead::create($request->validated());

        return redirect()->route('admin.rough-leads.index')
            ->with('success', "Rough Lead \"{$roughLead->full_name}\" created successfully.");
    }

    public function show(RoughLead $roughLead): View
    {
        $this->authorize('rough_leads.view');

        $roughLead->load(['city', 'assignedEmployee', 'convertedLead']);

        return view('admin.rough_leads.show', compact('roughLead'));
    }

    public function edit(RoughLead $roughLead): View
    {
        $this->authorize('rough_leads.edit');

        $cities = City::where('status', 'active')->orderBy('name')->get();
        $employees = $this->employeeOptions();

        return view('admin.rough_leads.edit', compact('roughLead', 'cities', 'employees'));
    }

    public function update(UpdateRoughLeadRequest $request, RoughLead $roughLead): RedirectResponse
    {
        $roughLead->update($request->validated());

        return redirect()->route('admin.rough-leads.index')
            ->with('success', "Rough Lead \"{$roughLead->full_name}\" updated successfully.");
    }

    /**
     * Quick status change from the listing screen. "Converted" is not a
     * selectable value here — it is only ever reached through convert().
     */
    public function updateStatus(Request $request, RoughLead $roughLead): RedirectResponse
    {
        $this->authorize('rough_leads.change_status');

        $selectable = array_keys(RoughLead::STATUSES);
        $selectable = array_diff($selectable, ['converted']);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in($selectable)],
        ]);

        if ($roughLead->isConverted()) {
            return redirect()->route('admin.rough-leads.index')
                ->with('error', 'This Rough Lead has already been converted and its status can no longer be changed.');
        }

        $roughLead->update(['status' => $validated['status']]);

        return redirect()->route('admin.rough-leads.index')
            ->with('success', 'Rough Lead status updated successfully.');
    }

    public function destroy(RoughLead $roughLead): RedirectResponse
    {
        $this->authorize('rough_leads.delete');

        $roughLead->delete();

        return redirect()->route('admin.rough-leads.index')
            ->with('success', "Rough Lead \"{$roughLead->full_name}\" deleted.");
    }

    /**
     * Convert a Rough Lead into a Main Lead. The Rough Lead record is kept
     * (never duplicated into a new rough lead) and gains a reference to the
     * Lead it produced; a Rough Lead that already has that reference cannot
     * be converted again.
     */
    public function convert(RoughLead $roughLead): RedirectResponse
    {
        $this->authorize('rough_leads.convert');

        if ($roughLead->isConverted()) {
            return redirect()->route('admin.rough-leads.show', $roughLead)
                ->with('error', 'This Rough Lead has already been converted to a Main Lead.');
        }

        $lead = Lead::create([
            'name' => $roughLead->full_name,
            'email' => $roughLead->email,
            'mobile_number' => $roughLead->mobile_number,
            'alternate_mobile_number' => $roughLead->alternate_mobile_number,
            'city_id' => $roughLead->city_id,
            'assigned_employee_id' => $roughLead->assigned_employee_id,
            'source' => $roughLead->source,
            'status' => 'new',
            'notes' => trim(
                'Converted from Rough Lead #' . $roughLead->id
                . ($roughLead->loan_requirement ? ' — Requirement: ' . $roughLead->loan_requirement : '')
                . ($roughLead->description ? "\n\n" . $roughLead->description : '')
            ),
        ]);

        $roughLead->update([
            'converted_lead_id' => $lead->id,
            'converted_at' => now(),
            'status' => 'converted',
        ]);

        return redirect()->route('admin.rough-leads.show', $roughLead)
            ->with('success', "Rough Lead \"{$roughLead->full_name}\" converted to Main Lead successfully.");
    }

    protected function employeeOptions()
    {
        return User::where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->where('name', '!=', 'Agent'))
            ->orderBy('name')
            ->get();
    }
}
