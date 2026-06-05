<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Railway (and most PaaS) terminate SSL at the proxy and forward plain
        // HTTP to the app, so force HTTPS on generated URLs in production to
        // avoid mixed-content blocking of assets.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}