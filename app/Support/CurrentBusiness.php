<?php

namespace App\Support;

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class CurrentBusiness
{
    public const SESSION_KEY = 'current_business_id';

    public static function id(): ?int
    {
        $id = Session::get(self::SESSION_KEY);

        return $id !== null ? (int) $id : null;
    }

    public static function get(): ?Business
    {
        $id = self::id();

        return $id !== null ? Business::query()->find($id) : null;
    }

    public static function set(?int $business_id): void
    {
        if ($business_id === null) {
            Session::forget(self::SESSION_KEY);

            return;
        }

        Session::put(self::SESSION_KEY, $business_id);
    }

    public static function initializeForUser(User $user): void
    {
        if (BusinessModuleAccess::bypassesModuleChecks($user)) {
            self::set(null);

            return;
        }

        $business_ids = $user->businessIds();

        if ($business_ids === []) {
            self::set(null);

            return;
        }

        $current_id = self::id();

        if ($current_id !== null && in_array($current_id, $business_ids, true)) {
            return;
        }

        $primary = $user->primaryBusiness();
        self::set($primary?->id ?? $business_ids[0]);
    }

    public static function switchForUser(User $user, int $business_id): void
    {
        abort_unless($user->belongsToBusiness($business_id), 403);

        self::set($business_id);
    }

    /** @return \Illuminate\Support\Collection<int, Business> */
    public static function selectableBusinessesFor(User $user): \Illuminate\Support\Collection
    {
        if ($user->relationLoaded('businesses')) {
            return $user->businesses;
        }

        return $user->businesses()->orderByDesc('user_business.is_primary')->orderBy('businesses.name')->get();
    }
}
