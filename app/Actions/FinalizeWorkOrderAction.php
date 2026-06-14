<?php

namespace App\Actions;

use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FinalizeWorkOrderAction
{
    use AsAction;

    /**
     * Finaliza la OT: cambia estado, registra km salida y genera factura si no existe.
     *
     * @param  WorkOrder $workOrder
     * @param  int|null  $km_exit
     * @param  string|null $work_description
     * @return WorkOrder
     */
    public function handle(WorkOrder $workOrder, ?int $km_exit = null, ?string $work_description = null): WorkOrder
    {
        if ($workOrder->status === 'finalizada') {
            return $workOrder;
        }

        return DB::transaction(function () use ($workOrder, $km_exit, $work_description) {
            $workOrder->recalculateTotals();

            $workOrder->update([
                'status'           => 'finalizada',
                'km_exit'          => $km_exit,
                'work_description' => $work_description,
                'finalized_at'     => now(),
            ]);

            // Actualiza km del vehículo con el km de salida
            if ($km_exit && $km_exit > $workOrder->vehicle->km_current) {
                $workOrder->vehicle->update(['km_current' => $km_exit]);
            }

            // Genera factura si no existe ninguna pendiente o pagada
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
