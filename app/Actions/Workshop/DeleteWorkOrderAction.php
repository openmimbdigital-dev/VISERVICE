<?php

namespace App\Actions\Workshop;

use App\Models\WorkOrder;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteWorkOrderAction
{
    use AsAction;

    public function handle(int $work_order_id): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.delete'), 403);

        $work_order = WorkOrder::query()->forAuthUser()->findOrFail($work_order_id);

        if ($work_order->invoices()->exists()
            || $work_order->remissions()->exists()
            || $work_order->purchaseOrders()->exists()
        ) {
            throw new \RuntimeException('No se puede eliminar: la OT tiene remisiones, facturas u órdenes de compra asociadas.');
        }

        if (! in_array($work_order->status, ['abierta', 'en_proceso'], true)) {
            throw new \RuntimeException('Solo se pueden eliminar OTs abiertas o en proceso.');
        }

        $work_order->items()->delete();
        $work_order->delete();
    }
}
