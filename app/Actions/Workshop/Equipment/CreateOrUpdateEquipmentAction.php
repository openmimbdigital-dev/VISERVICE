<?php

namespace App\Actions\Workshop\Equipment;

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
     *     plate: string,
     *     year: int|null,
     *     status: bool,
     *     notes: string|null,
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
            abort_unless((int) $business_id === (int) $user->business_id, 403);
        }

        $equipment_type = EquipmentType::query()->findOrFail($data['equipment_type_id']);
        abort_unless($equipment_type->isAccessibleToUser(), 403);

        $client = Client::query()
            ->forAuthUser()
            ->where('business_id', $business_id)
            ->whereKey($data['client_id'])
            ->firstOrFail();

        $brand_name = null;
        $model_name = null;

        if ($data['brand_id']) {
            $brand = Brand::query()
                ->visibleToUser()
                ->whereHas('equipmentTypes', fn ($query) => $query->whereKey($equipment_type->id))
                ->findOrFail($data['brand_id']);

            $brand_name = $brand->name;
        }

        if ($data['model_id']) {
            abort_unless($data['brand_id'], 403);

            $model = EquipmentModel::query()
                ->visibleToUser()
                ->where('brand_id', $data['brand_id'])
                ->findOrFail($data['model_id']);

            $model_name = $model->name;
        }

        $attributes = [
            'business_id'         => $business_id,
            'client_id'           => $client->id,
            'client_name'         => $client->name,
            'brand_id'            => $data['brand_id'],
            'model_id'            => $data['model_id'],
            'equipment_type_id'   => $equipment_type->id,
            'equipment_type_name' => $equipment_type->name,
            'plate'               => $data['plate'],
            'brand_name'          => $brand_name,
            'model_name'          => $model_name,
            'year'                => $data['year'],
            'status'              => $data['status'],
            'notes'               => $data['notes'],
        ];

        if ($equipment_id) {
            $equipment = Equipment::query()
                ->forAuthUser()
                ->where('equipment_type_id', $equipment_type->id)
                ->findOrFail($equipment_id);

            abort_unless((int) $equipment->business_id === (int) $business_id, 403);

            $equipment->update($attributes);

            return $equipment->fresh();
        }

        $attributes['created_by'] = auth()->id();

        return Equipment::create($attributes);
    }
}
