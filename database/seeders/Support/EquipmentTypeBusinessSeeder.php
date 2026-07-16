<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EquipmentTypeBusinessSeeder
{
    private const TRANSAD_SLUG = 'transportes-transad';

    private const TRANSAD_EXCLUSIVE_TYPE = 'Tractocamión';

    /**
     * Tipos que solo aplican a un negocio concreto (slug).
     *
     * @var array<string, string>
     */
    private const TYPE_BUSINESS_OVERRIDES = [
        self::TRANSAD_EXCLUSIVE_TYPE => self::TRANSAD_SLUG,
    ];

    /**
     * Negocios que solo pueden tener tipos explícitos en la pivote (sin heredar el catálogo general).
     *
     * @var array<string, list<string>>
     */
    private const BUSINESS_EXCLUSIVE_TYPES = [
        self::TRANSAD_SLUG => [self::TRANSAD_EXCLUSIVE_TYPE],
    ];

    /**
     * Asocia tipos de equipo existentes con negocios en la tabla pivote.
     *
     * - Tipo con business_id → asocia ese negocio.
     * - Tipo en TYPE_BUSINESS_OVERRIDES → solo el negocio indicado.
     * - Tipo general → todos los negocios activos, excepto negocios con catálogo exclusivo.
     */
    public static function run(): int
    {
        if (! Schema::hasTable('equipment_types')
            || ! Schema::hasTable('businesses')
            || ! Schema::hasTable('equipment_type_business')) {
            return 0;
        }

        self::purgeExclusiveBusinessExtras();

        $now = now();

        $active_businesses = DB::table('businesses')
            ->where('status', true)
            ->whereNull('deleted_at')
            ->get(['id', 'slug']);

        $exclusive_business_ids = self::resolveExclusiveBusinessIds();

        $equipment_types = DB::table('equipment_types')
            ->whereNull('deleted_at')
            ->get(['id', 'business_id', 'general', 'name']);

        $rows = [];

        foreach ($equipment_types as $type) {
            if ($type->business_id) {
                $rows[] = [
                    'equipment_type_id' => $type->id,
                    'business_id'       => $type->business_id,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];

                continue;
            }

            $override_slug = self::TYPE_BUSINESS_OVERRIDES[$type->name] ?? null;

            if ($override_slug) {
                $business_id = $active_businesses
                    ->firstWhere('slug', $override_slug)
                    ?->id;

                if ($business_id) {
                    DB::table('equipment_type_business')
                        ->where('equipment_type_id', $type->id)
                        ->delete();

                    $rows[] = [
                        'equipment_type_id' => $type->id,
                        'business_id'       => $business_id,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                }

                continue;
            }

            if ($type->general && $active_businesses->isNotEmpty()) {
                foreach ($active_businesses as $business) {
                    if (isset($exclusive_business_ids[$business->id])) {
                        continue;
                    }

                    $rows[] = [
                        'equipment_type_id' => $type->id,
                        'business_id'       => $business->id,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                }
            }
        }

        if ($rows === []) {
            return 0;
        }

        $inserted = 0;

        foreach (array_chunk($rows, 500) as $chunk) {
            $inserted += DB::table('equipment_type_business')->insertOrIgnore($chunk);
        }

        self::purgeExclusiveBusinessExtras();

        return $inserted;
    }

    /**
     * Elimina asociaciones de negocios exclusivos que no correspondan a sus tipos permitidos.
     */
    private static function purgeExclusiveBusinessExtras(): void
    {
        foreach (self::BUSINESS_EXCLUSIVE_TYPES as $slug => $allowed_type_names) {
            $business_id = DB::table('businesses')
                ->where('slug', $slug)
                ->whereNull('deleted_at')
                ->value('id');

            if (! $business_id) {
                continue;
            }

            $allowed_type_ids = DB::table('equipment_types')
                ->whereIn('name', $allowed_type_names)
                ->whereNull('deleted_at')
                ->pluck('id');

            DB::table('equipment_type_business')
                ->where('business_id', $business_id)
                ->when(
                    $allowed_type_ids->isNotEmpty(),
                    fn ($query) => $query->whereNotIn('equipment_type_id', $allowed_type_ids),
                    fn ($query) => $query
                )
                ->delete();
        }
    }

    /**
     * @return array<int, true>
     */
    private static function resolveExclusiveBusinessIds(): array
    {
        $slugs = array_keys(self::BUSINESS_EXCLUSIVE_TYPES);

        if ($slugs === []) {
            return [];
        }

        return DB::table('businesses')
            ->whereIn('slug', $slugs)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [(int) $id => true])
            ->all();
    }
}
