<?php

namespace App\Actions;

use App\Models\WorkOrderInvoice;
use Lorisleiva\Actions\Concerns\AsAction;

class RegisterInvoicePaymentAction
{
    use AsAction;

    /**
     * Registra el pago de una factura de OT.
     *
     * @param  WorkOrderInvoice $invoice
     * @param  string           $payment_method   Efectivo, transferencia, tarjeta, etc.
     * @param  string|null      $payment_reference
     * @param  string|null      $paid_at          Fecha de pago (Y-m-d)
     * @param  string|null      $notes
     * @return WorkOrderInvoice
     */
    public function handle(
        WorkOrderInvoice $invoice,
        string $payment_method,
        ?string $payment_reference = null,
        ?string $paid_at = null,
        ?string $notes = null
    ): WorkOrderInvoice {
        $invoice->update([
            'status'            => 'pagada',
            'paid_at'           => $paid_at ?? now(),
            'payment_method'    => $payment_method,
            'payment_reference' => $payment_reference,
            'notes'             => $notes ?? $invoice->notes,
        ]);

        return $invoice->fresh();
    }
}
