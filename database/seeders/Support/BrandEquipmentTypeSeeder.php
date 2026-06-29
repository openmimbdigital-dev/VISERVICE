<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BrandEquipmentTypeSeeder
{
    /**
     * Marca → tipos de equipo a los que aplica en el catálogo demo.
     *
     * @var array<string, list<string>>
     */
    private const BRAND_EQUIPMENT_TYPE_MAP = [
        'Toyota'    => ['Automóvil', 'Camión', 'Tractocamión'],
        'Honda'     => ['Automóvil', 'Motocicleta'],
        'Yamaha'    => ['Motocicleta', 'Bicicleta'],
        'Ford'      => ['Automóvil', 'Camión', 'Tractocamión'],
        'Chevrolet' => ['Automóvil', 'Camión'],
        'LG'        => ['Aire acondicionado'],
    ];

    /**
     * Asocia marcas existentes con tipos de equipo en la tabla pivote.
     */
    public static function run(): int
    {
        if (! Schema::hasTable('brands')
            || ! Schema::hasTable('equipment_types')
            || ! Schema::hasTable('brand_equipment_type')) {
            return 0;
        }

        $brand_ids = DB::table('brands')
            ->whereNull('deleted_at')
            ->whereIn('name', array_keys(self::BRAND_EQUIPMENT_TYPE_MAP))
            ->pluck('id', 'name');

        if ($brand_ids->isEmpty()) {
            return 0;
        }

        $type_ids = DB::table('equipment_types')
            ->whereNull('deleted_at')
            ->pluck('id', 'name');

        if ($type_ids->isEmpty()) {
            return 0;
        }

        $now  = now();
        $rows = [];

        foreach (self::BRAND_EQUIPMENT_TYPE_MAP as $brand_name => $type_names) {
            $brand_id = $brand_ids[$brand_name] ?? null;

            if (! $brand_id) {
                continue;
            }

            foreach ($type_names as $type_name) {
                $equipment_type_id = $type_ids[$type_name] ?? null;

                if (! $equipment_type_id) {
                    continue;
                }

                $rows[] = [
                    'brand_id'          => $brand_id,
                    'equipment_type_id' => $equipment_type_id,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }
        }

        if ($rows === []) {
            return 0;
        }

        $inserted = 0;

        foreach (array_chunk($rows, 500) as $chunk) {
            $inserted += DB::table('brand_equipment_type')->insertOrIgnore($chunk);
        }

        return $inserted;
    }
}
