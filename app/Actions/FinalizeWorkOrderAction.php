<?php

namespace App\Actions;

use App\Actions\Workshop\SyncWorkOrderRemissionsStatusAction;
use App\Enums\WorkOrderStatus;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FinalizeWorkOrderAction
{
    use AsAction;

    /**
     * Finaliza la OT: cambia estado y genera factura si no existe.
     */
    public function handle(WorkOrder $workOrder, ?string $work_description = null): WorkOrder
    {
        if ($workOrder->status === WorkOrderStatus::Completed) {
            return $workOrder;
        }

        return DB::transaction(function () use ($workOrder, $work_description) {
            $workOrder->recalculateTotals();

            $workOrder->update([
                'status'           => WorkOrderStatus::Completed,
                'work_description' => $work_description,
                'finalized_at'     => now(),
            ]);

            SyncWorkOrderRemissionsStatusAction::run($workOrder, WorkOrderStatus::Completed);

            $hasInvoice = $workOrder->invoices()
                ->whereIn('status', ['pendiente', 'pagada'])
                ->exists();

            if (! $hasInvoice && $workOrder->total > 0) {
                GenerateWorkOrderInvoiceAction::run($workOrder);
            }

            return $workOrder->fresh();
        });
    }
}
