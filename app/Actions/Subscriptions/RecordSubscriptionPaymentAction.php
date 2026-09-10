<?php

namespace App\Actions\Subscriptions;

use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPayment;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Deja registrado el detalle de un pago de suscripción.
 *
 * Se llama tanto cuando alguien confirma una transferencia como cuando Bold
 * avisa de un pago en línea, para que el detalle quede igual de completo por los
 * dos caminos. De la pasarela se guarda todo lo que reporta —medio, impuestos,
 * propina, correo del pagador, códigos— en vez de dejarlo enterrado en el
 * payload crudo del webhook.
 */
class RecordSubscriptionPaymentAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $gateway_data  «data» del webhook, si el pago fue en línea.
     */
    public function handle(
        SubscriptionInvoice $invoice,
        string $channel,
        string $method,
        ?string $payment_reference = null,
        ?string $notes = null,
        array $gateway_data = [],
        ?string $gateway = null,
        ?string $gateway_source = null,
    ): SubscriptionPayment {
        $amount = $gateway_data['amount'] ?? [];

        $payment_id = (string) ($gateway_data['payment_id'] ?? '') ?: null;

        // Un mismo pago de la pasarela no se registra dos veces: el webhook puede
        // llegar repetido y el índice único lo impediría de todos modos.
        if ($payment_id && $gateway) {
            $existing = SubscriptionPayment::query()
                ->where('gateway', $gateway)
                ->where('gateway_payment_id', $payment_id)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return SubscriptionPayment::query()->create([
            'business_id'             => (int) $invoice->business_id,
            'subscription_id'         => $invoice->subscription_id,
            'subscription_invoice_id' => (int) $invoice->id,
            'channel'                 => $channel,
            'gateway'                 => $gateway,
            'method'                  => $method,
            'method_label'            => null,
            'amount'                  => isset($amount['total'])
                ? round((float) $amount['total'], 2)
                : round((float) $invoice->amount, 2),
            'currency'                => (string) ($amount['currency'] ?? 'COP'),
            'tip'                     => isset($amount['tip']) ? round((float) $amount['tip'], 2) : null,
            'taxes'                   => ! empty($amount['taxes']) ? $amount['taxes'] : null,
            'gateway_payment_id'      => $payment_id,
            'gateway_reference'       => (string) ($gateway_data['metadata']['reference'] ?? '') ?: $invoice->bold_reference,
            'gateway_code'            => (string) ($gateway_data['bold_code'] ?? '') ?: null,
            'gateway_source'          => $gateway_source,
            'payer_email'             => (string) ($gateway_data['payer_email'] ?? '') ?: null,
            'payment_reference'       => $payment_reference,
            'paid_at'                 => $invoice->paid_at ?? now(),
            'metadata'                => $this->extraDetail($gateway_data),
            'notes'                   => $notes,
            'created_by'              => auth()->id(),
        ]);
    }

    /**
     * Lo que reporta la pasarela y no tiene columna propia. Se guarda aparte del
     * payload del webhook para que siga estando aunque esa bitácora se purgue.
     *
     * @param  array<string, mixed>  $gateway_data
     * @return array<string, mixed>|null
     */
    private function extraDetail(array $gateway_data): ?array
    {
        $extra = array_filter([
            'merchant_id'        => $gateway_data['merchant_id'] ?? null,
            'gateway_user_id'    => $gateway_data['user_id'] ?? null,
            'gateway_created_at' => $gateway_data['created_at'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        return $extra !== [] ? $extra : null;
    }
}
