<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
use App\Models\WorkOrder;
use App\Models\WorkOrderPayment;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Guarda el anticipo acordado en la OT y crea/actualiza el registro de seguimiento
 * en work_order_payments con estado "pending" (definido, no cobrado).
 * Los abonos reales (confirmed) se registran en Gestión de anticipo.
 */
class SyncWorkOrderAdvanceCommitmentAction
{
    use AsAction;

    public function handle(WorkOrder $work_order, float $advance_percentage): void
    {
        $advance_percentage = max(0, min(100, $advance_percentage));
        $amount = round((float) $work_order->subtotal * ($advance_percentage / 100), 2);

        $paid = $work_order->advancePaidAmount();
        if ($amount + 0.009 < $paid) {
            throw ValidationException::withMessages([
                'advance_percentage' => 'El anticipo no puede ser menor a lo ya abonado ('.number_format($paid, 2, '.', '').').',
            ]);
        }

        $previous_amount = round((float) $work_order->advance_amount, 2);
        $previous_percentage = round((float) $work_order->advance_percentage, 2);

        $work_order->update([
            'advance_percentage' => $advance_percentage,
            'advance_amount' => $amount,
        ]);

        $pending_query = WorkOrderPayment::query()
            ->where('work_order_id', $work_order->id)
            ->where('status', 'pending');

        if ($amount <= 0) {
            if ($pending_query->exists()) {
                $pending_query->delete();

                LogUserHistoricalAction::run(
                    action: 'advance_commitment_removed',
                    module: 'workshop.advance-payments',
                    description: "Eliminó el anticipo definido de la OT {$work_order->reference}",
                    subject: $work_order,
                    subject_label: $work_order->reference,
                    properties: [
                        'work_order_id' => $work_order->id,
                        'previous_advance_amount' => $previous_amount,
                        'previous_advance_percentage' => $previous_percentage,
                    ],
                    business_id: (int) $work_order->business_id,
                );
            }

            return;
        }

        $pending = $pending_query->first();

        if ($pending) {
            $changed = abs((float) $pending->amount - $amount) > 0.009
                || abs((float) $pending->percentage - $advance_percentage) > 0.009;

            $pending->update([
                'amount' => $amount,
                'percentage' => $advance_percentage,
            ]);

            if ($changed) {
                LogUserHistoricalAction::run(
                    action: 'advance_commitment_updated',
                    module: 'workshop.advance-payments',
                    description: "Actualizó el anticipo definido de la OT {$work_order->reference} a ".number_format($amount, 2, '.', ''),
                    subject: $pending->fresh(),
                    properties: [
                        'work_order_id' => $work_order->id,
                        'work_order_reference' => $work_order->reference,
                        'advance_percentage' => $advance_percentage,
                        'advance_amount' => $amount,
                        'previous_advance_amount' => $previous_amount,
                        'status' => 'pending',
                    ],
                    business_id: (int) $work_order->business_id,
                );
            }

            return;
        }

        $payment = WorkOrderPayment::query()->create([
            'business_id' => $work_order->business_id,
            'work_order_id' => $work_order->id,
            'amount' => $amount,
            'percentage' => $advance_percentage,
            'paid_at' => null,
            'notes' => $work_order->quotation_id
                ? 'Anticipo definido desde cotización'
                : 'Anticipo definido en la OT',
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        LogUserHistoricalAction::run(
            action: 'advance_commitment_defined',
            module: 'workshop.advance-payments',
            description: "Definió anticipo de ".number_format($amount, 2, '.', '')." en la OT {$work_order->reference}",
            subject: $payment,
            properties: [
                'work_order_id' => $work_order->id,
                'work_order_reference' => $work_order->reference,
                'advance_percentage' => $advance_percentage,
                'advance_amount' => $amount,
                'status' => 'pending',
            ],
            business_id: (int) $work_order->business_id,
        );
    }
}
