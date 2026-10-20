<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RevistaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\MetricasController;
use App\Http\Controllers\Admin\MiniaturaController;
use App\Http\Controllers\Admin\FondoController;
use App\Http\Controllers\Api\FavoritoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('revistas/index');
});

Route::get('/revistas', [RevistaController::class, 'index'])->name('revistas.index');
Route::get('/revistas/{revista}', [RevistaController::class, 'show'])->name('revistas.show');

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', function () {
        $revistas = \App\Models\Revista::withCount('paginas')->recientes()->paginate(10);

        $revistasActivas = \App\Models\Revista::where('activo', true)->count();
        $revistasOcultas = \App\Models\Revista::where('activo', false)->count();

        $userStats = null;
        if (auth()->user()->isAdmin()) {
            $userStats = [
                'total' => \App\Models\User::count(),
                'admins' => \App\Models\User::where('role', 'admin')->count(),
                'editores' => \App\Models\User::where('role', '!=', 'admin')->count(),
                'activos' => \App\Models\User::where('activo', true)->count(),
                'inactivos' => \App\Models\User::where('activo', false)->count(),
                'recientes' => \App\Models\User::latest()->take(5)->get(['id', 'name', 'email', 'role', 'created_at']),
            ];
        }

        return view('dashboard', compact('revistas', 'userStats', 'revistasActivas', 'revistasOcultas'));
    })->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        // Métricas y estadísticas (solo administradores)
        Route::get('/metricas', [MetricasController::class, 'index'])->name('metricas.index');
        Route::get('/metricas/data', [MetricasController::class, 'data'])->name('metricas.data');

        // Miniaturas ligeras de portadas y páginas (solo administradores)
        Route::get('/media/miniatura', [MiniaturaController::class, 'mostrar'])->name('media.miniatura');

        Route::get('/revistas/crear', [RevistaController::class, 'create'])->name('revistas.crear');
        Route::post('/revistas', [RevistaController::class, 'store'])->name('revistas.store');
        Route::get('/revistas/{revista}/editar', [RevistaController::class, 'edit'])->name('revistas.edit');
        Route::put('/revistas/{revista}', [RevistaController::class, 'update'])->name('revistas.update');
        Route::delete('/revistas/{revista}', [RevistaController::class, 'destroy'])->name('revistas.destroy');
        Route::post('/revistas/{revista}/reprocesar', [RevistaController::class, 'reprocesar'])->name('revistas.reprocesar');
        Route::patch('/revistas/{revista}/toggle', [RevistaController::class, 'toggleActivo'])->name('revistas.toggle');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/promote', [UserController::class, 'promoteToAdmin'])->name('users.promote');
        Route::patch('/users/{user}/demote', [UserController::class, 'demoteToEditor'])->name('users.demote');
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggleActivo'])->name('users.toggle');

        // Fondos del portal (solo administradores)
        Route::get('/fondos', [FondoController::class, 'index'])->name('fondos.index');
        Route::get('/fondos/crear', [FondoController::class, 'create'])->name('fondos.create');
        Route::post('/fondos', [FondoController::class, 'store'])->name('fondos.store');
        Route::get('/fondos/{fondo}/editar', [FondoController::class, 'edit'])->name('fondos.edit');
        Route::put('/fondos/{fondo}', [FondoController::class, 'update'])->name('fondos.update');
        Route::delete('/fondos/{fondo}', [FondoController::class, 'destroy'])->name('fondos.destroy');
        Route::patch('/fondos/{fondo}/toggle', [FondoController::class, 'toggle'])->name('fondos.toggle');

        // Presets predeterminados (fechas conmemorativas y rangos)
        Route::post('/fondos/presets', [FondoController::class, 'guardarPreset'])->name('fondos.presets.store');
        Route::delete('/fondos/presets/{preset}', [FondoController::class, 'eliminarPreset'])->name('fondos.presets.destroy');
    });

    Route::get('/revista/{revista}/procesando', [RevistaController::class, 'procesando'])->name('revista.procesando');
    Route::get('/revista/{revista}/progreso', [RevistaController::class, 'progreso'])->name('revista.progreso');
    Route::get('/revista/{revista}/estado', [RevistaController::class, 'estado'])->name('revista.estado');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Favoritos (sesión web + autenticado)
Route::middleware(['auth'])->group(function () {
    Route::get('/api/mis-favoritos', [FavoritoController::class, 'index']);
    Route::post('/api/favoritos', [FavoritoController::class, 'store']);
    Route::delete('/api/favoritos/{revistaId}', [FavoritoController::class, 'destroy']);
});

require __DIR__.'/auth.php';