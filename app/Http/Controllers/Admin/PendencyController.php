<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Lead;
use App\Models\User;
use App\Services\PendencyService;
use Illuminate\View\View;

/**
 * Read-only view over Lead data that already exists: no pending work is stored anywhere, it is
 * computed on each request by PendencyService from the Lead's own columns and its DPR/CMA/OR/
 * Stock Statement/Application Form/Document data. Access is gated the same way every other admin
 * listing is — pendency.view — so a user still needs the usual Lead-related permissions to act on
 * anything a pending item links to (the "View Pendency" links open the existing Lead edit screen,
 * which enforces its own permissions as it always has).
 */
class PendencyController extends Controller
{
    public function index(PendencyService $service): View
    {
        $this->authorize('pendency.view');

        $query = Lead::query()->with(['city', 'assignedEmployee']);

        $user = request()->user();
        if (! $user->can('leads.view_all') && $user->can('leads.view_own')) {
            $query->where('assigned_employee_id', $user->id);
        }

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }
        if ($cityId = request('city_id')) {
            $query->where('city_id', $cityId);
        }
        if ($employeeId = request('assigned_employee_id')) {
            $query->where('assigned_employee_id', $employeeId);
        }

        $leads = $query->orderByDesc('created_at')->orderByDesc('id')->get();
        $summaries = $service->summaries($leads, request()->user());

        // The Module filter and the Pending/Completed status filter apply after computing each
        // lead's pendency (they depend on it), not as a database WHERE clause.
        if ($moduleLabel = request('module')) {
            $summaries = array_values(array_filter($summaries, fn ($s) => in_array($moduleLabel, $s['pending_modules'], true)));
        }
        if ($statusFilter = request('status')) {
            $summaries = array_values(array_filter($summaries, fn ($s) => $statusFilter === 'pending' ? $s['pending'] > 0 : $s['pending'] === 0));
        }

        $totalActiveLeads = Lead::count();
        $leadsWithPendingWork = count(array_filter($summaries, fn ($s) => $s['pending'] > 0));
        $totalPendingItems = array_sum(array_column($summaries, 'pending'));
        $completedLeads = count(array_filter($summaries, fn ($s) => $s['pending'] === 0 && $s['completed'] > 0));

        $cities = City::where('status', 'active')->orderBy('name')->get();
        $employees = User::where('status', 'active')->orderBy('name')->get();
        $moduleLabels = $summaries ? array_unique(array_merge(...array_column($summaries, 'pending_modules'))) : [];
        sort($moduleLabels);

        return view('admin.pendency.index', compact(
            'summaries', 'totalActiveLeads', 'leadsWithPendingWork', 'totalPendingItems', 'completedLeads', 'cities', 'employees', 'moduleLabels'
        ));
    }

    public function show(Lead $lead, PendencyService $service): View
    {
        $this->authorize('pendency.view');

        $user = request()->user();
        if (! $user->can('leads.view_all') && $user->can('leads.view_own') && $lead->assigned_employee_id !== $user->id) {
            abort(403, 'You do not have permission to view this lead.');
        }

        $lead->load(['city', 'assignedEmployee', 'bank', 'loanProduct']);
        $result = $service->forLead($lead, request()->user());

        return view('admin.pendency.show', [
            'lead' => $lead,
            'modules' => $result['modules'],
            'pending' => $result['pending'],
            'completed' => $result['completed'],
        ]);
    }
}
