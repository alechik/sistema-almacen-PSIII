<?php

namespace App\Providers;

use App\Http\Controllers\View\NotificationComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }


    public function boot(): void
    {
        View::composer(
            'dashboard-layouts.header-menu', // 📌 El header donde quieres mostrar
            NotificationComposer::class
        );
    }
}
