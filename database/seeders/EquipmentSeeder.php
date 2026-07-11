<?php

namespace Database\Seeders;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\AttributeEquipmentType;
use App\Models\Brand;
use App\Models\Business;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\EquipmentModel;
use App\Models\EquipmentType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class EquipmentSeeder extends Seeder
{
    private const TRANSAD_SLUG = 'transportes-transad';

    public function run(): void
    {
        $tractocamion_type = EquipmentType::query()
            ->where('name', 'Tractocamión')
            ->whereNull('deleted_at')
            ->first();

        $aire_type = EquipmentType::query()
            ->where('name', 'Aire acondicionado')
            ->whereNull('deleted_at')
            ->first();

        $transad = Business::query()->where('slug', self::TRANSAD_SLUG)->first();

        if (! $tractocamion_type || ! $aire_type || ! $transad) {
            $this->command->warn('Equipos: faltan tipos o negocio TRANSAD. Ejecuta catálogo, atributos y negocios primero.');

            return;
        }

        $tracto_brand = Brand::query()->where('name', 'Ford')->whereNull('deleted_at')->first();
        $tracto_model = $tracto_brand
            ? EquipmentModel::query()->where('brand_id', $tracto_brand->id)->where('name', 'Ranger')->first()
            : null;

        $aire_brand = Brand::query()->where('name', 'LG')->whereNull('deleted_at')->first();
        $aire_model = $aire_brand
            ? EquipmentModel::query()->where('brand_id', $aire_brand->id)->orderBy('id')->first()
            : null;

        if (! $tracto_brand || ! $tracto_model || ! $aire_brand || ! $aire_model) {
            $this->command->warn('Equipos: faltan marcas/modelos Ford Ranger o LG. Ejecuta EquipmentCatalogSeeder.');

            return;
        }

        $other_businesses = $this->resolveOtherBusinessesWithClients();

        if ($other_businesses->isEmpty()) {
            $this->command->warn('Equipos: no hay otros negocios con clientes activos para repartir equipos.');

            return;
        }

        $created_by = User::query()->where('username', 'superadmin')->value('id');
        $created    = 0;
        $sequence   = 1;

        /** @var list<array{slug: string, type: string, count: int}> */
        $plan = [
            ['slug' => self::TRANSAD_SLUG, 'type' => 'tracto', 'count' => 10],
        ];

        $tracto_per_business = (int) floor(10 / max($other_businesses->count(), 1));
        $aire_per_business   = (int) floor(20 / max($other_businesses->count(), 1));
        $tracto_remainder    = 10 - ($tracto_per_business * $other_businesses->count());
        $aire_remainder      = 20 - ($aire_per_business * $other_businesses->count());

        foreach ($other_businesses->values() as $index => $business) {
            $extra_tracto = $index < $tracto_remainder ? 1 : 0;
            $extra_aire   = $index < $aire_remainder ? 1 : 0;

            $plan[] = ['slug' => $business->slug, 'type' => 'tracto', 'count' => $tracto_per_business + $extra_tracto];
            $plan[] = ['slug' => $business->slug, 'type' => 'aire', 'count' => $aire_per_business + $extra_aire];
        }

        foreach ($plan as $entry) {
            $business = Business::query()->where('slug', $entry['slug'])->first();

            if (! $business) {
                continue;
            }

            $equipment_type = $entry['type'] === 'tracto' ? $tractocamion_type : $aire_type;
            $brand          = $entry['type'] === 'tracto' ? $tracto_brand : $aire_brand;
            $model          = $entry['type'] === 'tracto' ? $tracto_model : $aire_model;
            $prefix         = $entry['type'] === 'tracto' ? 'SEED-TRA' : 'SEED-AIR';

            for ($i = 1; $i <= $entry['count']; $i++) {
                $plate = sprintf('%s-%s-%02d', $prefix, strtoupper(substr($entry['slug'], 0, 3)), $i);

                if ($this->seedEquipment(
                    business: $business,
                    equipment_type: $equipment_type,
                    brand: $brand,
                    model: $model,
                    name: ($entry['type'] === 'tracto' ? 'Tractocamión ' : 'Aire acondicionado ') . sprintf('%02d', $i),
                    plate: $plate,
                    year: $entry['type'] === 'tracto' ? 2018 + ($sequence % 7) : 2020 + ($sequence % 6),
                    sequence: $sequence,
                    created_by: $created_by
                )) {
                    $created++;
                }

                $sequence++;
            }
        }

        $this->command->info("Equipos demo: {$created} registros creados o actualizados (40 entre tractocamión y aire acondicionado).");
    }

    /** @return Collection<int, Business> */
    private function resolveOtherBusinessesWithClients(): Collection
    {
        $business_ids = Client::query()
            ->where('status', true)
            ->distinct()
            ->pluck('business_id');

        return Business::query()
            ->where('slug', '!=', self::TRANSAD_SLUG)
            ->where('status', true)
            ->whereIn('id', $business_ids)
            ->orderBy('slug')
            ->get();
    }

    private function seedEquipment(
        Business $business,
        EquipmentType $equipment_type,
        Brand $brand,
        EquipmentModel $model,
        string $name,
        string $plate,
        int $year,
        int $sequence,
        ?int $created_by
    ): bool {
        $clients = Client::query()
            ->where('business_id', $business->id)
            ->where('status', true)
            ->orderBy('id')
            ->get(['id', 'name']);

        if ($clients->isEmpty()) {
            $this->command->warn("Equipos: sin clientes activos para {$business->slug}, se omite {$plate}.");

            return false;
        }

        $client = $clients[($sequence - 1) % $clients->count()];

        $equipment = Equipment::withTrashed()->updateOrCreate(
            [
                'business_id' => $business->id,
                'plate'       => $plate,
            ],
            [
                'client_id'           => $client->id,
                'client_name'         => $client->name,
                'brand_id'            => $brand->id,
                'model_id'            => $model->id,
                'equipment_type_id'   => $equipment_type->id,
                'equipment_type_name' => $equipment_type->name,
                'name'                => $name,
                'brand_name'          => $brand->name,
                'model_name'          => $model->name,
                'year'                => $year,
                'status'              => true,
                'notes'               => 'Equipo de demostración generado por seeder.',
                'created_by'          => $created_by,
                'deleted_at'          => null,
            ]
        );

        if ($equipment->trashed()) {
            $equipment->restore();
        }

        $this->seedAttributeValues($equipment, $business, $equipment_type, $sequence);

        return true;
    }

    private function seedAttributeValues(
        Equipment $equipment,
        Business $business,
        EquipmentType $equipment_type,
        int $sequence
    ): void {
        $links = AttributeEquipmentType::query()
            ->where('model_type', EquipmentType::class)
            ->where('model_id', $equipment_type->id)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($business) {
                $query->where(function ($q) {
                    $q->where('general', true)->whereNull('business_id');
                })->orWhere('business_id', $business->id);
            })
            ->with('attribute')
            ->get()
            ->filter(fn (AttributeEquipmentType $link) => $link->attribute !== null)
            ->unique('attribute_id');

        AttributeEquipmentType::query()
            ->where('model_type', Equipment::class)
            ->where('model_id', $equipment->id)
            ->forceDelete();

        foreach ($links as $link) {
            $attribute = $link->attribute;
            $value     = $this->buildAttributeValue($attribute, $equipment->plate, $sequence, $business->slug);

            if ($this->isEmptyValue($attribute, $value)) {
                if ($attribute->required && ! $attribute->nullable_creation) {
                    $value = $this->fallbackRequiredValue($attribute, $equipment->plate, $sequence);
                } else {
                    continue;
                }
            }

            AttributeEquipmentType::create([
                'business_id'  => $business->id,
                'model_id'     => $equipment->id,
                'model_type'   => Equipment::class,
                'attribute_id' => $attribute->id,
                'general'      => (bool) $attribute->general,
                'value'        => $this->serializeValue($attribute->type, $value),
            ]);
        }
    }

    private function buildAttributeValue(Attribute $attribute, string $plate, int $sequence, string $business_slug): mixed
    {
        return match ($attribute->name) {
            'Referencia'                    => "REF-{$plate}",
            'Número de serie'               => sprintf('SN-%04d-%s', $sequence, strtoupper(substr($business_slug, 0, 3))),
            'Color'                         => (string) ($attribute->options['default'] ?? '#1e40af'),
            'Estado operativo'              => 'Operativo',
            'Código de activo TRANSAD'      => $business_slug === self::TRANSAD_SLUG ? "TA-{$sequence}" : null,
            'Kilometraje'                   => (string) (80000 + ($sequence * 17341) % 900000),
            'Capacidad refrigerada (BTU)'   => (string) [9000, 12000, 18000, 24000][$sequence % 4],
            'Código interno Carga Rápida'   => $business_slug === 'carga-rapida-sas' ? "CR-{$sequence}" : null,
            'Número de manifiesto'          => "MAN-{$sequence}",
            'Peso neto autorizado (kg)'     => (string) (5000 + ($sequence * 997) % 30000),
            'Tipo de mercancía'             => 'General',
            'Zona de operación'             => ['Urbana', 'Regional', 'Nacional'][$sequence % 3],
            default                         => $this->genericAttributeValue($attribute, $sequence),
        };
    }

    private function genericAttributeValue(Attribute $attribute, int $sequence): mixed
    {
        return match ($attribute->type) {
            AttributeType::TEXT, AttributeType::TEXTAREA => "{$attribute->name}-{$sequence}",
            AttributeType::NUMBER => (string) (100 + $sequence * 17),
            AttributeType::SELECT, AttributeType::RADIO => $attribute->options[0]['value'] ?? $attribute->options[0]['label'] ?? '—',
            AttributeType::COLOR  => '#6366f1',
            AttributeType::CHECKBOX => [$attribute->options[0]['value'] ?? 'Opción 1'],
            default => null,
        };
    }

    private function fallbackRequiredValue(Attribute $attribute, string $plate, int $sequence): mixed
    {
        return match ($attribute->type) {
            AttributeType::NUMBER => '0',
            AttributeType::COLOR  => '#000000',
            default               => "{$attribute->name}-{$plate}-{$sequence}",
        };
    }

    private function isEmptyValue(Attribute $attribute, mixed $value): bool
    {
        if ($attribute->type === AttributeType::CHECKBOX) {
            return ! is_array($value) || $value === [];
        }

        return $value === null || $value === '';
    }

    private function serializeValue(AttributeType $type, mixed $value): string
    {
        if ($type === AttributeType::CHECKBOX) {
            return json_encode(array_values(is_array($value) ? $value : []), JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }
}
