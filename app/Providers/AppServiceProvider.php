<?php

namespace App\Providers;

use App\Services\FuentePeruRail;
use App\Services\FuentePeruRailSimulada;
use App\Services\FuenteSenamhi;
use App\Services\FuenteSenamhiSimulada;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FuentePeruRail::class, FuentePeruRailSimulada::class);
        $this->app->bind(FuenteSenamhi::class, FuenteSenamhiSimulada::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
