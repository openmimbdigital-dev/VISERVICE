<?php

namespace App\Actions\Settings\Equipment;

use App\Models\EquipmentType;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateEquipmentTypeAction
{
    use AsAction;

    /**
     * Crea o actualiza un tipo de equipo.
     *
     * @param  int|null  $equipment_type_id
     * @param  array     $data  name, active
     */
    public function handle(?int $equipment_type_id, array $data): EquipmentType
    {
        abort_unless(auth()->user()->can('settings.edit'), 403);

        $user = auth()->user();
        $is_super_admin = $user->hasRole('superAdmin');

        $attributes = [
            'name'   => $data['name'],
            'label'  => static::normalizeLabel($data['name']),
            'active' => $data['active'],
        ];

        if ($equipment_type_id) {
            $equipment_type = EquipmentType::findOrFail($equipment_type_id);
            abort_unless($equipment_type->isEditableBy($user), 403);

            $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
            $attributes['general']     = $is_super_admin;

            $equipment_type->update($attributes);

            return $equipment_type->fresh();
        }

        $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
        $attributes['general']     = $is_super_admin;

        return EquipmentType::create($attributes);
    }

    public static function normalizeLabel(string $name): string
    {
        $ascii = Str::ascii($name);

        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ascii) ?? '');
    }
}
