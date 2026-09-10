<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Ejecuta algo en nombre del superAdmin de la plataforma.
 *
 * Hay caminos que confirman un pago sin que haya nadie en sesión: la
 * notificación de Bold y la tarea que consulta los pagos en la pasarela. Las
 * acciones que crean la OT y su factura piden permisos y dejan autoría, así que
 * se corren como el superAdmin de la plataforma.
 *
 * Si ya hay alguien autenticado —el superAdmin confirmando una transferencia a
 * mano— se respeta: la autoría debe quedar a su nombre, no a nombre del sistema.
 */
class SystemActor
{
    public static function run(callable $callback): mixed
    {
        if (Auth::check()) {
            return $callback();
        }

        $system_user = self::user();

        if (! $system_user) {
            return $callback();
        }

        Auth::setUser($system_user);

        try {
            return $callback();
        } finally {
            Auth::forgetUser();
        }
    }

    public static function user(): ?User
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'superAdmin'))
            ->orderBy('id')
            ->first();
    }
}
