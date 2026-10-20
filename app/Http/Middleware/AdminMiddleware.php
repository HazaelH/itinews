<?php

namespace App\Http\Middleware; 

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos de administrador para esta acción.');
        }

        return $next($request);
    }
}