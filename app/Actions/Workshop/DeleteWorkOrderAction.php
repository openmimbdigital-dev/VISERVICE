<?php

namespace App\Actions\Workshop;

use App\Actions\LogEquipmentHistoricalAction;
use App\Actions\LogUserHistoricalAction;
use App\Models\WorkOrder;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteWorkOrderAction
{
    use AsAction;

    public function handle(int $work_order_id): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.delete'), 403);

        $work_order = WorkOrder::query()
            ->forAuthUser()
            ->with(['client:id,name', 'equipments', 'items'])
            ->findOrFail($work_order_id);

        if ($work_order->invoices()->exists()
            || $work_order->remissions()->exists()
            || $work_order->purchaseOrders()->exists()
        ) {
            throw new \RuntimeException('No se puede eliminar: la OT tiene remisiones, facturas u órdenes de compra asociadas.');
        }

        if (! ($work_order->status?->isOpen() ?? false)) {
            throw new \RuntimeException('Solo se pueden eliminar OTs creadas o en proceso.');
        }

        $properties = [
            'status'        => $work_order->status?->value,
            'client_id'     => $work_order->client_id,
            'equipment_ids' => $work_order->equipments->pluck('id')->all(),
            'quotation_id'  => $work_order->quotation_id,
            'total'         => $work_order->total,
        ];

        LogUserHistoricalAction::run(
            action: 'deleted',
            module: 'workshop.work-orders',
            description: "Eliminó la orden de trabajo {$work_order->reference}",
            subject: $work_order,
            subject_label: $work_order->reference,
            properties: $properties,
            business_id: (int) $work_order->business_id,
        );

        foreach ($work_order->equipments as $equipment) {
            LogEquipmentHistoricalAction::run(
                action: 'deleted',
                module: 'workshop.work-orders',
                description: "Eliminó la orden de trabajo {$work_order->reference}",
                equipment: $equipment,
                subject: $work_order,
                properties: $properties,
                business_id: (int) $work_order->business_id,
            );
        }

        $work_order->items()->delete();
        $work_order->delete();
    }
}
