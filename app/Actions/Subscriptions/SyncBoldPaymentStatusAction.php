<?php

namespace App\Actions\Subscriptions;

use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPayment;
use App\Services\Bold\BoldClient;
use App\Services\Bold\BoldRequestException;
use App\Support\SystemActor;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

/**
 * Le pregunta a Bold en qué quedó un cobro, en vez de esperar a que avise.
 *
 * El webhook es el camino rápido, pero no es de fiar como único camino: puede no
 * estar registrado para el entorno por el que entró el pago, puede llegar firmado
 * con otra llave, o simplemente no llegar. Cuando eso pasa el comercio pagó y su
 * cuenta sigue sin activar, que es el peor final posible.
 *
 * Por eso el estado también se consulta: al volver del checkout, cada diez
 * minutos desde la tarea programada, y a mano desde el panel. Los dos caminos
 * terminan en la misma acción de confirmación, así que da igual cuál llegue
 * primero: el segundo no hace nada.
 */
class SyncBoldPaymentStatusAction
{
    use AsAction;

    /** Estados con los que Bold da el link por cobrado. */
    private const PAID = ['PAID', 'APPROVED'];

    /**
     * @return array{changed: bool, status: string, detail: string}
     */
    public function handle(SubscriptionInvoice $invoice): array
    {
        if ($invoice->status === 'paid') {
            return $this->result(false, 'PAID', 'El cobro ya estaba pagado.');
        }

        if (blank($invoice->bold_payment_link)) {
            return $this->result(false, 'SIN_LINK', 'El cobro no tiene un link de Bold que consultar.');
        }

        try {
            $link = BoldClient::make()->linkStatus((string) $invoice->bold_payment_link);
        } catch (BoldRequestException $exception) {
            return $this->result(false, 'ERROR', 'Bold no respondió: '.$exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return $this->result(false, 'ERROR', 'No fue posible consultar el link en Bold.');
        }

        $status = mb_strtoupper(trim((string) ($link['status'] ?? '')));

        if ($status === '') {
            return $this->result(false, 'ERROR', 'Bold no informó el estado del link.');
        }

        if (! in_array($status, self::PAID, true)) {
            if ($status !== $invoice->bold_status) {
                $invoice->forceFill(['bold_status' => $status])->save();
            }

            return $this->result(false, $status, 'El link sigue en '.$status.'.');
        }

        return $this->confirm($invoice, $link);
    }

    /**
     * @param  array<string, mixed>  $link
     * @return array{changed: bool, status: string, detail: string}
     */
    private function confirm(SubscriptionInvoice $invoice, array $link): array
    {
        $transaction_id = (string) ($link['transaction_id'] ?? '');
        $method = (string) ($link['payment_method'] ?? '');

        $invoice->forceFill([
            'bold_status'         => 'PAID',
            'bold_payment_id'     => $transaction_id ?: $invoice->bold_payment_id,
            'bold_payment_method' => $method ?: $invoice->bold_payment_method,
        ])->save();

        SystemActor::run(fn () => ConfirmSubscriptionPaymentAction::run(
            invoice: $invoice,
            payment_method: $method ?: 'en línea',
            payment_reference: $transaction_id ?: null,
            paid_at: now()->toDateTimeString(),
            channel: SubscriptionPayment::CHANNEL_ONLINE,
            gateway_data: $this->gatewayData($link),
            gateway: 'bold',
            gateway_source: 'consulta',
        ));

        return $this->result(true, 'PAID', 'Pago confirmado al consultarlo en Bold.');
    }

    /**
     * Lo que reporta el link, con la misma forma con la que llega por el webhook,
     * para que el detalle del pago quede igual venga por donde venga.
     *
     * @param  array<string, mixed>  $link
     * @return array<string, mixed>
     */
    private function gatewayData(array $link): array
    {
        return [
            'payment_id'     => (string) ($link['transaction_id'] ?? ''),
            'payment_method' => (string) ($link['payment_method'] ?? ''),
            'amount'         => array_filter([
                'currency' => (string) ($link['currency'] ?? 'COP'),
                'total'    => $link['total'] ?? null,
                'tip'      => $link['tip_amount'] ?? null,
                'taxes'    => $link['taxes'] ?? null,
            ], fn ($value) => $value !== null && $value !== []),
            'metadata'       => ['reference' => (string) ($link['reference'] ?? '')],
        ];
    }

    /** @return array{changed: bool, status: string, detail: string} */
    private function result(bool $changed, string $status, string $detail): array
    {
        return ['changed' => $changed, 'status' => $status, 'detail' => $detail];
    }
}
