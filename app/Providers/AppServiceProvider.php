<?php

namespace App\Providers;

use App\Http\Controllers\View\NotificationComposer;
use Illuminate\Support\Facades\View;
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
        View::composer(
            'dashboard-layouts.header-menu', // 📌 El header donde quieres mostrar
            NotificationComposer::class
        );
    }
}
