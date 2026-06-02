<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reemplaza el patrón checkSession() manual disperso en todos los controladores.
 * Redirige al login si no hay sesión activa de Laravel Auth.
 */
class Authenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para continuar.');
        }

        if (! Auth::user()->isActive()) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        return $next($request);
    }
}
