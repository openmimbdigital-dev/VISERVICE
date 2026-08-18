<?php

namespace App\Support\Public;

use App\Models\Business;

class ParticipantsPortalAuthorization
{
    public const EVENTS_ITEM = 'public.participants.events';

    public static function businessFromToken(string $business_token): ?Business
    {
        return BusinessPublicId::resolveBusiness($business_token);
    }

    public static function requireBusiness(string $business_token, ?string $route_item = null): Business
    {
        $business = self::businessFromToken($business_token);

        abort_unless($business !== null, 404);

        if ($route_item !== null) {
            abort_unless(PublicRouteAccess::businessAllowsItem($business, $route_item), 404);
        }

        return $business;
    }
}
