<?php

namespace App\Actions\Subscriptions;

use App\Models\BoldStatusCheck;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPayment;
use App\Services\Bold\BoldClient;
use App\Support\SystemActor;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Le pregunta a Bold en qué quedó un cobro, y solo lo da por pagado si la
 * respuesta lo sostiene.
 *
 * El webhook es el camino natural, pero no puede ser el único: si su aviso no
 * llega, el comercio pagó y su cuenta sigue sin activar. Consultar es la otra
 * mitad. Ahora bien, confirmar por consulta es tan definitivo como hacerlo por el
 * webhook —activa la suscripción y emite la factura—, así que se exige lo mismo
 * que se le exigiría a una notificación:
 *
 *  - que Bold declare el link pagado;
 *  - que traiga identificador de transacción, porque un cobro aprobado siempre
 *    tiene uno y su ausencia delata un estado a medias;
 *  - que el monto cobrado sea el que teníamos que cobrar.
 *
 * Si algo de eso falla no se confirma, y el motivo queda escrito. Errar hacia
 * «sigue pendiente» se arregla con un botón; errar hacia «pagado» regala el
 * servicio y encima descuadra la contabilidad.
 *
 * Cada consulta —confirme o no— deja constancia con la respuesta literal de Bold,
 * para que un cobro dado por pagado siempre pueda mostrar en qué se basó.
 */
class SyncBoldPaymentStatusAction
{
    use AsAction;

    /** Estados con los que Bold da el link por cobrado. */
    private const PAID = ['PAID', 'APPROVED'];

    /** Diferencia máxima tolerable entre lo que cobramos y lo que reporta Bold. */
    private const AMOUNT_TOLERANCE = 0.01;

    /**
     * @return array{changed: bool, status: string, detail: string}
     */
    public function handle(
        SubscriptionInvoice $invoice,
        string $origin = BoldStatusCheck::ORIGIN_MANUAL,
    ): array {
        if ($invoice->status === 'paid') {
            return $this->result(false, 'PAID', 'El cobro ya estaba pagado.');
        }

        if (blank($invoice->bold_payment_link)) {
            return $this->result(false, 'SIN_LINK', 'El cobro no tiene un link de Bold que consultar.');
        }

        $answer = BoldClient::make()->linkStatusDetailed((string) $invoice->bold_payment_link);
        $link = $answer['body'];

        $check = $this->startCheck($invoice, $origin, $answer);

        if (! $answer['ok']) {
            return $this->close($check, false, 'ERROR', 'No se pudo consultar a Bold: '.($answer['error'] ?? 'sin detalle'));
        }

        $status = mb_strtoupper(trim((string) ($link['status'] ?? '')));

        if ($status === '') {
            return $this->close($check, false, 'ERROR', 'Bold respondió sin informar el estado del link.');
        }

        if (! in_array($status, self::PAID, true)) {
            if ($status !== $invoice->bold_status) {
                $invoice->forceFill(['bold_status' => $status])->save();
            }

            return $this->close($check, false, $status, "Bold reporta el link en {$status}: no se confirma nada.");
        }

        return $this->confirmIfSound($invoice, $link, $check);
    }

    /**
     * Bold dice que está pagado. Antes de creerle del todo, que lo que dice cuadre.
     *
     * @param  array<string, mixed>  $link
     * @return array{changed: bool, status: string, detail: string}
     */
    private function confirmIfSound(SubscriptionInvoice $invoice, array $link, BoldStatusCheck $check): array
    {
        $transaction_id = trim((string) ($link['transaction_id'] ?? ''));

        if ($transaction_id === '') {
            return $this->close($check, false, 'PAID_SIN_TRANSACCION',
                'Bold da el link por pagado pero no informa identificador de transacción. '
                .'No se confirma: un cobro aprobado siempre trae uno.');
        }

        $expected = round((float) $invoice->amount, 2);
        $reported = round((float) ($link['total'] ?? 0), 2);

        if (abs($reported - $expected) > self::AMOUNT_TOLERANCE) {
            return $this->close($check, false, 'MONTO_NO_CUADRA',
                "Bold reporta {$reported} y el cobro es por {$expected}. No se confirma.");
        }

        $method = (string) ($link['payment_method'] ?? '');

        $invoice->forceFill([
            'bold_status'         => 'PAID',
            'bold_payment_id'     => $transaction_id,
            'bold_payment_method' => $method ?: $invoice->bold_payment_method,
        ])->save();

        SystemActor::run(fn () => ConfirmSubscriptionPaymentAction::run(
            invoice: $invoice,
            payment_method: $method ?: 'en línea',
            payment_reference: $transaction_id,
            paid_at: now()->toDateTimeString(),
            channel: SubscriptionPayment::CHANNEL_ONLINE,
            gateway_data: $this->gatewayData($link),
            gateway: 'bold',
            gateway_source: 'consulta: '.$check->origin,
        ));

        return $this->close($check, true, 'PAID',
            "Pago confirmado: Bold reporta PAID con la transacción {$transaction_id} por {$reported}.");
    }

    /**
     * Abre la constancia con la respuesta de Bold antes de decidir nada, para que
     * quede aunque la decisión falle a mitad de camino.
     *
     * @param  array{ok: bool, http_status: int|null, body: array<string, mixed>, raw: string, duration_ms: int, error: string|null}  $answer
     */
    private function startCheck(SubscriptionInvoice $invoice, string $origin, array $answer): BoldStatusCheck
    {
        $link = $answer['body'];

        return BoldStatusCheck::query()->create([
            'subscription_invoice_id' => $invoice->id,
            'payment_link'            => $invoice->bold_payment_link,
            'reference'               => $invoice->bold_reference,
            'origin'                  => $origin,
            'http_status'             => $answer['http_status'],
            'reported_status'         => (string) ($link['status'] ?? '') ?: null,
            'reported_amount'         => isset($link['total']) ? round((float) $link['total'], 2) : null,
            'reported_currency'       => (string) ($link['currency'] ?? '') ?: null,
            'transaction_id'          => (string) ($link['transaction_id'] ?? '') ?: null,
            'payment_method'          => (string) ($link['payment_method'] ?? '') ?: null,
            'is_sandbox'              => array_key_exists('is_sandbox', $link) ? (bool) $link['is_sandbox'] : null,
            'raw_response'            => $answer['raw'],
            'error'                   => $answer['error'],
            'duration_ms'             => $answer['duration_ms'],
            'user_id'                 => auth()->id(),
            'ip'                      => request()?->ip(),
        ]);
    }

    /** @return array{changed: bool, status: string, detail: string} */
    private function close(BoldStatusCheck $check, bool $confirmed, string $status, string $detail): array
    {
        $check->forceFill(['confirmed' => $confirmed, 'decision' => $detail])->save();

        return $this->result($confirmed, $status, $detail);
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
