<?php

namespace App\Actions\Workshop\Equipment;

use App\Models\AttributeEquipmentType;
use App\Models\Equipment;
use App\Models\EquipmentType;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEquipmentAction
{
    use AsAction;

    public function handle(Equipment $equipment, EquipmentType $equipment_type): void
    {
        abort_unless(auth()->user()->can('workshop.equipment.delete'), 403);

        abort_unless(
            Equipment::query()->forAuthUser()->whereKey($equipment->id)->exists(),
            403
        );

        abort_unless(
            (int) $equipment->equipment_type_id === (int) $equipment_type->id,
            404
        );

        abort_unless($equipment_type->isAccessibleToUser(), 403);

        if ($equipment->hasDependencies()) {
            abort(422, $equipment->dependencyBlockReason() ?? 'No se puede eliminar el equipo.');
        }

        AttributeEquipmentType::query()
            ->where('model_type', Equipment::class)
            ->where('model_id', $equipment->id)
            ->delete();

        $equipment->delete();
    }
}
