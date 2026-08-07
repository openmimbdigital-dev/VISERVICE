<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
use App\Models\BusinessBankAccount;
use App\Models\BusinessPaymentMethod;
use App\Models\WorkOrder;
use App\Models\WorkOrderPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class RegisterWorkOrderAdvancePaymentAction
{
    use AsAction;

    /**
     * @param  array{
     *     amount: float|int|string,
     *     business_payment_method_id?: int|null,
     *     business_bank_account_id?: int|null,
     *     payment_reference?: string|null,
     *     paid_at?: string|null,
     *     notes?: string|null,
     * }  $data
     */
    public function handle(WorkOrder $work_order, array $data): WorkOrderPayment
    {
        abort_unless(auth()->user()->can('workshop.advance-payments.pay'), 403);

        return DB::transaction(function () use ($work_order, $data) {
            $work_order = WorkOrder::query()
                ->forAuthUser()
                ->lockForUpdate()
                ->findOrFail($work_order->id);

            if ((float) $work_order->advance_amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Esta OT no tiene anticipo acordado.',
                ]);
            }

            $remaining = $work_order->advanceRemainingAmount();
            if ($remaining <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'El anticipo de esta OT ya está cubierto.',
                ]);
            }

            $amount = round((float) $data['amount'], 2);
            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'El monto debe ser mayor a cero.',
                ]);
            }

            if ($amount > $remaining + 0.009) {
                throw ValidationException::withMessages([
                    'amount' => 'El monto supera el saldo pendiente ('.number_format($remaining, 2, '.', '').').',
                ]);
            }

            $method_id = ! empty($data['business_payment_method_id'])
                ? (int) $data['business_payment_method_id']
                : null;
            $bank_id = ! empty($data['business_bank_account_id'])
                ? (int) $data['business_bank_account_id']
                : null;

            if ($method_id) {
                abort_unless(
                    BusinessPaymentMethod::query()->visibleToUser()->whereKey($method_id)->exists(),
                    422
                );
            }

            if ($bank_id) {
                abort_unless(
                    BusinessBankAccount::query()
                        ->forAuthUser()
                        ->where('business_id', $work_order->business_id)
                        ->whereKey($bank_id)
                        ->exists(),
                    422
                );
            }

            $subtotal = (float) $work_order->subtotal;
            $percentage = $subtotal > 0 ? round(($amount / $subtotal) * 100, 2) : null;

            $payment = WorkOrderPayment::query()->create([
                'business_id' => $work_order->business_id,
                'work_order_id' => $work_order->id,
                'amount' => $amount,
                'percentage' => $percentage,
                'payment_method' => $method_id
                    ? BusinessPaymentMethod::query()->find($method_id)?->name
                    : null,
                'business_payment_method_id' => $method_id,
                'business_bank_account_id' => $bank_id,
                'payment_reference' => $data['payment_reference'] ?? null,
                'paid_at' => ! empty($data['paid_at']) ? $data['paid_at'] : now(),
                'notes' => $data['notes'] ?? null,
                'status' => 'confirmed',
                'created_by' => auth()->id(),
            ]);

            $work_order->loadMissing('client:id,name');

            LogUserHistoricalAction::run(
                action: 'advance_payment_registered',
                module: 'workshop.advance-payments',
                description: "Registró abono de anticipo por ".number_format($amount, 2, '.', '')." en la OT {$work_order->reference}",
                subject: $payment,
                properties: [
                    'work_order_id' => $work_order->id,
                    'work_order_reference' => $work_order->reference,
                    'amount' => $payment->amount,
                    'percentage' => $payment->percentage,
                    'status' => $payment->status,
                    'paid_at' => $payment->paid_at?->toDateTimeString(),
                    'remaining_after' => $work_order->fresh()->advanceRemainingAmount(),
                ],
                business_id: (int) $work_order->business_id,
            );

            return $payment;
        });
    }
}
