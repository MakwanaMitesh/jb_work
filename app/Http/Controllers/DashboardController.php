<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard overview — routes to Admin or Employee view based on user role.
     */
    public function index(): View
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('Admin');

        if ($isAdmin) {
            return $this->adminDashboard($user);
        }

        return $this->employeeDashboard($user);
    }

    /**
     * Admin / Super Admin Dashboard.
     */
    protected function adminDashboard(User $user): View
    {
        $isAdmin = true;

        // 1. Basic Lead Statistics & Top Summary Cards
        $totalLeads = Lead::count();
        $newLeads = Lead::where('status', Lead::STATUS_NEW)->count();
        $followUpLeads = Lead::whereIn('status', ['contacted', 'in_progress', 'visit_pending', 'visit_completed'])->count();
        $qualifiedLeads = Lead::whereIn('status', ['documentation_pending', 'documentation_in_progress', 'documentation_completed', 'under_process', 'approved'])->count();
        $convertedLeads = Lead::whereIn('status', ['converted', 'completed'])->count();
        $closedLeads = Lead::whereIn('status', ['lost', 'rejected', 'cancelled'])->count();

        $pendingActiveLeads = Lead::whereIn('status', Lead::PENDING_ACTIVE_STATUSES)->count();
        $approvedConvertedLeads = Lead::whereIn('status', Lead::APPROVED_CONVERTED_STATUSES)->count();
        $rejectedClosedLeads = Lead::whereIn('status', Lead::REJECTED_CLOSED_STATUSES)->count();

        $leadStats = [
            'total_leads' => $totalLeads,
            'new_leads' => $newLeads,
            'follow_up_leads' => $followUpLeads,
            'qualified_leads' => $qualifiedLeads,
            'converted_leads' => $convertedLeads,
            'closed_leads' => $closedLeads,
            'pending_active_leads' => $pendingActiveLeads,
            'approved_converted_leads' => $approvedConvertedLeads,
            'rejected_closed_leads' => $rejectedClosedLeads,
        ];

        // 2. City-wise Lead Status Matrix Cards (Dynamic, All cities with leads, Alphabetical)
        $cityStatusRows = DB::table('leads')
            ->leftJoin('cities', 'leads.city_id', '=', 'cities.id')
            ->whereNull('leads.deleted_at')
            ->select(
                'cities.id as city_id',
                'cities.name as city_name',
                'leads.status',
                DB::raw('count(leads.id) as count')
            )
            ->groupBy('cities.id', 'cities.name', 'leads.status')
            ->get();

        $cityLeadStatusCards = $this->buildCityLeadStatusCards($cityStatusRows);

        // 3. Leads by City (Chart & Summary)
        $leadsByCity = City::select('cities.id', 'cities.name', DB::raw('count(leads.id) as total'))
            ->leftJoin('leads', function ($join) {
                $join->on('cities.id', '=', 'leads.city_id')
                    ->whereNull('leads.deleted_at');
            })
            ->groupBy('cities.id', 'cities.name')
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->get();

        $unassignedCityCount = Lead::whereNull('city_id')->count();
        if ($unassignedCityCount > 0) {
            $leadsByCity->push((object)[
                'id' => null,
                'name' => 'Unassigned',
                'total' => $unassignedCityCount,
            ]);
        }

        // 4. Leads by Status (Chart & Summary)
        $leadsByStatus = Lead::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        // 5. Recent Leads
        $recentLeads = Lead::with(['city', 'loanProduct', 'assignedEmployee'])
            ->latest()
            ->take(6)
            ->get();

        // 6. Employee / System stats
        $employees = User::whereHas('roles', fn ($q) => $q->where('name', '!=', 'Agent'));
        $stats = [
            'total_employees' => (clone $employees)->count(),
            'active_employees' => (clone $employees)->where('status', 'active')->count(),
            'inactive_employees' => (clone $employees)->where('status', 'inactive')->count(),
            'total_roles' => Role::count(),
        ];
        $roleBreakdown = Role::withCount('users')->orderByDesc('users_count')->get();
        $recentEmployees = (clone $employees)->with('roles')->latest()->take(5)->get();
        $expiredInsurances = User::whereHas('roles', fn ($q) => $q->where('name', 'Employee'))
            ->where('status', 'active')
            ->whereNotNull('insurance_end_date')
            ->where('insurance_end_date', '<', now()->toDateString())
            ->get();

        return view('dashboard', compact(
            'isAdmin',
            'leadStats',
            'cityLeadStatusCards',
            'leadsByCity',
            'leadsByStatus',
            'recentLeads',
            'stats',
            'roleBreakdown',
            'recentEmployees',
            'expiredInsurances'
        ));
    }

    /**
     * Employee Dashboard (scoped strictly to the logged-in employee).
     */
    protected function employeeDashboard(User $user): View
    {
        $userId = $user->id;
        $baseQuery = Lead::where('assigned_employee_id', $userId);

        // 1. Basic Summary Cards (Filtered strictly by logged-in employee)
        $totalAssignedLeads = (clone $baseQuery)->count();
        $newLeads = (clone $baseQuery)->where('status', Lead::STATUS_NEW)->count();
        $followUpLeads = (clone $baseQuery)->whereIn('status', ['contacted', 'in_progress', 'visit_pending', 'visit_completed'])->count();
        $qualifiedLeads = (clone $baseQuery)->whereIn('status', ['documentation_pending', 'documentation_in_progress', 'documentation_completed', 'under_process', 'approved'])->count();
        $convertedLeads = (clone $baseQuery)->whereIn('status', ['converted', 'completed'])->count();
        $closedLeads = (clone $baseQuery)->whereIn('status', ['lost', 'rejected', 'cancelled'])->count();

        $pendingActiveLeads = (clone $baseQuery)->whereIn('status', Lead::PENDING_ACTIVE_STATUSES)->count();
        $approvedConvertedLeads = (clone $baseQuery)->whereIn('status', Lead::APPROVED_CONVERTED_STATUSES)->count();
        $rejectedClosedLeads = (clone $baseQuery)->whereIn('status', Lead::REJECTED_CLOSED_STATUSES)->count();

        $leadStats = [
            'total_assigned_leads' => $totalAssignedLeads,
            'total_leads' => $totalAssignedLeads,
            'new_leads' => $newLeads,
            'follow_up_leads' => $followUpLeads,
            'qualified_leads' => $qualifiedLeads,
            'converted_leads' => $convertedLeads,
            'closed_leads' => $closedLeads,
            'pending_active_leads' => $pendingActiveLeads,
            'approved_converted_leads' => $approvedConvertedLeads,
            'rejected_closed_leads' => $rejectedClosedLeads,
        ];

        // 2. City-wise Lead Status Matrix Cards (Assigned Only, Dynamic, Alphabetical)
        $cityStatusRows = DB::table('leads')
            ->leftJoin('cities', 'leads.city_id', '=', 'cities.id')
            ->where('leads.assigned_employee_id', $userId)
            ->whereNull('leads.deleted_at')
            ->select(
                'cities.id as city_id',
                'cities.name as city_name',
                'leads.status',
                DB::raw('count(leads.id) as count')
            )
            ->groupBy('cities.id', 'cities.name', 'leads.status')
            ->get();

        $cityLeadStatusCards = $this->buildCityLeadStatusCards($cityStatusRows);

        // 3. Assigned Leads by Status
        $leadsByStatus = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        // 4. Leads by City (Assigned only)
        $leadsByCity = City::select('cities.id', 'cities.name', DB::raw('count(leads.id) as total'))
            ->join('leads', function ($join) use ($userId) {
                $join->on('cities.id', '=', 'leads.city_id')
                    ->where('leads.assigned_employee_id', '=', $userId)
                    ->whereNull('leads.deleted_at');
            })
            ->groupBy('cities.id', 'cities.name')
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->get();

        $unassignedCityCount = (clone $baseQuery)->whereNull('city_id')->count();
        if ($unassignedCityCount > 0) {
            $leadsByCity->push((object)[
                'id' => null,
                'name' => 'Unassigned',
                'total' => $unassignedCityCount,
            ]);
        }

        // 5. Recent Assigned Leads
        $recentAssignedLeads = (clone $baseQuery)
            ->with([
                'city',
                'loanProduct',
                'activities' => fn ($q) => $q->latest(),
                'visits' => fn ($q) => $q->where('status', '!=', 'cancelled')->latest('visit_date'),
            ])
            ->latest('updated_at')
            ->take(6)
            ->get();

        // 6. Upcoming Follow-ups
        $upcomingFollowUps = Visit::where(function ($q) use ($userId) {
                $q->where('employee_id', $userId)
                  ->orWhereHas('lead', fn ($l) => $l->where('assigned_employee_id', $userId));
            })
            ->where(function ($q) {
                $q->where('visit_date', '>=', now()->startOfDay())
                  ->orWhere('next_follow_up_date', '>=', now()->startOfDay());
            })
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['lead.city', 'lead.loanProduct'])
            ->orderByRaw('COALESCE(visit_date, next_follow_up_date) ASC')
            ->take(6)
            ->get();

        // 7. Recent Activity
        $recentActivities = LeadActivity::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('lead', fn ($l) => $l->where('assigned_employee_id', $userId));
            })
            ->with(['lead', 'user'])
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard', [
            'isAdmin' => false,
            'leadStats' => $leadStats,
            'cityLeadStatusCards' => $cityLeadStatusCards,
            'leadsByStatus' => $leadsByStatus,
            'leadsByCity' => $leadsByCity,
            'recentAssignedLeads' => $recentAssignedLeads,
            'upcomingFollowUps' => $upcomingFollowUps,
            'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * Transform flat SQL group-by rows into structured, alphabetical City Cards.
     *
     * @param \Illuminate\Support\Collection $cityStatusRows
     * @return \Illuminate\Support\Collection
     */
    protected function buildCityLeadStatusCards(Collection $cityStatusRows): Collection
    {
        $citiesMap = [];

        foreach ($cityStatusRows as $row) {
            $cityKey = $row->city_id ? (string)$row->city_id : 'unassigned';
            $cityName = $row->city_name ?: 'Unassigned';

            if (!isset($citiesMap[$cityKey])) {
                $citiesMap[$cityKey] = [
                    'city_id' => $row->city_id,
                    'city_name' => $cityName,
                    'total_leads' => 0,
                    'status_counts' => [],
                ];
            }

            $citiesMap[$cityKey]['total_leads'] += (int)$row->count;
            $citiesMap[$cityKey]['status_counts'][$row->status] = (int)$row->count;
        }

        // Sort alphabetically by city name (with Unassigned at the end if present)
        return collect($citiesMap)->sortBy(function ($item) {
            return $item['city_name'] === 'Unassigned' ? 'ZZZZZZ' : strtolower($item['city_name']);
        })->values();
    }
}
