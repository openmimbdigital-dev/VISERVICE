<?php

namespace App\Http\Controllers;

use App\Actions\Subscriptions\ConfirmSubscriptionPaymentAction;
use App\Models\BoldWebhookEvent;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Recibe las notificaciones de pago de Bold.
 *
 * Reglas del proveedor que condicionan el diseño de este endpoint:
 *
 *  - Espera un 200 en menos de dos segundos y reintenta hasta cinco veces si no
 *    lo recibe. Por eso aquí no va nada lento: confirmar el cobro crea la OT y
 *    su factura, que son escrituras locales, pero la emisión ante la DIAN queda
 *    por fuera y se dispara aparte.
 *  - Reintenta el mismo evento, así que se descarta por «event_id».
 *  - Firma con HMAC-SHA256 sobre el cuerpo crudo en base64. Se valida antes de
 *    mirar el contenido: un cuerpo sin firma válida no mueve dinero.
 *
 * Un evento inválido igual responde 200 para que Bold no lo reintente en vano;
 * queda registrado con el motivo.
 */
class BoldWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $raw_body = $request->getContent();
        $signature = (string) $request->header((string) config('bold.webhook.signature_header'), '');
        $signature_valid = $this->signatureIsValid($raw_body, $signature);

        $payload = json_decode($raw_body, true);

        if (! is_array($payload)) {
            Log::warning('Webhook de Bold con cuerpo no interpretable.');

            return $this->ok('cuerpo inválido');
        }

        $event_id = (string) ($payload['id'] ?? '');

        if ($event_id === '') {
            return $this->ok('evento sin identificador');
        }

        // Idempotencia: si el evento ya se registró, no se vuelve a procesar.
        if (BoldWebhookEvent::query()->where('event_id', $event_id)->exists()) {
            return $this->ok('evento ya recibido');
        }

        $type = (string) ($payload['type'] ?? '');
        $data = (array) ($payload['data'] ?? []);
        $reference = (string) ($data['metadata']['reference'] ?? '');
        $payment_id = (string) ($data['payment_id'] ?? '');

        $invoice = $reference !== ''
            ? SubscriptionInvoice::query()->where('bold_reference', $reference)->first()
            : null;

        $event = BoldWebhookEvent::query()->create([
            'event_id'                => $event_id,
            'type'                    => $type,
            'reference'               => $reference ?: null,
            'payment_id'              => $payment_id ?: null,
            'subscription_invoice_id' => $invoice?->id,
            'signature_valid'         => $signature_valid,
            'processed'               => false,
            'payload'                 => $payload,
        ]);

        if (! $signature_valid) {
            $event->update(['result' => 'Firma inválida: no se procesó.']);
            Log::warning('Webhook de Bold con firma inválida.', ['event_id' => $event_id]);

            return $this->ok('firma inválida');
        }

        if (! $invoice) {
            $event->update(['result' => 'No se encontró un cobro con esa referencia.']);

            return $this->ok('cobro no encontrado');
        }

        if ($type !== BoldWebhookEvent::TYPE_SALE_APPROVED) {
            $invoice->forceFill(['bold_status' => $this->statusFor($type)])->save();
            $event->update(['result' => 'Evento registrado sin cambiar el cobro.']);

            return $this->ok('evento sin efecto sobre el cobro');
        }

        try {
            $this->confirm($invoice, $data, $payment_id, $event);
        } catch (Throwable $exception) {
            report($exception);
            $event->update(['result' => 'Error al confirmar: '.$exception->getMessage()]);

            // Se responde 200 igual: el evento ya quedó guardado y reintentarlo
            // no arreglaría el problema. Queda visible en la bitácora.
            return $this->ok('error al confirmar');
        }

        return $this->ok('procesado');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function confirm(SubscriptionInvoice $invoice, array $data, string $payment_id, BoldWebhookEvent $event): void
    {
        $invoice->forceFill([
            'bold_status'         => 'PAID',
            'bold_payment_id'     => $payment_id ?: null,
            'bold_payment_method' => (string) ($data['payment_method'] ?? '') ?: null,
        ])->save();

        // Las acciones que generan la OT y la factura piden permisos, y aquí no
        // hay sesión: se actúa como el superAdmin de la plataforma, que además
        // deja la autoría correcta en la bitácora.
        $previous_user = Auth::user();
        $system_user = $this->systemUser();

        if ($system_user) {
            Auth::setUser($system_user);
        }

        try {
            $work_order_invoice = ConfirmSubscriptionPaymentAction::run(
                invoice: $invoice,
                payment_method: 'Bold · '.((string) ($data['payment_method'] ?? 'en línea')),
                payment_reference: $payment_id ?: null,
                paid_at: now()->toDateTimeString(),
            );
        } finally {
            $previous_user ? Auth::setUser($previous_user) : Auth::forgetUser();
        }

        $event->update([
            'processed' => true,
            'result'    => $work_order_invoice
                ? "Cobro confirmado y facturado ({$work_order_invoice->reference})."
                : 'Cobro confirmado; no se generó factura.',
        ]);
    }

    /**
     * Firma HMAC-SHA256 sobre el cuerpo crudo codificado en base64, en
     * hexadecimal. Se compara en tiempo constante.
     */
    private function signatureIsValid(string $raw_body, string $signature): bool
    {
        if ($signature === '') {
            return false;
        }

        $secret = (string) config('bold.secret_key', '');

        // En pruebas Bold firma con llave vacía.
        if (config('bold.webhook.test_mode')) {
            $secret = '';
        }

        $expected = hash_hmac('sha256', base64_encode($raw_body), $secret);

        return hash_equals($expected, $signature);
    }

    private function statusFor(string $type): string
    {
        return match ($type) {
            BoldWebhookEvent::TYPE_SALE_REJECTED => 'REJECTED',
            BoldWebhookEvent::TYPE_VOID_APPROVED => 'VOIDED',
            default => 'PROCESSING',
        };
    }

    private function systemUser(): ?User
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'superAdmin'))
            ->orderBy('id')
            ->first();
    }

    private function ok(string $result): JsonResponse
    {
        return response()->json(['received' => true, 'result' => $result]);
    }
}
