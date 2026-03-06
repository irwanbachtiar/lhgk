<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Lhgk;
use App\Observers\LhgkObserver;

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
        // Register Lhgk observer to auto-fill calculated fields
        Lhgk::observe(LhgkObserver::class);
        // Temporary: log slow database queries for profiling (only in local/dev)
        if ($this->app->environment('local') || $this->app->environment('development')) {
            \DB::listen(function ($query) {
                // log queries slower than 200ms
                if (isset($query->time) && $query->time > 200) {
                    \Log::warning('slow_query', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time_ms' => $query->time,
                    ]);
                }
            });
        }
    }
}
