<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability, $arguments = []) {
            if ($ability === 'toggleStatus' && isset($arguments[0]) && $arguments[0] instanceof \App\Models\Role) {
                return null;
            }

            return $user->hasRole('Admin') ? true : null;
        });
    }
}
