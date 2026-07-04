<?php

namespace App\Actions;

use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateWorkOrderAction
{
    use AsAction;

    /**
     * Crea una OT con sus items en una transacción atómica.
     * Puede usarse para OT directa o al aceptar una cotización.
     *
     * @param  int    $business_id
     * @param  int    $client_id
     * @param  int    $equipment_id
     * @param  array  $data   Campos adicionales (quotation_id, km_entry, diagnosis, etc.)
     * @param  array  $items  Array de items
     * @return WorkOrder
     */
    public function handle(int $business_id, int $client_id, int $equipment_id, array $data, array $items = []): WorkOrder
    {
        return DB::transaction(function () use ($business_id, $client_id, $equipment_id, $data, $items) {
            $workOrder = WorkOrder::create([
                'business_id'       => $business_id,
                'client_id'         => $client_id,
                'equipment_id'      => $equipment_id,
                'quotation_id'      => $data['quotation_id'] ?? null,
                'reference'         => WorkOrder::generateReference($business_id),
                'status'            => 'abierta',
                'km_entry'          => $data['km_entry'] ?? 0,
                'diagnosis'         => $data['diagnosis'] ?? null,
                'tax_percentage'    => $data['tax_percentage'] ?? 0,
                'estimated_delivery' => $data['estimated_delivery'] ?? null,
                'notes'             => $data['notes'] ?? null,
                'observations'      => $data['observations'] ?? null,
                'created_by'        => $data['created_by'] ?? auth()->id(),
            ]);

            foreach ($items as $item) {
                $this->addItem($workOrder, $item);
            }

            $workOrder->recalculateTotals();

            // Actualiza el km del equipo si es mayor al registrado
            $equipment = $workOrder->equipment;
            if ($workOrder->km_entry > $equipment->km_current) {
                $equipment->update(['km_current' => $workOrder->km_entry]);
            }

            return $workOrder->fresh();
        });
    }

    protected function addItem(WorkOrder $workOrder, array $item): WorkOrderItem
    {
        $qty      = (float) ($item['quantity'] ?? 1);
        $price    = (float) ($item['unit_price'] ?? 0);
        $discount = (float) ($item['discount_percentage'] ?? 0);
        $subtotal = round(($qty * $price) * (1 - $discount / 100), 2);

        return WorkOrderItem::create([
            'work_order_id'       => $workOrder->id,
            'item_type'           => $item['item_type'] ?? 'servicio',
            'description'         => $item['description'],
            'quantity'            => $qty,
            'unit_price'          => $price,
            'discount_percentage' => $discount,
            'subtotal'            => $subtotal,
            'status'              => 'pendiente',
        ]);
    }
}
