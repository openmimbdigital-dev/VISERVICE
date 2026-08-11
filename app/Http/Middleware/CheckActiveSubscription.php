<?php

namespace App\Http\Middleware;

use App\Support\CurrentBusiness;
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

        if ($user->hasRole('superAdmin')) {
            return $next($request);
        }

        $business = CurrentBusiness::get() ?? $user->primaryBusiness();

        if (! $business) {
            abort(402, 'No tienes un comercio asociado. Contacta al administrador.');
        }

        $pending_subscription = $business->subscriptions()->where('status', 'pending')->latest()->first();

        if ($pending_subscription && ! $business->hasActiveSubscription()) {
            return redirect()->route('pending-activation');
        }

        if (! $business->hasActiveSubscription()) {
            abort(402, 'Tu suscripción ha expirado o no está activa. Contacta al administrador.');
        }

        return $next($request);
    }
}
