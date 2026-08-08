<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/public.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Railway/Render terminan HTTPS delante del contenedor (HTTP interno).
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'permission'          => \App\Http\Middleware\CheckPermission::class,
            'role'                => \App\Http\Middleware\CheckRole::class,
            'role_or_permission'  => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'active.subscription' => \App\Http\Middleware\CheckActiveSubscription::class,
            'ensure.business'     => \App\Http\Middleware\EnsureCurrentBusiness::class,
            'business.module'     => \App\Http\Middleware\CheckBusinessModule::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Vistas branded en resources/views/errors/* (activar/desactivar con APP_CUSTOM_ERROR_PAGES).
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (config('app.custom_error_pages', true)) {
                return null;
            }

            // Desactivado: forzar detalle de excepción en lugar de la vista de error.
            config(['app.debug' => true]);

            return null;
        });
    })->create();
