<?php

namespace Database\Seeders;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\AttributeEquipmentType;
use App\Models\Business;
use App\Models\EquipmentType;
use Illuminate\Database\Seeder;

class AttributesSeeder extends Seeder
{
    public function run(): void
    {
        $tractocamion = $this->generalEquipmentType('Tractocamión');
        $vehiculo     = $this->generalEquipmentType('Automóvil');
        $camion       = $this->generalEquipmentType('Camión');
        $aire         = $this->generalEquipmentType('Aire acondicionado');
        $transad      = Business::query()->where('name', 'Transportes TRANSAD')->first();
        $carga_rapida = Business::query()->where('name', 'Carga Rápida S.A.S')->first();

        if (! $tractocamion || ! $vehiculo || ! $aire) {
            $this->command->warn('Atributos: faltan tipos de equipo. Ejecuta EquipmentCatalogSeeder primero.');

            return;
        }

        $todos       = [$tractocamion->id, $vehiculo->id, $aire->id];
        $rodados     = [$tractocamion->id, $vehiculo->id];
        $solo_aire   = [$aire->id];
        $carga_pesada = array_filter([
            $tractocamion->id,
            $camion?->id,
        ]);

        $generales = [
            [
                'name'     => 'Referencia',
                'type'     => AttributeType::TEXT,
                'required' => true,
                'types'    => $todos,
            ],
            [
                'name'     => 'Número de serie',
                'type'     => AttributeType::TEXT,
                'required' => false,
                'types'    => $todos,
            ],
            [
                'name'    => 'Color',
                'type'    => AttributeType::COLOR,
                'options' => ['default' => '#1e293b'],
                'types'   => $todos,
            ],
            [
                'name'    => 'Estado operativo',
                'type'    => AttributeType::SELECT,
                'options' => [
                    ['label' => 'Operativo', 'value' => 'Operativo'],
                    ['label' => 'En mantenimiento', 'value' => 'En mantenimiento'],
                    ['label' => 'Fuera de servicio', 'value' => 'Fuera de servicio'],
                ],
                'required' => true,
                'types'      => $todos,
            ],
        ];

        $transad_attributes = [
            [
                'name'     => 'Código de activo TRANSAD',
                'type'     => AttributeType::TEXT,
                'required' => true,
                'types'    => $todos,
            ],
            [
                'name'      => 'Kilometraje',
                'type'      => AttributeType::NUMBER,
                'required'  => false,
                'min_value' => 0,
                'max_value' => 9999999,
                'types'     => $rodados,
            ],
            [
                'name'      => 'Capacidad refrigerada (BTU)',
                'type'      => AttributeType::NUMBER,
                'required'  => false,
                'min_value' => 0,
                'max_value' => 500000,
                'types'     => $solo_aire,
            ],
        ];

        foreach ($generales as $data) {
            $this->seedAttribute($data, general: true);
        }

        if ($transad) {
            foreach ($transad_attributes as $data) {
                $this->seedAttribute($data, general: false, business_id: $transad->id);
            }
        } else {
            $this->command->warn('Atributos: no se encontró Transportes TRANSAD, se omitieron sus atributos.');
        }

        $carga_rapida_attributes = [
            [
                'name'     => 'Código interno Carga Rápida',
                'type'     => AttributeType::TEXT,
                'required' => true,
                'types'    => $carga_pesada ?: $rodados,
            ],
            [
                'name'     => 'Número de manifiesto',
                'type'     => AttributeType::TEXT,
                'required' => false,
                'types'    => $carga_pesada ?: $rodados,
            ],
            [
                'name'      => 'Peso neto autorizado (kg)',
                'type'      => AttributeType::NUMBER,
                'required'  => false,
                'min_value' => 0,
                'max_value' => 50000,
                'types'     => $carga_pesada ?: $rodados,
            ],
            [
                'name'    => 'Tipo de mercancía',
                'type'    => AttributeType::SELECT,
                'options' => [
                    ['label' => 'General', 'value' => 'General'],
                    ['label' => 'Perecedera', 'value' => 'Perecedera'],
                    ['label' => 'Peligrosa', 'value' => 'Peligrosa'],
                    ['label' => 'Frágil', 'value' => 'Frágil'],
                ],
                'required' => false,
                'types'      => $carga_pesada ?: $rodados,
            ],
            [
                'name'    => 'Zona de operación',
                'type'    => AttributeType::RADIO,
                'options' => [
                    ['label' => 'Urbana', 'value' => 'Urbana'],
                    ['label' => 'Regional', 'value' => 'Regional'],
                    ['label' => 'Nacional', 'value' => 'Nacional'],
                ],
                'required' => true,
                'types'    => $carga_pesada ?: $rodados,
            ],
        ];

        if ($carga_rapida) {
            foreach ($carga_rapida_attributes as $data) {
                $this->seedAttribute($data, general: false, business_id: $carga_rapida->id);
            }
        } else {
            $this->command->warn('Atributos: no se encontró Carga Rápida S.A.S, se omitieron sus atributos.');
        }

        $this->command->info('Atributos: generales, TRANSAD y Carga Rápida S.A.S sembrados.');
    }

    private function generalEquipmentType(string $name): ?EquipmentType
    {
        return EquipmentType::query()
            ->whereNull('business_id')
            ->where('general', true)
            ->where('name', $name)
            ->first();
    }

    /**
     * @param  array{
     *     name: string,
     *     type: AttributeType,
     *     required?: bool,
     *     nullable_creation?: bool,
     *     min_value?: int|null,
     *     max_value?: int|null,
     *     options?: array|null,
     *     types: array<int>,
     * }  $data
     */
    private function seedAttribute(array $data, bool $general, ?int $business_id = null): void
    {
        $attribute = Attribute::withTrashed()->firstOrNew([
            'name' => $data['name'],
            'type' => $data['type'],
        ]);

        if ($attribute->trashed()) {
            $attribute->restore();
        }

        $attribute->fill([
            'required'          => $data['required'] ?? false,
            'nullable_creation' => $data['nullable_creation'] ?? false,
            'min_value'         => $data['min_value'] ?? null,
            'max_value'         => $data['max_value'] ?? null,
            'general'           => $general,
            'options'           => $data['options'] ?? null,
            'default'           => false,
        ])->save();

        if ($general) {
            $attribute->businesses()->detach();
        } elseif ($business_id) {
            $attribute->businesses()->sync([$business_id]);
        }

        AttributeEquipmentType::query()
            ->where('attribute_id', $attribute->id)
            ->where('model_type', EquipmentType::class)
            ->delete();

        foreach ($data['types'] as $equipment_type_id) {
            AttributeEquipmentType::create([
                'attribute_id' => $attribute->id,
                'model_id'     => $equipment_type_id,
                'model_type'   => EquipmentType::class,
                'business_id'  => $general ? null : $business_id,
                'general'      => $general,
            ]);
        }
    }
}
