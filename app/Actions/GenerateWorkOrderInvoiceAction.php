<?php

namespace App\Actions;

use App\Models\WorkOrder;
use App\Models\WorkOrderInvoice;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateWorkOrderInvoiceAction
{
    use AsAction;

    /**
     * Genera una factura para la OT con el total actual.
     *
     * @param  WorkOrder $workOrder
     * @param  array     $data  Datos adicionales (due_date, tax_percentage, notes)
     * @return WorkOrderInvoice
     */
    public function handle(WorkOrder $workOrder, array $data = []): WorkOrderInvoice
    {
        $workOrder->recalculateTotals();
        $workOrder->refresh();

        $taxPercentage = $data['tax_percentage'] ?? $workOrder->tax_percentage;
        $subtotal      = $workOrder->subtotal;
        $taxAmount     = round($subtotal * ($taxPercentage / 100), 2);
        $total         = $subtotal + $taxAmount;

        return WorkOrderInvoice::create([
            'business_id'    => $workOrder->business_id,
            'work_order_id'  => $workOrder->id,
            'reference'      => WorkOrderInvoice::generateReference($workOrder->business_id),
            'subtotal'       => $subtotal,
            'tax_percentage' => $taxPercentage,
            'tax_amount'     => $taxAmount,
            'total'          => $total,
            'status'         => 'pendiente',
            'due_date'       => $data['due_date'] ?? now()->addDays(15)->toDateString(),
            'notes'          => $data['notes'] ?? null,
            'created_by'     => auth()->id(),
        ]);
    }
}
