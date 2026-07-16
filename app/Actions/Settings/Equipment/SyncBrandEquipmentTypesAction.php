<?php

namespace App\Actions\Settings\Equipment;

use App\Models\Brand;
use App\Models\EquipmentType;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncBrandEquipmentTypesAction
{
    use AsAction;

    /**
     * Sincroniza la relación N:N entre una marca y los tipos de equipo.
     *
     * @param  array<int>  $equipment_type_ids
     */
    public function handle(Brand $brand, array $equipment_type_ids): void
    {
        abort_unless(auth()->user()->can('settings.edit'), 403);

        $user = auth()->user();

        $allowed_ids = ($user->hasRole('superAdmin')
            ? EquipmentType::query()
            : EquipmentType::query()->visibleToUser($user))
            ->whereIn('id', $equipment_type_ids)
            ->pluck('id')
            ->all();

        $brand->equipmentTypes()->sync(
            collect($equipment_type_ids)
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->intersect($allowed_ids)
                ->values()
                ->all()
        );
    }
}
