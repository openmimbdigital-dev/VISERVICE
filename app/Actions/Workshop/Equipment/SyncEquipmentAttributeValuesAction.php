<?php

namespace App\Actions\Workshop\Equipment;

use App\Enums\AttributeType;
use App\Models\AttributeEquipmentType;
use App\Models\Equipment;
use App\Support\EquipmentTypeAttributeResolver;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncEquipmentAttributeValuesAction
{
    use AsAction;

    /**
     * Persiste valores de atributos dinámicos del equipo en attribute_equipment_types.
     *
     * @param  array<int, mixed>  $attribute_values
     */
    public function handle(
        Equipment $equipment,
        int $business_id,
        int $equipment_type_id,
        array $attribute_values,
        bool $is_editing = false
    ): void {
        $links = EquipmentTypeAttributeResolver::linksFor($equipment_type_id, $business_id);

        if ($is_editing) {
            $this->updateExisting($equipment, $business_id, $links, $attribute_values);

            return;
        }

        $this->createForNewEquipment($equipment, $business_id, $links, $attribute_values);
    }

    /**
     * @param  Collection<int, AttributeEquipmentType>  $links
     * @param  array<int, mixed>  $attribute_values
     */
    protected function createForNewEquipment(
        Equipment $equipment,
        int $business_id,
        Collection $links,
        array $attribute_values
    ): void {
        foreach ($links as $link) {
            $attribute = $link->attribute;
            $raw       = $attribute_values[$attribute->id] ?? null;

            if ($this->isEmpty($attribute->type, $raw)) {
                continue;
            }

            AttributeEquipmentType::create([
                'business_id'  => $business_id,
                'model_id'     => $equipment->id,
                'model_type'   => Equipment::class,
                'attribute_id' => $attribute->id,
                'general'      => (bool) $attribute->general,
                'value'        => $this->serializeValue($attribute->type, $raw),
            ]);
        }
    }

    /**
     * @param  Collection<int, AttributeEquipmentType>  $links
     * @param  array<int, mixed>  $attribute_values
     */
    protected function updateExisting(
        Equipment $equipment,
        int $business_id,
        Collection $links,
        array $attribute_values
    ): void {
        $existing_rows = AttributeEquipmentType::query()
            ->where('model_type', Equipment::class)
            ->where('model_id', $equipment->id)
            ->get()
            ->keyBy('attribute_id');

        foreach ($links as $link) {
            $attribute = $link->attribute;
            $existing  = $existing_rows->get($attribute->id);

            if (! $existing) {
                continue;
            }

            $raw = $attribute_values[$attribute->id] ?? null;

            $existing->update([
                'business_id' => $business_id,
                'general'     => (bool) $attribute->general,
                'value'       => $this->isEmpty($attribute->type, $raw)
                    ? null
                    : $this->serializeValue($attribute->type, $raw),
            ]);
        }
    }

    protected function isEmpty(AttributeType $type, mixed $value): bool
    {
        if ($type === AttributeType::CHECKBOX) {
            return ! is_array($value) || $value === [];
        }

        return $value === null || $value === '';
    }

    protected function serializeValue(AttributeType $type, mixed $value): string
    {
        if ($type === AttributeType::CHECKBOX) {
            return json_encode(array_values(is_array($value) ? $value : []), JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }
}
