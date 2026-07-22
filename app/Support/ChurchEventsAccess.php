<?php

namespace App\Support;

use App\Models\User;

class ChurchEventsAccess
{
    public static function allowed(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('superAdmin')) {
            return true;
        }

        return $user->business?->organization_type?->label === 'iglesia';
    }

    public static function authorize(?User $user = null): void
    {
        abort_unless(static::allowed($user), 403);
    }
}
