<?php

namespace App\Actions\Settings\Equipment;

use App\Models\Brand;
use App\Models\EquipmentModel;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateEquipmentModelAction
{
    use AsAction;

    /**
     * Crea o actualiza un modelo de equipo.
     *
     * @param  int|null  $equipment_model_id
     * @param  array     $data  brand_id, name, active
     */
    public function handle(?int $equipment_model_id, array $data): EquipmentModel
    {
        abort_unless(
            auth()->user()->can($equipment_model_id ? 'settings.model_equipment.edit' : 'settings.model_equipment.create'),
            403
        );

        $user = auth()->user();
        $is_super_admin = $user->hasRole('superAdmin');

        $brand = Brand::query()
            ->visibleToUser($user)
            ->whereKey($data['brand_id'])
            ->firstOrFail();

        $attributes = [
            'brand_id' => $brand->id,
            'name'     => $data['name'],
            'label'    => static::normalizeLabel($data['name']),
            'active'   => $data['active'],
        ];

        if ($equipment_model_id) {
            $equipment_model = EquipmentModel::query()->visibleToUser($user)->findOrFail($equipment_model_id);
            abort_unless($equipment_model->isEditableBy($user), 403);

            $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
            $attributes['general']     = $is_super_admin;

            $equipment_model->update($attributes);

            return $equipment_model->fresh();
        }

        $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
        $attributes['general']     = $is_super_admin;

        return EquipmentModel::create($attributes);
    }

    public static function normalizeLabel(string $name): string
    {
        $ascii = Str::ascii($name);

        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ascii) ?? '');
    }
}
