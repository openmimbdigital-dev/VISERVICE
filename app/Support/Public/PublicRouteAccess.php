<?php

namespace App\Support\Public;

use App\Models\Business;
use App\Models\OrganizationType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class PublicRouteAccess
{
    /** @return array{label: string, home_route: string} */
    public static function section(): array
    {
        return config('public_routes.section', [
            'label' => 'Participantes',
            'home_route' => 'public.participants.home',
        ]);
    }

    /** @return array<string, array{label: string, route_name: string, sort_order?: int}> */
    public static function items(): array
    {
        $items = config('public_routes.items', []);

        uasort($items, fn (array $a, array $b) => ($a['sort_order'] ?? 100) <=> ($b['sort_order'] ?? 100));

        return $items;
    }

    public static function itemExists(string $route_key): bool
    {
        return array_key_exists($route_key, self::items());
    }

    public static function label(string $route_key): string
    {
        return (string) (self::items()[$route_key]['label'] ?? $route_key);
    }

    public static function organizationTypeAllows(int $organization_type_id, string $route_key): bool
    {
        return DB::table('organization_type_public_routes')
            ->where('organization_type_id', $organization_type_id)
            ->where('route_key', $route_key)
            ->exists();
    }

    public static function businessAllowsItem(Business $business, string $route_key): bool
    {
        if ($business->organization_type_id === null || ! self::itemExists($route_key)) {
            return false;
        }

        return self::organizationTypeAllows((int) $business->organization_type_id, $route_key);
    }

    /** @return list<string> */
    public static function enabledItemKeysForOrganizationType(int $organization_type_id): array
    {
        $known = array_keys(self::items());

        return DB::table('organization_type_public_routes')
            ->where('organization_type_id', $organization_type_id)
            ->whereIn('route_key', $known)
            ->orderBy('route_key')
            ->pluck('route_key')
            ->map(fn ($key) => (string) $key)
            ->all();
    }

    /**
     * @return list<array{key: string, label: string, route_name: string, url: string|null}>
     */
    public static function portalItemsForBusiness(Business $business, string $business_token): array
    {
        if ($business->organization_type_id === null) {
            return [];
        }

        $enabled = self::enabledItemKeysForOrganizationType((int) $business->organization_type_id);
        $items = [];

        foreach (self::items() as $key => $meta) {
            if (! in_array($key, $enabled, true)) {
                continue;
            }

            $route_name = $meta['route_name'] ?? null;

            $items[] = [
                'key' => $key,
                'label' => (string) ($meta['label'] ?? $key),
                'route_name' => (string) $route_name,
                'url' => $route_name && Route::has($route_name)
                    ? route($route_name, ['businessToken' => $business_token])
                    : null,
            ];
        }

        return $items;
    }

    /**
     * Reemplaza los ítems visibles de la sección Participantes para un tipo de organización.
     *
     * @param  list<string>  $item_keys
     */
    public static function syncOrganizationTypeItems(int $organization_type_id, array $item_keys): void
    {
        abort_unless(
            OrganizationType::query()->whereKey($organization_type_id)->where('status', true)->exists(),
            422
        );

        $valid_keys = collect($item_keys)
            ->map(fn ($key) => (string) $key)
            ->filter(fn (string $key) => self::itemExists($key))
            ->unique()
            ->values()
            ->all();

        $known_item_keys = array_keys(self::items());

        DB::transaction(function () use ($organization_type_id, $valid_keys, $known_item_keys) {
            DB::table('organization_type_public_routes')
                ->where('organization_type_id', $organization_type_id)
                ->whereIn('route_key', $known_item_keys)
                ->delete();

            $now = now();

            $rows = collect($valid_keys)->map(fn (string $key) => [
                'organization_type_id' => $organization_type_id,
                'route_key' => $key,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            if ($rows !== []) {
                DB::table('organization_type_public_routes')->insert($rows);
            }
        });
    }
}
