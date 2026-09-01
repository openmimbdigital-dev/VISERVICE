<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $allowedRoles = explode('|', $roles);

        if (!auth()->user()->hasAnyRole($allowedRoles)) {
            abort(403, 'No tienes permisos para acceder a esta funcionalidad.');
        }

        return $next($request);
    }
}
