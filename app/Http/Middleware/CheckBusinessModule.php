<?php

namespace App\Http\Middleware;

use App\Support\BusinessModuleAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBusinessModule
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $route_name = $request->route()?->getName();

        if (! BusinessModuleAccess::routeIsAllowedForUser($user, $route_name)) {
            abort(403, 'Este módulo no está habilitado para el negocio activo.');
        }

        return $next($request);
    }
}
