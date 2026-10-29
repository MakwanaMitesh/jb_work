<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LoanProduct;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use App\Services\PendencyService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        $hasDeadlineColumn = Schema::hasColumn('leads', 'deadline');
        $baseQuery = Lead::where('assigned_employee_id', $userId);

        // 1. Fetch assigned leads with eager-loaded relations for pendency, table, and performance
        $assignedLeads = (clone $baseQuery)
            ->with([
                'city',
                'loanProduct',
                'bank',
                'activities' => fn ($q) => $q->latest(),
                'visits' => fn ($q) => $q->where('status', '!=', 'cancelled')->latest('visit_date'),
                'leadDocuments.documentType',
                'dprReport',
                'cmaProfile',
                'opinionReport',
                'stockStatement',
            ])
            ->get();

        // 2. Compute pendency for all assigned leads using PendencyService
        $pendencyService = app(PendencyService::class);
        $leadPendencyMap = [];
        $myPendingWorkByLead = [];
        $pendingByModule = [];
        $totalPendingWorkCount = 0;

        foreach ($assignedLeads as $lead) {
            $pendencyResult = $pendencyService->forLead($lead, $user);
            $leadPendencyMap[$lead->id] = $pendencyResult;
            $totalPendingWorkCount += $pendencyResult['pending'];

            if ($pendencyResult['pending'] > 0) {
                $pendingModules = array_values(array_filter($pendencyResult['modules'], fn ($m) => ($m['pending_count'] ?? 0) > 0));
                $myPendingWorkByLead[] = [
                    'lead' => $lead,
                    'total_pending' => $pendencyResult['pending'],
                    'total_completed' => $pendencyResult['completed'],
                    'modules' => $pendingModules,
                ];
                foreach ($pendingModules as $pmod) {
                    $lbl = $pmod['label'];
                    $pendingByModule[$lbl] = ($pendingByModule[$lbl] ?? 0) + $pmod['pending_count'];
                }
            }
        }
        arsort($pendingByModule);

        // 3. Basic & Comprehensive Summary Card Stats
        $totalAssignedLeads = $assignedLeads->count();
        $newLeads = $assignedLeads->where('status', Lead::STATUS_NEW)->count();
        $followUpLeads = $assignedLeads->whereIn('status', ['contacted', 'in_progress', 'visit_pending', 'visit_completed'])->count();
        $qualifiedLeads = $assignedLeads->whereIn('status', ['documentation_pending', 'documentation_in_progress', 'documentation_completed', 'under_process', 'approved'])->count();
        $convertedLeads = $assignedLeads->whereIn('status', ['converted', 'completed'])->count();
        $closedLeads = $assignedLeads->whereIn('status', ['lost', 'rejected', 'cancelled'])->count();

        $activeLeadsCount = $assignedLeads->whereIn('status', Lead::PENDING_ACTIVE_STATUSES)->count();
        $approvedConvertedLeads = $assignedLeads->whereIn('status', Lead::APPROVED_CONVERTED_STATUSES)->count();
        $rejectedClosedLeads = $assignedLeads->whereIn('status', Lead::REJECTED_CLOSED_STATUSES)->count();

        $completedLeadsCount = $assignedLeads->where('status', 'completed')->count();
        $completedThisMonthCount = $assignedLeads->where('status', 'completed')
            ->filter(fn ($l) => $l->updated_at && $l->updated_at->gte(now()->startOfMonth()))
            ->count();

        $overdueLeadsCount = 0;
        $dueTodayCount = 0;
        if ($hasDeadlineColumn) {
            $overdueLeadsCount = $assignedLeads->filter(function ($l) {
                return $l->deadline && Carbon::parse($l->deadline)->isPast() && !in_array($l->status, ['completed', 'rejected', 'cancelled', 'lost']);
            })->count();

            $dueTodayCount = $assignedLeads->filter(function ($l) {
                return $l->deadline && Carbon::parse($l->deadline)->isToday() && !in_array($l->status, ['completed', 'rejected', 'cancelled', 'lost']);
            })->count();
        }

        $leadStats = [
            'total_assigned_leads' => $totalAssignedLeads,
            'total_leads' => $totalAssignedLeads,
            'active_leads' => $activeLeadsCount,
            'pending_work_count' => $totalPendingWorkCount,
            'overdue_leads' => $overdueLeadsCount,
            'due_today_leads' => $dueTodayCount,
            'completed_leads' => $completedLeadsCount,
            'completed_this_month' => $completedThisMonthCount,
            'new_leads' => $newLeads,
            'follow_up_leads' => $followUpLeads,
            'qualified_leads' => $qualifiedLeads,
            'converted_leads' => $convertedLeads,
            'closed_leads' => $closedLeads,
            'pending_active_leads' => $activeLeadsCount,
            'approved_converted_leads' => $approvedConvertedLeads,
            'rejected_closed_leads' => $rejectedClosedLeads,
        ];

        // 4. City-wise Lead Status Matrix Cards (Assigned Only, Dynamic, Alphabetical)
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

        // 5. Assigned Leads by Status
        $leadsByStatus = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        // 6. Leads by City (Assigned only)
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

        // 7. Recent Assigned Leads
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

        // 8. Upcoming Deadlines Widget Data
        $overdueLeads = collect();
        $dueTodayLeads = collect();
        $upcoming7DaysLeads = collect();

        if ($hasDeadlineColumn) {
            $overdueLeads = $assignedLeads->filter(function ($l) {
                return $l->deadline && Carbon::parse($l->deadline)->isPast() && !in_array($l->status, ['completed', 'rejected', 'cancelled', 'lost']);
            })->sortBy('deadline')->values();

            $dueTodayLeads = $assignedLeads->filter(function ($l) {
                return $l->deadline && Carbon::parse($l->deadline)->isToday() && !in_array($l->status, ['completed', 'rejected', 'cancelled', 'lost']);
            })->sortBy('deadline')->values();

            $upcoming7DaysLeads = $assignedLeads->filter(function ($l) {
                if (!$l->deadline || in_array($l->status, ['completed', 'rejected', 'cancelled', 'lost'])) {
                    return false;
                }
                $d = Carbon::parse($l->deadline);
                return $d->isFuture() && $d->lte(now()->addDays(7)->endOfDay());
            })->sortBy('deadline')->values();
        }

        // 9. Upcoming Follow-ups
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

        // 10. Recent Activity
        $recentActivities = LeadActivity::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('lead', fn ($l) => $l->where('assigned_employee_id', $userId));
            })
            ->with(['lead', 'user'])
            ->latest()
            ->take(10)
            ->get();

        // 11. My Assigned Leads Table Filtering, Sorting, and Pagination
        $filteredTableLeads = $assignedLeads;

        // Search by Lead name or ID (or phone/email if query is 4+ characters)
        $search = trim((string)request('search'));
        if ($search !== '') {
            $searchLower = strtolower($search);
            $idSearch = preg_replace('/^#?ld-?0*/i', '', $search);
            $filteredTableLeads = $filteredTableLeads->filter(function ($l) use ($searchLower, $idSearch) {
                $nameMatches = str_contains(strtolower($l->name), $searchLower);
                $mobileMatches = strlen($searchLower) >= 4 && str_contains(strtolower($l->mobile_number ?? ''), $searchLower);
                $emailMatches = strlen($searchLower) >= 4 && str_contains(strtolower($l->email ?? ''), $searchLower);
                $formattedId = '#ld-' . str_pad($l->id, 5, '0', STR_PAD_LEFT);
                $idMatches = (string)$l->id === $idSearch 
                    || (string)$l->id === $searchLower 
                    || $formattedId === $searchLower
                    || ltrim($formattedId, '#') === $searchLower;
                return $nameMatches || $mobileMatches || $emailMatches || $idMatches;
            });
        }

        // Card Quick Filter (?filter=active / overdue / etc.)
        $cardFilter = request('filter');
        if ($cardFilter === 'active') {
            $filteredTableLeads = $filteredTableLeads->filter(fn ($l) => in_array($l->status, Lead::PENDING_ACTIVE_STATUSES));
        } elseif ($cardFilter === 'pending_work') {
            $filteredTableLeads = $filteredTableLeads->filter(fn ($l) => ($leadPendencyMap[$l->id]['pending'] ?? 0) > 0);
        } elseif ($cardFilter === 'overdue') {
            $filteredTableLeads = $filteredTableLeads->filter(fn ($l) => $hasDeadlineColumn && $l->deadline && Carbon::parse($l->deadline)->isPast() && !in_array($l->status, ['completed', 'rejected', 'cancelled', 'lost']));
        } elseif ($cardFilter === 'due_today') {
            $filteredTableLeads = $filteredTableLeads->filter(fn ($l) => $hasDeadlineColumn && $l->deadline && Carbon::parse($l->deadline)->isToday() && !in_array($l->status, ['completed', 'rejected', 'cancelled', 'lost']));
        } elseif ($cardFilter === 'completed') {
            $filteredTableLeads = $filteredTableLeads->filter(fn ($l) => $l->status === 'completed');
        }

        // Specific Status Filter
        if ($statusParam = request('status')) {
            $normalizedStatus = strtolower(str_replace(' ', '_', $statusParam));
            $filteredTableLeads = $filteredTableLeads->filter(fn ($l) => $l->status === $normalizedStatus);
        }

        // City Filter
        $cityParam = request('city_id');
        if ($cityParam) {
            if ($cityParam === 'unassigned') {
                $filteredTableLeads = $filteredTableLeads->filter(fn ($l) => empty($l->city_id));
            } else {
                $filteredTableLeads = $filteredTableLeads->filter(fn ($l) => (string)$l->city_id === (string)$cityParam);
            }
        }

        // Loan Product Filter
        if ($prodParam = request('loan_product_id')) {
            $filteredTableLeads = $filteredTableLeads->filter(fn ($l) => (string)$l->loan_product_id === (string)$prodParam);
        }

        // Deadline Filter
        if ($dlFilter = request('deadline_filter')) {
            if ($dlFilter === 'overdue') {
                $filteredTableLeads = $filteredTableLeads->filter(fn ($l) => $hasDeadlineColumn && $l->deadline && Carbon::parse($l->deadline)->isPast() && !in_array($l->status, ['completed', 'rejected', 'cancelled', 'lost']));
            } elseif ($dlFilter === 'due_today') {
                $filteredTableLeads = $filteredTableLeads->filter(fn ($l) => $hasDeadlineColumn && $l->deadline && Carbon::parse($l->deadline)->isToday() && !in_array($l->status, ['completed', 'rejected', 'cancelled', 'lost']));
            } elseif ($dlFilter === 'upcoming_7') {
                $filteredTableLeads = $filteredTableLeads->filter(fn ($l) => $hasDeadlineColumn && $l->deadline && Carbon::parse($l->deadline)->isFuture() && Carbon::parse($l->deadline)->lte(now()->addDays(7)->endOfDay()) && !in_array($l->status, ['completed', 'rejected', 'cancelled', 'lost']));
            } elseif ($dlFilter === 'no_deadline') {
                $filteredTableLeads = $filteredTableLeads->filter(fn ($l) => !$hasDeadlineColumn || empty($l->deadline));
            }
        }

        // Sorting (default: deadline, nearest first)
        $sort = request('sort', 'deadline');
        $direction = request('direction', ($sort === 'deadline' ? 'asc' : 'desc'));
        $isAsc = strtolower($direction) === 'asc';

        if ($sort === 'deadline') {
            $filteredTableLeads = $filteredTableLeads->sort(function ($a, $b) use ($isAsc, $hasDeadlineColumn) {
                $dlA = ($hasDeadlineColumn && $a->deadline) ? Carbon::parse($a->deadline)->timestamp : null;
                $dlB = ($hasDeadlineColumn && $b->deadline) ? Carbon::parse($b->deadline)->timestamp : null;

                if ($dlA === null && $dlB === null) {
                    $dateA = $a->created_at ? $a->created_at->timestamp : 0;
                    $dateB = $b->created_at ? $b->created_at->timestamp : 0;
                    return ($dateB <=> $dateA) ?: ($b->id <=> $a->id);
                }
                if ($dlA === null) return 1;
                if ($dlB === null) return -1;
                if ($dlA === $dlB) {
                    $dateA = $a->created_at ? $a->created_at->timestamp : 0;
                    $dateB = $b->created_at ? $b->created_at->timestamp : 0;
                    return ($dateB <=> $dateA) ?: ($b->id <=> $a->id);
                }
                return $isAsc ? ($dlA <=> $dlB) : ($dlB <=> $dlA);
            });
        } elseif ($sort === 'created_at') {
            $filteredTableLeads = $filteredTableLeads->sort(function ($a, $b) use ($isAsc) {
                $dateA = $a->created_at ? $a->created_at->timestamp : 0;
                $dateB = $b->created_at ? $b->created_at->timestamp : 0;
                $comp = $isAsc ? ($dateA <=> $dateB) : ($dateB <=> $dateA);
                return $comp ?: ($isAsc ? ($a->id <=> $b->id) : ($b->id <=> $a->id));
            });
        } elseif ($sort === 'assigned_date' || $sort === 'assigned_at') {
            $filteredTableLeads = $filteredTableLeads->sort(function ($a, $b) use ($isAsc) {
                $dateA = $a->assigned_at ? $a->assigned_at->timestamp : ($a->created_at ? $a->created_at->timestamp : 0);
                $dateB = $b->assigned_at ? $b->assigned_at->timestamp : ($b->created_at ? $b->created_at->timestamp : 0);
                $comp = $isAsc ? ($dateA <=> $dateB) : ($dateB <=> $dateA);
                return $comp ?: ($isAsc ? ($a->id <=> $b->id) : ($b->id <=> $a->id));
            });
        } elseif ($sort === 'status') {
            $filteredTableLeads = $filteredTableLeads->sort(function ($a, $b) use ($isAsc) {
                $comp = $isAsc ? strcmp($a->status, $b->status) : strcmp($b->status, $a->status);
                return $comp ?: ($b->id <=> $a->id);
            });
        } elseif ($sort === 'pending_count') {
            $filteredTableLeads = $filteredTableLeads->sort(function ($a, $b) use ($isAsc, $leadPendencyMap) {
                $pA = $leadPendencyMap[$a->id]['pending'] ?? 0;
                $pB = $leadPendencyMap[$b->id]['pending'] ?? 0;
                $comp = $isAsc ? ($pA <=> $pB) : ($pB <=> $pA);
                return $comp ?: ($b->id <=> $a->id);
            });
        } else {
            $filteredTableLeads = $filteredTableLeads->sort(function ($a, $b) {
                $dateA = $a->created_at ? $a->created_at->timestamp : 0;
                $dateB = $b->created_at ? $b->created_at->timestamp : 0;
                return ($dateB <=> $dateA) ?: ($b->id <=> $a->id);
            });
        }

        // Pagination
        $page = LengthAwarePaginator::resolveCurrentPage('page');
        $perPage = 10;
        $tableLeadsCollection = $filteredTableLeads->values();
        $currentPageItems = $tableLeadsCollection->slice(($page - 1) * $perPage, $perPage)->values();

        $tableLeads = new LengthAwarePaginator(
            $currentPageItems,
            $tableLeadsCollection->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
        $tableLeads->withQueryString();

        // Filter options for dropdowns
        $assignedCities = City::whereIn('id', $assignedLeads->pluck('city_id')->filter())->orderBy('name')->get();
        $allLoanProducts = LoanProduct::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();

        return view('dashboard', [
            'isAdmin' => false,
            'leadStats' => $leadStats,
            'cityLeadStatusCards' => $cityLeadStatusCards,
            'leadsByStatus' => $leadsByStatus,
            'leadsByCity' => $leadsByCity,
            'recentAssignedLeads' => $recentAssignedLeads,
            'upcomingFollowUps' => $upcomingFollowUps,
            'recentActivities' => $recentActivities,
            'hasDeadlineColumn' => $hasDeadlineColumn,
            'hasDeadlineField' => $hasDeadlineColumn,
            'leadPendencyMap' => $leadPendencyMap,
            'myPendingWorkByLead' => $myPendingWorkByLead,
            'pendingByModule' => $pendingByModule,
            'overdueLeads' => $overdueLeads,
            'dueTodayLeads' => $dueTodayLeads,
            'upcoming7DaysLeads' => $upcoming7DaysLeads,
            'tableLeads' => $tableLeads,
            'assignedCities' => $assignedCities,
            'allLoanProducts' => $allLoanProducts,
            'activeFilter' => $cardFilter,
            'currentSort' => $sort,
            'currentDirection' => $direction,
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
