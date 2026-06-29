<?php

namespace App\Actions\Settings\Equipment;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\AttributeProductType;
use App\Models\EquipmentType;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateAttributeAction
{
    use AsAction;

    /**
     * Crea o actualiza un atributo, sus negocios y vínculos con tipos de equipo.
     *
     * @param  array{
     *     name: string,
     *     type: string,
     *     options: array<int, array{label: string, value: string}>|null,
     *     equipment_types: array<int>,
     *     required: bool,
     *     nullable_creation: bool,
     *     min_value: float|null,
     *     max_value: float|null,
     *     general: bool,
     *     business_ids: array<int>,
     * }  $data
     */
    public function handle(?int $attribute_id, array $data): Attribute
    {
        abort_unless(
            auth()->user()->can($attribute_id ? 'settings.attributes.edit' : 'settings.attributes.create'),
            403
        );

        $is_super_admin = auth()->user()->hasRole('superAdmin');
        $general        = $is_super_admin ? $data['general'] : false;

        $attributes = [
            'name'              => $data['name'],
            'type'              => AttributeType::from($data['type']),
            'required'          => $data['required'],
            'nullable_creation' => $data['nullable_creation'],
            'min_value'         => $data['min_value'],
            'max_value'         => $data['max_value'],
            'general'           => $general,
            'options'           => $this->formatOptions($data['type'], $data['options'] ?? []),
        ];

        if ($attribute_id) {
            $attribute = Attribute::query()->findOrFail($attribute_id);

            abort_unless($attribute->isAccessibleBy(), 403);

            $attribute->update($attributes);
        } else {
            $attribute = Attribute::create($attributes);
        }

        SyncAttributeBusinessesAction::run(
            $attribute,
            $data['business_ids'] ?? [],
            $general
        );

        $this->syncEquipmentTypeRelations($attribute, $data['equipment_types']);

        return $attribute->fresh(['businesses']);
    }

    /**
     * @param  array<int, array{label?: string, value?: string}>  $options
     * @return array<int, array{label: string, value: string}>|null
     */
    protected function formatOptions(string $type, array $options): ?array
    {
        if (! in_array($type, ['select', 'radio', 'checkbox'], true) || empty($options)) {
            return null;
        }

        $formatted = [];

        foreach ($options as $option) {
            $label = trim((string) ($option['label'] ?? ''));

            if ($label !== '') {
                $formatted[] = ['label' => $label, 'value' => $label];
            }
        }

        return $formatted ?: null;
    }

    /**
     * @param  array<int>  $equipment_type_ids
     */
    protected function syncEquipmentTypeRelations(Attribute $attribute, array $equipment_type_ids): void
    {
        AttributeProductType::query()
            ->where('attribute_id', $attribute->id)
            ->where('model_type', EquipmentType::class)
            ->delete();

        $equipment_type_ids = collect($equipment_type_ids)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($attribute->general) {
            $visible_ids = EquipmentType::query()
                ->where('active', true)
                ->where('general', true)
                ->pluck('id')
                ->all();

            foreach ($equipment_type_ids as $equipment_type_id) {
                if (! in_array($equipment_type_id, $visible_ids, true)) {
                    continue;
                }

                AttributeProductType::create([
                    'attribute_id' => $attribute->id,
                    'model_id'     => $equipment_type_id,
                    'model_type'   => EquipmentType::class,
                    'business_id'  => null,
                    'general'      => true,
                ]);
            }

            return;
        }

        $business_ids = $attribute->businesses()->pluck('businesses.id')->all();

        foreach ($business_ids as $business_id) {
            $visible_ids = EquipmentType::query()
                ->where('active', true)
                ->where(function ($q) use ($business_id) {
                    $q->where('general', true)
                        ->orWhere('business_id', $business_id);
                })
                ->pluck('id')
                ->all();

            foreach ($equipment_type_ids as $equipment_type_id) {
                if (! in_array($equipment_type_id, $visible_ids, true)) {
                    continue;
                }

                AttributeProductType::create([
                    'attribute_id' => $attribute->id,
                    'model_id'     => $equipment_type_id,
                    'model_type'   => EquipmentType::class,
                    'business_id'  => $business_id,
                    'general'      => false,
                ]);
            }
        }
    }
}
