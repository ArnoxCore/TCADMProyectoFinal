<?php

namespace App\Providers;

use App\Models\Cita;
use App\Observers\CitaObserver;
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
        Cita::observe(CitaObserver::class);
    }
}
