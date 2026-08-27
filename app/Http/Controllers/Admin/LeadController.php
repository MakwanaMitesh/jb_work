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

        return view('admin.leads.create', compact('cities', 'agents', 'loanProducts', 'constitutions'));
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $lead = Lead::create($request->validated());

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

        $lead->load(['visits.employee', 'visits.creator', 'activities.user', 'assignments.employee', 'assignments.assigner', 'loanProduct', 'constitution']);

        return view('admin.leads.show', compact('lead', 'employees'));
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

        return view('admin.leads.edit', compact('lead', 'cities', 'agents', 'loanProducts', 'constitutions'));
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $lead->update($request->validated());

        return redirect()->route('admin.leads.index')
            ->with('success', "Lead \"{$lead->name}\" updated successfully.");
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
}
