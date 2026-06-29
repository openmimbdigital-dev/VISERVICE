<?php

namespace App\Actions\Settings\Equipment;

use App\Models\EquipmentType;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateEquipmentTypeAction
{
    use AsAction;

    /**
     * Crea o actualiza un tipo de equipo y sus negocios asociados.
     *
     * @param  array{name: string, active: bool, business_ids: array<int>}  $data
     */
    public function handle(?int $equipment_type_id, array $data): EquipmentType
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);

        abort_unless(
            auth()->user()->can($equipment_type_id ? 'settings.equipment_types.edit' : 'settings.equipment_types.create'),
            403
        );

        $attributes = [
            'name'        => $data['name'],
            'label'       => static::normalizeLabel($data['name']),
            'active'      => $data['active'],
            'business_id' => null,
            'general'     => true,
        ];

        if ($equipment_type_id) {
            $equipment_type = EquipmentType::findOrFail($equipment_type_id);
            abort_unless($equipment_type->isEditableBy(), 403);

            $equipment_type->update($attributes);
        } else {
            $equipment_type = EquipmentType::create($attributes);
        }

        SyncEquipmentTypeBusinessesAction::run($equipment_type, $data['business_ids']);

        return $equipment_type->fresh(['businesses']);
    }

    public static function normalizeLabel(string $name): string
    {
        $ascii = Str::ascii($name);

        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ascii) ?? '');
    }
}
