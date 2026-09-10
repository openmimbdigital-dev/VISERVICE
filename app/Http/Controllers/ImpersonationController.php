<?php

namespace App\Http\Controllers;

use App\Support\Impersonation;
use Illuminate\Http\RedirectResponse;

/**
 * Salida de la suplantación.
 *
 * Va aparte de la pantalla de usuarios y sin exigir permisos: quien está dentro
 * de otra cuenta ya no tiene los del superAdmin, y volver a la propia siempre
 * tiene que ser posible.
 */
class ImpersonationController extends Controller
{
    public function stop(): RedirectResponse
    {
        if (! Impersonation::isActive()) {
            return redirect()->route('dashboard');
        }

        $impersonator = Impersonation::stop();

        if (! $impersonator) {
            // La cuenta original ya no existe: lo seguro es cerrar sesión.
            auth()->logout();

            return redirect()->route('login');
        }

        return redirect()->route('admin.users.index')
            ->with('status', 'Volviste a tu cuenta.');
    }
}
