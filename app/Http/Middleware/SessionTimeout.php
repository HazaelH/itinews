<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        // Si la cuenta fue desactivada, cerrar la sesión de inmediato.
        if (! (Auth::user()->estaActivo() ?? true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cuenta desactivada'], 401);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
        }

        $maxIdleTime = 20 * 60; 

        $lastActivity = Session::get('last_activity');

        // Si existe registro y ya pasó el tiempo límite
        if ($lastActivity && (time() - $lastActivity > $maxIdleTime)) {
            
            // Si es una petición AJAX (JS), devolver error JSON
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesión expirada'], 401);
            }

            // 1. Cerrar sesión
            Auth::logout();
            
            // 2. Invalidar sesión y regenerar token
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // 3. Redirigir al login con el mensaje
            return redirect()->route('login')
                ->with('error', 'Tu sesión se ha cerrado por inactividad (20 min). Por favor ingresa de nuevo.');
        }

        // Actualizar la hora de la última actividad
        Session::put('last_activity', time());

        return $next($request);
    }
}