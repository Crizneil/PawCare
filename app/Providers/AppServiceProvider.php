<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();

        // Fix for "mail::" components not resolving in some Laravel environments
        \Illuminate\Support\Facades\Blade::anonymousComponentPath(
            base_path('vendor/laravel/framework/src/Illuminate/Mail/resources/views/html'),
            'mail'
        );

        // View Composer for Header Notifications
        \Illuminate\Support\Facades\View::composer('layout.header', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $user = \Illuminate\Support\Facades\Auth::user();
                $query = \App\Models\Vaccination::whereBetween('next_due_date', [now(), now()->addDays(14)]);
                
                if (strtolower($user->role) === 'owner') {
                    $query->whereHas('pet', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                }
                
                $view->with('upcomingCount', $query->count());
            }
        });
    }
}
