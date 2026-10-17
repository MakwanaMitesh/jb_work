<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicContactController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $loanProducts = collect();
    if (\Illuminate\Support\Facades\Schema::hasTable('loan_products')) {
        try {
            $loanProducts = \App\Models\LoanProduct::where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        } catch (\Throwable $e) {
            $loanProducts = collect();
        }
    }

    return view('welcome', compact('loanProducts'));
})->name('home');

Route::post('/contact-enquiries', [PublicContactController::class, 'store'])->name('contact-enquiries.store');
Route::post('/contact', [PublicContactController::class, 'store'])->name('contact.store');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'permission:dashboard.view'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
