<?php

namespace App\Actions\Workshop;

use App\Models\WorkOrder;
use App\Models\WorkOrderPayment;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateInitialWorkOrderAdvancePaymentAction
{
    use AsAction;

    public function handle(WorkOrder $work_order, float $advance_percentage): ?WorkOrderPayment
    {
        $advance_percentage = max(0, min(100, $advance_percentage));
        $amount = round((float) $work_order->subtotal * ($advance_percentage / 100), 2);

        $work_order->update([
            'advance_percentage' => $advance_percentage,
            'advance_amount' => $amount,
        ]);

        if ($amount <= 0) {
            return null;
        }

        return WorkOrderPayment::query()->create([
            'business_id' => $work_order->business_id,
            'work_order_id' => $work_order->id,
            'amount' => $amount,
            'percentage' => $advance_percentage,
            'paid_at' => now(),
            'notes' => $work_order->quotation_id
                ? 'Anticipo inicial desde cotización'
                : 'Anticipo inicial al crear la OT',
            'status' => 'confirmed',
            'created_by' => auth()->id(),
        ]);
    }
}
