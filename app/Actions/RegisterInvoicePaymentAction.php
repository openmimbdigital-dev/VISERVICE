<?php

namespace App\Actions;

use App\Actions\Workshop\RecordInvoiceStatusHistoryAction;
use App\Models\WorkOrderInvoice;
use App\Models\WorkOrderInvoiceStatusHistory;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class RegisterInvoicePaymentAction
{
    use AsAction;

    /**
     * Registra el pago de una factura de OT.
     *
     * @param  string       $payment_method     Efectivo, transferencia, tarjeta, etc.
     * @param  string|null  $paid_at            Fecha de pago (Y-m-d)
     */
    public function handle(
        WorkOrderInvoice $invoice,
        string $payment_method,
        ?string $payment_reference = null,
        ?string $paid_at = null,
        ?string $notes = null
    ): WorkOrderInvoice {
        abort_unless(auth()->user()?->can('workshop.invoices.pay'), 403);

        $invoice = WorkOrderInvoice::query()->forAuthUser()->findOrFail($invoice->id);

        if ($invoice->status === 'pagada') {
            throw ValidationException::withMessages([
                'payment' => 'Esta factura ya está registrada como pagada.',
            ]);
        }

        if ($invoice->status === 'anulada') {
            throw ValidationException::withMessages([
                'payment' => 'No se puede registrar el pago de una factura anulada.',
            ]);
        }

        $previous_status = (string) $invoice->status;

        $invoice->update([
            'status'            => 'pagada',
            'paid_at'           => $paid_at ?? now(),
            'payment_method'    => $payment_method,
            'payment_reference' => $payment_reference,
            'notes'             => $notes ?? $invoice->notes,
        ]);

        RecordInvoiceStatusHistoryAction::run(
            invoice: $invoice,
            kind: WorkOrderInvoiceStatusHistory::KIND_BILLING,
            to_status: 'pagada',
            from_status: $previous_status,
            comment: $notes,
            metadata: [
                'payment_method'    => $payment_method,
                'payment_reference' => $payment_reference,
            ],
        );

        LogUserHistoricalAction::run(
            action: 'updated',
            module: 'workshop.invoices',
            description: "Registró el pago de la factura {$invoice->reference}",
            subject: $invoice,
            subject_label: $invoice->reference,
            properties: [
                'from'              => $previous_status,
                'payment_method'    => $payment_method,
                'payment_reference' => $payment_reference,
                'total'             => $invoice->total,
            ],
            business_id: (int) $invoice->business_id,
        );

        return $invoice->fresh();
    }
}
