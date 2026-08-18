<?php

use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserPermissionController;
use Illuminate\Support\Facades\Route;

// Role & Permission management, and per-user direct permissions.
// Authorization is enforced per-action in the controllers via Policies
// (App\Policies\RolePolicy / App\Policies\UserPolicy) — Admin only.
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('roles', RoleController::class)
        ->except(['show', 'destroy']);

    Route::patch('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])
        ->name('roles.toggle-status');

    Route::get('users', [UserPermissionController::class, 'index'])->name('users.index');
    Route::get('users/{user}/permissions', [UserPermissionController::class, 'edit'])->name('users.permissions.edit');
    Route::put('users/{user}/permissions', [UserPermissionController::class, 'update'])->name('users.permissions.update');

    // Employee Management. Every action is additionally gated inside
    // EmployeeController via $this->authorize('employees.*') — the granular
    // permissions created in Step 3 — so access works for any user holding
    // that permission, not just a hardcoded "Admin" role check.
    Route::resource('employees', EmployeeController::class)
        ->except(['show'])
        ->parameters(['employees' => 'employee']);
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::patch('employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])
        ->name('employees.toggle-status');

    // Agent Management. Every action is additionally gated inside
    // AgentController via $this->authorize('agent.*')
    Route::resource('agents', AgentController::class)
        ->except(['show'])
        ->parameters(['agents' => 'agent']);
    Route::get('agents/{agent}', [AgentController::class, 'show'])->name('agents.show');
    Route::patch('agents/{agent}/toggle-status', [AgentController::class, 'toggleStatus'])
        ->name('agents.toggle-status');

    // City Management. Gated by city.* permissions inside CityController.
    Route::resource('cities', CityController::class)
        ->except(['show'])
        ->parameters(['cities' => 'city']);
    Route::patch('cities/{city}/toggle-status', [CityController::class, 'toggleStatus'])
        ->name('cities.toggle-status');
});
