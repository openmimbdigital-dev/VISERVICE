<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // El superAdmin siempre tiene acceso
        if ($user->hasRole('superAdmin')) {
            return $next($request);
        }

        $business = $user->business;

        if (! $business || ! $business->hasActiveSubscription()) {
            abort(402, 'Tu suscripción ha expirado o no está activa. Contacta al administrador.');
        }

        return $next($request);
    }
}
