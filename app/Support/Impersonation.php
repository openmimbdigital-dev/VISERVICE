<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * «Entrar como» otro usuario.
 *
 * El superAdmin administra la plataforma, no los comercios: sus pantallas
 * muestran lo de su propio negocio. Cuando necesita ver lo de un cliente —para
 * dar soporte, revisar una OT, entender un cobro— entra como alguien de ese
 * comercio y ve exactamente lo que esa persona ve, con sus permisos y su
 * alcance. Nada de reglas especiales repartidas por el código.
 *
 * En la sesión solo queda el id del usuario original, para poder volver.
 */
class Impersonation
{
    public const SESSION_KEY = 'impersonator_id';

    public static function isActive(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    /** Usuario que inició la suplantación, si hay una en curso. */
    public static function impersonator(): ?User
    {
        $id = Session::get(self::SESSION_KEY);

        return $id ? User::query()->find($id) : null;
    }

    /**
     * ¿Puede este usuario entrar como aquel?
     *
     * Solo el superAdmin, nunca a sí mismo y nunca a otro superAdmin: entrar
     * como un par no aporta nada y enturbia la trazabilidad de quién hizo qué.
     * Tampoco a una cuenta inactiva, que no debería poder operar.
     */
    public static function allows(?User $actor, ?User $target): bool
    {
        if (! $actor || ! $target) {
            return false;
        }

        if (! $actor->hasRole('superAdmin') || ! $actor->can('users.impersonate')) {
            return false;
        }

        if ((int) $actor->id === (int) $target->id) {
            return false;
        }

        return ! $target->hasRole('superAdmin') && (bool) $target->status;
    }

    /**
     * Entra como el usuario indicado, recordando quién lo hizo.
     *
     * El negocio actual se recalcula para el suplantado: si se quedara el del
     * superAdmin, vería su propio comercio con la sesión de otra persona.
     */
    public static function start(User $actor, User $target): void
    {
        abort_unless(self::allows($actor, $target), 403);

        Session::put(self::SESSION_KEY, $actor->id);

        Auth::login($target);
        CurrentBusiness::initializeForUser($target);
    }

    /** Vuelve a la cuenta original. */
    public static function stop(): ?User
    {
        $impersonator = self::impersonator();

        Session::forget(self::SESSION_KEY);

        if (! $impersonator) {
            return null;
        }

        Auth::login($impersonator);
        CurrentBusiness::initializeForUser($impersonator);

        return $impersonator;
    }
}
