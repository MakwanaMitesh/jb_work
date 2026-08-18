<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard overview — built from real Employee & Role data only
     * (Task/Lead/EOD modules aren't implemented yet, so nothing here is faked).
     */
    public function index(): View
    {
        $employees = User::whereHas('roles', fn ($q) => $q->where('name', '!=', 'Agent'));

        $stats = [
            'total_employees' => (clone $employees)->count(),
            'active_employees' => (clone $employees)->where('status', 'active')->count(),
            'inactive_employees' => (clone $employees)->where('status', 'inactive')->count(),
            'total_roles' => Role::count(),
        ];

        $roleBreakdown = Role::withCount('users')->orderByDesc('users_count')->get();

        $recentEmployees = (clone $employees)->with('roles')->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'roleBreakdown', 'recentEmployees'));
    }
}
