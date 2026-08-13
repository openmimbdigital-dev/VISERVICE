<?php

namespace App\Support\Public;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Cache del feed de la agenda pública de eventos.
 *
 * Usa el store por defecto (CACHE_STORE). Sin tags, para que funcione
 * con database/file hoy y con redis al cambiar el driver.
 */
class PortalEventsFeedCache
{
    public const TTL_SECONDS = 600;

    /**
     * @param  callable(): list<array<string, mixed>>  $callback
     * @return list<array<string, mixed>>
     */
    public static function remember(int $business_id, string $start, string $end, callable $callback, bool $force = false): array
    {
        $key = self::payloadKey($business_id, $start, $end);

        if (! $force) {
            return Cache::remember($key, self::TTL_SECONDS, $callback);
        }

        $payload = $callback();
        Cache::put($key, $payload, self::TTL_SECONDS);

        return $payload;
    }

    public static function flush(int $business_id): void
    {
        Cache::increment(self::versionKey($business_id));
    }

    private static function payloadKey(int $business_id, string $start, string $end): string
    {
        $version = (int) Cache::get(self::versionKey($business_id), 0);
        $range_start = Carbon::parse($start)->toDateString();
        $range_end = Carbon::parse($end)->toDateString();

        return "public_portal.events_feed.{$business_id}.v{$version}.{$range_start}.{$range_end}";
    }

    private static function versionKey(int $business_id): string
    {
        return "public_portal.events_feed.version.{$business_id}";
    }
}
