<?php

namespace App\Http\Middleware;

use App\Support\CurrentBusiness;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentBusiness
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            CurrentBusiness::initializeForUser(auth()->user());
        }

        return $next($request);
    }
}
