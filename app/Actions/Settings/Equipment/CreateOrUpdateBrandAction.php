<?php

namespace App\Actions\Settings\Equipment;

use App\Models\Brand;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateBrandAction
{
    use AsAction;

    /**
     * Crea o actualiza una marca de equipo.
     *
     * @param  array{name: string, active: bool, equipment_type_ids: array<int>}  $data
     */
    public function handle(?int $brand_id, array $data): Brand
    {
        abort_unless(
            auth()->user()->can($brand_id ? 'settings.brands.edit' : 'settings.brands.create'),
            403
        );

        $user = auth()->user();
        $is_super_admin = $user->hasRole('superAdmin');

        $attributes = [
            'name'   => $data['name'],
            'label'  => static::normalizeLabel($data['name']),
            'active' => $data['active'],
        ];

        if ($brand_id) {
            $brand = Brand::query()->visibleToUser($user)->findOrFail($brand_id);
            abort_unless($brand->isEditableBy($user), 403);

            $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
            $attributes['general']     = $is_super_admin;

            $brand->update($attributes);

            SyncBrandEquipmentTypesAction::run($brand, $data['equipment_type_ids']);

            return $brand->fresh(['equipmentTypes']);
        }

        $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
        $attributes['general']     = $is_super_admin;

        $brand = Brand::create($attributes);

        SyncBrandEquipmentTypesAction::run($brand, $data['equipment_type_ids']);

        return $brand->fresh(['equipmentTypes']);
    }

    public static function normalizeLabel(string $name): string
    {
        $ascii = Str::ascii($name);

        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ascii) ?? '');
    }
}
