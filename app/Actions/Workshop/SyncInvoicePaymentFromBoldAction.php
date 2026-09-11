<?php

namespace App\Actions\Workshop;

use App\Actions\RegisterInvoicePaymentAction;
use App\Models\BoldStatusCheck;
use App\Models\BusinessBoldSetting;
use App\Models\WorkOrderInvoice;
use App\Services\Bold\BoldClient;
use App\Support\SystemActor;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Le pregunta a Bold si la factura ya se pagó, y la salda solo si la respuesta lo
 * sostiene.
 *
 * Es la contraparte del webhook para las facturas de taller: cuando el cliente
 * vuelve del checkout, o cuando el aviso de Bold no llega. Exige lo mismo que en
 * las suscripciones —estado pagado, identificador de transacción y monto que
 * cuadre—, porque dar por pagada una factura que no lo está le regala el trabajo
 * al taller.
 *
 * Cada consulta deja constancia con la respuesta literal, así que una factura
 * saldada por este camino siempre puede mostrar en qué se basó.
 */
class SyncInvoicePaymentFromBoldAction
{
    use AsAction;

    private const PAID = ['PAID', 'APPROVED'];

    private const AMOUNT_TOLERANCE = 0.01;

    /**
     * @return array{changed: bool, status: string, detail: string}
     */
    public function handle(
        WorkOrderInvoice $invoice,
        string $origin = BoldStatusCheck::ORIGIN_MANUAL,
    ): array {
        if ($invoice->status === 'pagada') {
            return $this->result(false, 'PAID', 'La factura ya estaba pagada.');
        }

        if ($invoice->status === 'anulada') {
            return $this->result(false, 'ANULADA', 'La factura está anulada.');
        }

        if (blank($invoice->bold_payment_link)) {
            return $this->result(false, 'SIN_LINK', 'La factura no tiene un link de Bold que consultar.');
        }

        $setting = BusinessBoldSetting::query()->where('business_id', $invoice->business_id)->first();

        $answer = BoldClient::forBusiness($setting)->linkStatusDetailed((string) $invoice->bold_payment_link);
        $link = $answer['body'];

        $check = BoldStatusCheck::query()->create([
            'payment_link'      => $invoice->bold_payment_link,
            'reference'         => $invoice->bold_reference,
            'origin'            => $origin,
            'http_status'       => $answer['http_status'],
            'reported_status'   => (string) ($link['status'] ?? '') ?: null,
            'reported_amount'   => isset($link['total']) ? round((float) $link['total'], 2) : null,
            'reported_currency' => (string) ($link['currency'] ?? '') ?: null,
            'transaction_id'    => (string) ($link['transaction_id'] ?? '') ?: null,
            'payment_method'    => (string) ($link['payment_method'] ?? '') ?: null,
            'is_sandbox'        => array_key_exists('is_sandbox', $link) ? (bool) $link['is_sandbox'] : null,
            'raw_response'      => $answer['raw'],
            'error'             => $answer['error'],
            'duration_ms'       => $answer['duration_ms'],
            'user_id'           => auth()->id(),
            'ip'                => request()?->ip(),
        ]);

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

        return $this->settle($invoice, $link, $check);
    }

    /**
     * @param  array<string, mixed>  $link
     * @return array{changed: bool, status: string, detail: string}
     */
    private function settle(WorkOrderInvoice $invoice, array $link, BoldStatusCheck $check): array
    {
        $transaction_id = trim((string) ($link['transaction_id'] ?? ''));

        if ($transaction_id === '') {
            return $this->close($check, false, 'PAID_SIN_TRANSACCION',
                'Bold da el link por pagado pero no informa identificador de transacción. No se confirma.');
        }

        $expected = round((float) $invoice->total, 2);
        $reported = round((float) ($link['total'] ?? 0), 2);

        if (abs($reported - $expected) > self::AMOUNT_TOLERANCE) {
            return $this->close($check, false, 'MONTO_NO_CUADRA',
                "Bold reporta {$reported} y la factura es por {$expected}. No se confirma.");
        }

        $method = (string) ($link['payment_method'] ?? '');

        $invoice->forceFill([
            'bold_status'         => 'PAID',
            'bold_payment_id'     => $transaction_id,
            'bold_payment_method' => $method ?: $invoice->bold_payment_method,
        ])->save();

        SystemActor::run(fn () => RegisterInvoicePaymentAction::run(
            invoice: $invoice,
            payment_method: $method ?: 'Pago en línea',
            payment_reference: $transaction_id,
            paid_at: now()->toDateString(),
        ));

        return $this->close($check, true, 'PAID',
            "Factura {$invoice->reference} saldada: Bold reporta PAID con la transacción {$transaction_id}.");
    }

    /** @return array{changed: bool, status: string, detail: string} */
    private function close(BoldStatusCheck $check, bool $confirmed, string $status, string $detail): array
    {
        $check->forceFill(['confirmed' => $confirmed, 'decision' => $detail])->save();

        return $this->result($confirmed, $status, $detail);
    }

    /** @return array{changed: bool, status: string, detail: string} */
    private function result(bool $changed, string $status, string $detail): array
    {
        return ['changed' => $changed, 'status' => $status, 'detail' => $detail];
    }
}
