<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
     *
     * En Laravel 12 el enrutamiento (web/api/console) se registra en
     * bootstrap/app.php mediante withRouting(), por lo que aquí solo
     * definimos configuraciones de servicios como los rate limiters.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Regla de contraseñas para registro, restablecer y alta/edición de
        // usuarios (coincide con el medidor de fuerza del portal).
        Password::defaults(function () {
            return Password::min(8)->letters()->numbers();
        });
    }
}
