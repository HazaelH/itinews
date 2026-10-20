<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Revista;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        // Portadas de revistas activas para el carrusel de la pantalla de acceso.
        $portadas = Revista::query()
            ->activas()
            ->whereNotNull('ruta_portada')
            ->latest()
            ->take(10)
            ->pluck('ruta_portada')
            ->map(fn ($ruta) => asset('storage/' . ltrim($ruta, '/')))
            ->filter()
            ->values()
            ->all();

        return view('auth.login', compact('portadas'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('login');
    }
}
