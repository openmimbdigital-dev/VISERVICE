<?php

namespace App\Support;

use App\Enums\AttributeType;
use App\Models\AttributeEquipmentType;
use App\Models\Equipment;
use App\Models\EquipmentType;
use Illuminate\Support\Collection;

class EquipmentTypeAttributeResolver
{
    /**
     * Vínculos tipo-atributo aplicables al negocio (generales + del negocio).
     *
     * @return Collection<int, AttributeEquipmentType>
     */
    public static function linksFor(int $equipment_type_id, int $business_id): Collection
    {
        return AttributeEquipmentType::query()
            ->where('model_type', EquipmentType::class)
            ->where('model_id', $equipment_type_id)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($business_id) {
                $query->where(function ($q) {
                    $q->where('general', true)->whereNull('business_id');
                })->orWhere('business_id', $business_id);
            })
            ->whereHas('attribute', fn ($q) => $q->whereNull('deleted_at')->forAuthUser())
            ->with('attribute')
            ->orderBy('id')
            ->get()
            ->filter(fn (AttributeEquipmentType $link) => $link->attribute !== null)
            ->unique('attribute_id')
            ->values();
    }

    /**
     * @return array<int, mixed>
     */
    public static function valuesForEquipment(Equipment $equipment): array
    {
        $rows = AttributeEquipmentType::query()
            ->where('model_type', Equipment::class)
            ->where('model_id', $equipment->id)
            ->whereNull('deleted_at')
            ->with('attribute')
            ->get();

        $values = [];

        foreach ($rows as $row) {
            if ($row->attribute?->type === AttributeType::CHECKBOX) {
                $values[$row->attribute_id] = json_decode($row->value ?? '[]', true) ?: [];

                continue;
            }

            $values[$row->attribute_id] = $row->value ?? '';
        }

        return $values;
    }
}
