<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
// IMPORTANTE: Importa tu middleware aquí arriba
use App\Http\Middleware\SessionTimeout; 

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // AGREGA ESTO: Registramos el middleware en el grupo 'web'
        $middleware->web(append: [
            SessionTimeout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// El DocumentRoot de Apache es public_html/, por lo que alineamos public_path()
// para que @vite lea el manifest correcto (public_html/build/manifest.json).
$app->usePublicPath(dirname(__DIR__).'/public_html');

return $app;