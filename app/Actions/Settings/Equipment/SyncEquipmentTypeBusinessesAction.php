<?php

namespace App\Actions\Settings\Equipment;

use App\Models\EquipmentType;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncEquipmentTypeBusinessesAction
{
    use AsAction;

    /**
     * Sincroniza la relación N:N entre un tipo de equipo y los negocios.
     *
     * @param  array<int>  $business_ids
     */
    public function handle(EquipmentType $equipment_type, array $business_ids): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);

        $equipment_type->businesses()->sync(
            collect($business_ids)
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all()
        );
    }
}
