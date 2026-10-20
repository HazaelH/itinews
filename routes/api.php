<?php

use App\Http\Controllers\Api\MetricasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

/*
|--------------------------------------------------------------------------
| Métricas del portal (públicas y anónimas)
|--------------------------------------------------------------------------
| Sin autenticación ni CSRF: los visitantes del portal no inician sesión.
| Limitadas por throttle para evitar abusos.
*/
Route::prefix('metricas')->middleware('throttle:120,1')->group(function () {
    Route::post('/portal', [MetricasController::class, 'portal']);
    Route::post('/revista', [MetricasController::class, 'revista']);
    Route::post('/pagina', [MetricasController::class, 'pagina']);
    Route::post('/cerrar', [MetricasController::class, 'cerrar']);
});
