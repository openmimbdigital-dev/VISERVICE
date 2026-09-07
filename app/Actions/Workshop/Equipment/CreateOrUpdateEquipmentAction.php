<?php

namespace App\Actions\Workshop\Equipment;

use App\Actions\LogEquipmentHistoricalAction;
use App\Actions\LogUserHistoricalAction;
use App\Models\Brand;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\EquipmentModel;
use App\Models\EquipmentType;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateEquipmentAction
{
    use AsAction;

    /**
     * @param  array{
     *     client_id: int,
     *     brand_id: int|null,
     *     model_id: int|null,
     *     equipment_type_id: int,
     *     name: string,
     *     plate: string,
     *     year: int|null,
     *     status: bool,
     *     notes: string|null,
     *     attribute_values: array<int, mixed>,
     *     step: int,
     *     final_step: int,
     * }  $data
     */
    public function handle(int $business_id, ?int $equipment_id, array $data): Equipment
    {
        abort_unless(
            auth()->user()->can($equipment_id ? 'workshop.equipment.edit' : 'workshop.equipment.create'),
            403
        );

        $user = auth()->user();

        if (! $user->hasRole('superAdmin')) {
            abort_unless($user->belongsToBusiness($business_id), 403);
        }

        $equipment_type = EquipmentType::query()->findOrFail($data['equipment_type_id']);
        abort_unless($equipment_type->isAccessibleToUser(), 403);

        $client = Client::query()
            ->forAuthUser()
            ->where('business_id', $business_id)
            ->whereKey($data['client_id'])
            ->firstOrFail();

        $brand      = null;
        $brand_name = null;

        if (! empty($data['brand_id'])) {
            $brand = Brand::query()
                ->visibleToUser()
                ->whereHas('equipmentTypes', fn ($query) => $query->whereKey($equipment_type->id))
                ->findOrFail($data['brand_id']);

            $brand_name = $brand->name;
        }

        $model      = null;
        $model_name = null;

        if (! empty($data['model_id'])) {
            abort_unless(! empty($data['brand_id']), 422);

            $model = EquipmentModel::query()
                ->visibleToUser()
                ->where('brand_id', $data['brand_id'])
                ->findOrFail($data['model_id']);

            $model_name = $model->name;
        }

        $attributes = [
            'business_id'         => $business_id,
            'step'                => (int) ($data['step'] ?? 1),
            'final_step'          => (int) ($data['final_step'] ?? Equipment::DEFAULT_FINAL_STEP),
            'client_id'           => $client->id,
            'client_name'         => $client->name,
            'brand_id'            => $data['brand_id'] ?? null,
            'model_id'            => $data['model_id'] ?? null,
            'equipment_type_id'   => $equipment_type->id,
            'equipment_type_name' => $equipment_type->name,
            'name'                => $data['name'],
            'plate'               => $data['plate'],
            'brand_name'          => $brand_name,
            'model_name'          => $model_name,
            'year'                => $data['year'] ?? null,
            'status'              => $data['status'],
            'notes'               => $data['notes'],
        ];

        $is_editing = (bool) $equipment_id;

        if ($equipment_id) {
            $equipment = Equipment::query()
                ->forAuthUser()
                ->where('equipment_type_id', $equipment_type->id)
                ->findOrFail($equipment_id);

            abort_unless((int) $equipment->business_id === (int) $business_id, 403);

            $equipment->update($attributes);
        } else {
            $attributes['created_by'] = auth()->id();
            $equipment                  = Equipment::create($attributes);
        }

        SyncEquipmentAttributeValuesAction::run(
            $equipment,
            $business_id,
            $equipment_type->id,
            $data['attribute_values'] ?? [],
            is_editing: $is_editing
        );

        $equipment = $equipment->fresh();

        $action = $is_editing ? 'updated' : 'created';
        $description = ($is_editing ? 'Actualizó' : 'Creó') . " el equipo {$equipment->plate}";
        $properties = [
            'name'              => $equipment->name,
            'plate'             => $equipment->plate,
            'equipment_type_id' => $equipment->equipment_type_id,
            'status'            => $equipment->status,
            'step'              => $equipment->step,
        ];

        LogUserHistoricalAction::run(
            action: $action,
            module: 'workshop.equipment',
            description: $description,
            subject: $equipment,
            subject_label: $equipment->plate,
            properties: $properties,
            business_id: $business_id,
        );

        LogEquipmentHistoricalAction::run(
            action: $action,
            module: 'workshop.equipment',
            description: $description,
            equipment: $equipment,
            subject: $equipment,
            properties: $properties,
            business_id: $business_id,
        );

        return $equipment;
    }
}
