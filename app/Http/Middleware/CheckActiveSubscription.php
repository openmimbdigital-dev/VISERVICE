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

        if (! $business) {
            abort(402, 'No tienes un comercio asociado. Contacta al administrador.');
        }

        // Suscripción pendiente de confirmación de pago
        $pendingSubscription = $business->subscriptions()->where('status', 'pending')->latest()->first();
        if ($pendingSubscription && ! $business->hasActiveSubscription()) {
            return redirect()->route('pending-activation');
        }

        if (! $business->hasActiveSubscription()) {
            abort(402, 'Tu suscripción ha expirado o no está activa. Contacta al administrador.');
        }

        return $next($request);
    }
}
