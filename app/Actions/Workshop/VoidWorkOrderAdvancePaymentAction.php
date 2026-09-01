<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
use App\Models\WorkOrderPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class VoidWorkOrderAdvancePaymentAction
{
    use AsAction;

    public function handle(int $payment_id, ?string $reason = null): WorkOrderPayment
    {
        abort_unless(auth()->user()->can('workshop.advance-payments.void'), 403);

        return DB::transaction(function () use ($payment_id, $reason) {
            $payment = WorkOrderPayment::query()
                ->forAuthUser()
                ->lockForUpdate()
                ->findOrFail($payment_id);

            if ($payment->status === 'voided') {
                throw ValidationException::withMessages([
                    'payment' => 'Este abono ya está anulado.',
                ]);
            }

            if ($payment->status === 'pending') {
                throw ValidationException::withMessages([
                    'payment' => 'El anticipo definido no se anula como abono. Solo se anulan pagos registrados.',
                ]);
            }

            if ($payment->status !== 'confirmed') {
                throw ValidationException::withMessages([
                    'payment' => 'Solo se pueden anular abonos confirmados.',
                ]);
            }

            $reason = $reason ?: 'Anulado desde gestión de anticipo';

            $payment->update([
                'status' => 'voided',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancellation_reason' => $reason,
            ]);

            $payment = $payment->fresh(['workOrder.client:id,name']);
            $work_order = $payment->workOrder;

            LogUserHistoricalAction::run(
                action: 'advance_payment_voided',
                module: 'workshop.advance-payments',
                description: 'Anuló abono de anticipo'
                    .($work_order ? " en la OT {$work_order->reference}" : '')
                    .' por '.number_format((float) $payment->amount, 2, '.', ''),
                subject: $payment,
                properties: [
                    'work_order_id' => $payment->work_order_id,
                    'work_order_reference' => $work_order?->reference,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'cancellation_reason' => $reason,
                ],
                business_id: (int) $payment->business_id,
            );

            return $payment;
        });
    }
}
