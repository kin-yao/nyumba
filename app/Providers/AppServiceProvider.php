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
        // Railway terminates SSL at its proxy and forwards plain HTTP, so force
        // HTTPS on generated URLs in production to avoid mixed-content blocking.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Recreate the Firebase Admin SDK credentials file from a base64 env var.
        // Railway's filesystem is ephemeral and its builder (Railpack) does not
        // run the nixpacks build step, so write the file at runtime if missing.
        $encoded = getenv('FIREBASE_CREDENTIALS_BASE64')
            ?: ($_ENV['FIREBASE_CREDENTIALS_BASE64'] ?? null);

        if ($encoded) {
            $path = storage_path('app/private/nyumba-d932c-firebase-adminsdk-fbsvc-dbcffe8b58.json');

            if (! is_file($path)) {
                $dir = dirname($path);
                if (! is_dir($dir)) {
                    @mkdir($dir, 0775, true);
                }
                @file_put_contents($path, base64_decode($encoded));
            }
        }
    }
}