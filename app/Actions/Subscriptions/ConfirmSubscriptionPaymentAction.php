<?php

namespace App\Actions\Subscriptions;

use App\Models\SubscriptionInvoice;
use App\Models\WorkOrderInvoice;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Da por pagado el cobro de una suscripción y emite su documento.
 *
 * Es el único punto por el que se confirma un pago, venga de donde venga: el
 * superAdmin confirmando una transferencia o Bold avisando que el cobro en línea
 * se aprobó. Así ambos caminos activan la suscripción y generan la OT con su
 * factura exactamente igual.
 *
 * Es idempotente: un cobro ya pagado devuelve su factura sin volver a tocar nada,
 * que es justo lo que hace falta cuando Bold reintenta una notificación.
 */
class ConfirmSubscriptionPaymentAction
{
    use AsAction;

    public function handle(
        SubscriptionInvoice $invoice,
        string $payment_method,
        ?string $payment_reference = null,
        ?string $paid_at = null,
        ?string $notes = null,
    ): ?WorkOrderInvoice {
        if ($invoice->status === 'paid') {
            return $invoice->workOrderInvoice;
        }

        $invoice->forceFill(array_filter([
            'status'            => 'paid',
            'paid_at'           => $paid_at ?: now(),
            'payment_method'    => $payment_method,
            'payment_reference' => $payment_reference,
            'notes'             => $notes ?: $invoice->notes,
        ], fn ($value) => $value !== null))->save();

        $subscription = $invoice->subscription;

        if ($subscription && in_array($subscription->status, ['pending', 'trial', 'past_due'], true)) {
            $subscription->update(['status' => 'active']);
        }

        return CreateSubscriptionWorkOrderAction::run($invoice->fresh());
    }
}
