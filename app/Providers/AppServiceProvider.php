<?php

namespace App\Providers;

use App\Models\EnvioPlanta;
use App\Policies\EnvioPlantaPolicy;
use App\Services\PlantaApiService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar el servicio de Planta API como singleton
        $this->app->singleton(PlantaApiService::class, function ($app) {
            return new PlantaApiService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar policies
        Gate::policy(EnvioPlanta::class, EnvioPlantaPolicy::class);
    }
}
