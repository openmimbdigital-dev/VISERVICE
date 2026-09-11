<?php

namespace App\Http\Controllers;

use App\Actions\Subscriptions\ConfirmSubscriptionPaymentAction;
use App\Models\BoldWebhookEvent;
use App\Models\SubscriptionInvoice;
use App\Actions\RegisterInvoicePaymentAction;
use App\Models\BusinessBoldSetting;
use App\Models\SubscriptionPayment;
use App\Models\WorkOrderInvoice;
use App\Support\SystemActor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
 *  - Reintenta el mismo evento, así que se procesa una sola vez.
 *  - Firma con HMAC-SHA256 sobre el cuerpo crudo en base64. Se valida antes de
 *    mirar el contenido: un cuerpo sin firma válida no mueve dinero.
 *
 * El orden es deliberado: primero se registra la entrega —siempre, entienda o no
 * lo que llegó—, después se procesa, y al final se guarda lo que respondimos. Así
 * ninguna llamada puede desaparecer sin dejar rastro, que es la única forma de
 * distinguir «Bold no avisó» de «Bold avisó y no supimos leerlo». Un evento
 * inválido igual responde 200 para que Bold no lo reintente en vano.
 */
class BoldWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $started_at = microtime(true);

        $event = $this->recordArrival($request);

        try {
            $response = $this->process($event);
        } catch (Throwable $exception) {
            report($exception);
            $event->update(['result' => 'Error no controlado: '.$exception->getMessage()]);

            $response = $this->ok('error');
        }

        $this->recordResponse($event, $response, $started_at);

        return $response;
    }

    /**
     * Deja constancia de la entrega antes de intentar entenderla.
     *
     * Nada de lo que entre por aquí se descarta sin guardarse: cuerpo crudo,
     * cabeceras, IP y firma quedan tal como llegaron, aunque el contenido no se
     * pueda interpretar.
     */
    private function recordArrival(Request $request): BoldWebhookEvent
    {
        $raw_body = $request->getContent();
        $decoded = json_decode($raw_body, true);
        $payload = is_array($decoded) ? $decoded : null;

        $signature = (string) $request->header((string) config('bold.webhook.signature_header'), '');
        $signed_with = $this->matchingSecret($raw_body, $signature);

        $data = (array) ($payload['data'] ?? []);
        $amount = (array) ($data['amount'] ?? []);
        $reference = $this->referenceIn($payload ?? []);

        return BoldWebhookEvent::query()->create([
            'event_id'                => (string) ($payload['id'] ?? '') ?: $this->syntheticId($raw_body),
            'type'                    => (string) ($payload['type'] ?? '') ?: 'SIN_TIPO',
            'reference'               => $reference ?: null,
            'payment_id'              => (string) ($data['payment_id'] ?? $data['transaction_id'] ?? $payload['subject'] ?? '') ?: null,
            'amount'                  => isset($amount['total']) ? round((float) $amount['total'], 2) : null,
            'currency'                => (string) ($amount['currency'] ?? '') ?: null,
            'payment_method'          => (string) ($data['payment_method'] ?? '') ?: null,
            'event_time'              => (string) ($payload['time'] ?? '') ?: null,
            'subscription_invoice_id' => $this->invoiceFor($reference, $payload ?? [])?->id,
            'work_order_invoice_id'   => WorkOrderInvoice::findByBoldReference($reference)?->id,
            'signature_valid'         => $signed_with !== null,
            'signature'               => $signature ?: null,
            'signed_with'             => $signed_with,
            'processed'               => false,
            'payload'                 => $payload,
            'received_at'             => now(),
            'ip'                      => $request->ip(),
            'user_agent'              => mb_substr((string) $request->userAgent(), 0, 255) ?: null,
            'http_method'             => $request->method(),
            'url'                     => mb_substr($request->fullUrl(), 0, 255),
            'headers'                 => $this->headers($request),
            'raw_body'                => $raw_body,
            'body_bytes'              => strlen($raw_body),
        ]);
    }

    /** Guarda lo que Bold recibió de vuelta: de eso depende que reintente o no. */
    private function recordResponse(BoldWebhookEvent $event, JsonResponse $response, float $started_at): void
    {
        $event->forceFill([
            'response_status' => $response->getStatusCode(),
            'response_body'   => mb_substr((string) $response->getContent(), 0, 500),
            'duration_ms'     => (int) round((microtime(true) - $started_at) * 1000),
        ])->save();
    }

    private function process(BoldWebhookEvent $event): JsonResponse
    {
        if (! $event->isReadable()) {
            $event->update(['result' => 'El cuerpo no es un JSON interpretable; queda tal cual en «raw_body».']);

            Log::warning('Webhook de Bold con cuerpo no interpretable.', ['evento' => $event->id]);

            return $this->ok('cuerpo inválido');
        }

        if (! $event->signature_valid) {
            $event->update(['result' => $this->signatureFailureDetail((string) $event->signature)]);

            Log::warning('Webhook de Bold con firma inválida.', [
                'evento'          => $event->id,
                'event_id'        => $event->event_id,
                'firma_recibida'  => $event->signature ? mb_substr((string) $event->signature, 0, 16).'…' : '(sin cabecera)',
                'llaves_probadas' => array_keys($this->candidateSecrets()),
            ]);

            return $this->ok('firma inválida');
        }

        // Idempotencia: el reintento de un evento ya procesado queda registrado
        // como entrega, pero no vuelve a mover el cobro.
        $processed = BoldWebhookEvent::query()
            ->where('event_id', $event->event_id)
            ->where('id', '!=', $event->id)
            ->where('processed', true)
            ->first();

        if ($processed) {
            $event->update(['result' => "Reintento de un evento ya procesado en el registro #{$processed->id}."]);

            return $this->ok('evento ya recibido');
        }

        // Un mismo endpoint recibe los dos cobros: la suscripción que nos paga el
        // negocio y la factura que le paga su cliente. La referencia dice cuál es.
        if ($event->workOrderInvoice) {
            return $this->confirmWorkOrderInvoice($event);
        }

        $invoice = $event->subscriptionInvoice;

        if (! $invoice) {
            $event->update(['result' => $event->reference
                ? "No se encontró un cobro con la referencia «{$event->reference}»."
                : 'El evento no trae referencia ni link conocido: no hay cómo saber a qué cobro corresponde.']);

            return $this->ok('cobro no encontrado');
        }

        if ($event->type !== BoldWebhookEvent::TYPE_SALE_APPROVED) {
            $invoice->forceFill(['bold_status' => $this->statusFor($event->type)])->save();

            $event->update(['result' => "Evento «{$event->type}»: se anotó el estado en el cobro, sin darlo por pagado."]);

            return $this->ok('evento sin efecto sobre el cobro');
        }

        try {
            $this->confirm($invoice, $event);
        } catch (Throwable $exception) {
            report($exception);
            $event->update(['result' => 'Error al confirmar: '.$exception->getMessage()]);

            // Se responde 200 igual: el evento ya quedó guardado y reintentarlo
            // no arreglaría el problema. Queda visible en la bitácora.
            return $this->ok('error al confirmar');
        }

        return $this->ok('procesado');
    }

    private function confirm(SubscriptionInvoice $invoice, BoldWebhookEvent $event): void
    {
        $data = (array) ($event->payload['data'] ?? []);
        $payment_id = (string) $event->payment_id;

        $invoice->forceFill([
            'bold_status'         => 'PAID',
            'bold_payment_id'     => $payment_id ?: null,
            'bold_payment_method' => $event->payment_method,
        ])->save();

        // Las acciones que generan la OT y la factura piden permisos, y aquí no
        // hay sesión: se actúa como el superAdmin de la plataforma, que además
        // deja la autoría correcta en la bitácora.
        $work_order_invoice = SystemActor::run(fn () => ConfirmSubscriptionPaymentAction::run(
            invoice: $invoice,
            payment_method: $event->payment_method ?: 'en línea',
            payment_reference: $payment_id ?: null,
            paid_at: now()->toDateTimeString(),
            channel: SubscriptionPayment::CHANNEL_ONLINE,
            gateway_data: $data,
            gateway: 'bold',
            gateway_source: (string) ($event->payload['source'] ?? '') ?: null,
        ));

        $signature_note = $event->signed_with ? " [firmado con: {$event->signed_with}]" : '';

        $event->update([
            'processed' => true,
            'result'    => ($work_order_invoice
                ? "Cobro {$invoice->invoice_number} confirmado y facturado ({$work_order_invoice->reference})."
                : "Cobro {$invoice->invoice_number} confirmado; no se generó factura.").$signature_note,
        ]);
    }

    /**
     * Da por pagada la factura de taller que el cliente acaba de pagar.
     *
     * Pasa por la misma acción que el botón de la pantalla, así que el estado, la
     * fecha y la línea de tiempo quedan igual vengan del cliente o del taller.
     */
    private function confirmWorkOrderInvoice(BoldWebhookEvent $event): JsonResponse
    {
        $invoice = $event->workOrderInvoice;

        if ($event->type !== BoldWebhookEvent::TYPE_SALE_APPROVED) {
            $invoice->forceFill(['bold_status' => $this->statusFor($event->type)])->save();
            $event->update(['result' => "Evento «{$event->type}» sobre la factura {$invoice->reference}: anotado sin darla por pagada."]);

            return $this->ok('evento sin efecto sobre la factura');
        }

        $invoice->forceFill([
            'bold_status'         => 'PAID',
            'bold_payment_id'     => $event->payment_id,
            'bold_payment_method' => $event->payment_method,
        ])->save();

        if ($invoice->status === 'pagada') {
            $event->update(['processed' => true, 'result' => "La factura {$invoice->reference} ya estaba pagada."]);

            return $this->ok('factura ya pagada');
        }

        SystemActor::run(fn () => RegisterInvoicePaymentAction::run(
            invoice: $invoice,
            payment_method: $event->payment_method ?: 'Pago en línea',
            payment_reference: $event->payment_id,
            paid_at: now()->toDateString(),
        ));

        $event->update([
            'processed' => true,
            'result'    => "Factura {$invoice->reference} pagada en línea"
                .($event->signed_with ? " [firmado con: {$event->signed_with}]" : '').'.',
        ]);

        return $this->ok('procesado');
    }

    /**
     * Cabeceras tal como llegaron, salvo las que puedan traer credenciales.
     *
     * @return array<string, mixed>
     */
    private function headers(Request $request): array
    {
        $hidden = ['authorization', 'cookie', 'proxy-authorization', 'x-api-key'];
        $headers = [];

        foreach ($request->headers->all() as $name => $values) {
            $headers[$name] = in_array($name, $hidden, true)
                ? '(oculto)'
                : (count($values) === 1 ? (string) $values[0] : $values);
        }

        return $headers;
    }

    /**
     * Cobro al que corresponde el evento: por nuestra referencia y, si no la
     * trae, por el identificador del link.
     *
     * @param  array<string, mixed>  $payload
     */
    private function invoiceFor(string $reference, array $payload = []): ?SubscriptionInvoice
    {
        return SubscriptionInvoice::findByBoldReference($reference) ?? $this->invoiceByLink($payload);
    }

    /**
     * Último recurso: el identificador del link de pago. Si el evento llega sin
     * nuestra referencia, el link sigue siendo un hilo válido porque lo guardamos
     * al generarlo.
     *
     * @param  array<string, mixed>  $payload
     */
    private function invoiceByLink(array $payload): ?SubscriptionInvoice
    {
        $data = (array) ($payload['data'] ?? []);

        foreach ([$data['payment_link'] ?? null, $data['link_id'] ?? null, $data['link'] ?? null] as $link) {
            if (! is_string($link) || trim($link) === '') {
                continue;
            }

            $invoice = SubscriptionInvoice::query()->where('bold_payment_link', trim($link))->first();

            if ($invoice) {
                return $invoice;
            }
        }

        return null;
    }

    /**
     * Nuestra referencia dentro del evento.
     *
     * En los eventos de venta Bold la devuelve en «data.metadata.reference», pero
     * no todos los tipos de integración la ponen en el mismo sitio. Se miran las
     * variantes conocidas antes de darla por ausente, que es lo que deja un cobro
     * sin acreditar.
     *
     * @param  array<string, mixed>  $payload
     */
    private function referenceIn(array $payload): string
    {
        $data = (array) ($payload['data'] ?? []);

        $candidates = [
            $data['metadata']['reference'] ?? null,
            $data['reference'] ?? null,
            $payload['metadata']['reference'] ?? null,
            $payload['reference'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return '';
    }

    /**
     * Identificador propio para un evento que llegó sin el suyo. Se deriva del
     * cuerpo, así que un reintento del mismo evento sigue siendo el mismo y la
     * idempotencia lo sigue reconociendo.
     */
    private function syntheticId(string $raw_body): string
    {
        $hash = md5('bold-sin-id|'.$raw_body);

        return implode('-', [
            mb_substr($hash, 0, 8),
            mb_substr($hash, 8, 4),
            mb_substr($hash, 12, 4),
            mb_substr($hash, 16, 4),
            mb_substr($hash, 20, 12),
        ]);
    }

    /**
     * Llave con la que viene firmado el evento, o null si con ninguna cuadra.
     *
     * Bold firma con HMAC-SHA256 sobre el cuerpo crudo codificado en base64, en
     * hexadecimal, y la llave depende del tipo de integración por el que entró
     * el pago. Se prueban todas las conocidas —comparando en tiempo constante— y
     * se devuelve cuál coincidió, que es lo que después se registra.
     */
    private function matchingSecret(string $raw_body, string $signature): ?string
    {
        if ($signature === '') {
            return null;
        }

        $encoded = base64_encode($raw_body);

        foreach ($this->candidateSecrets() as $label => $secret) {
            if (hash_equals(hash_hmac('sha256', $encoded, $secret), $signature)) {
                return $label;
            }
        }

        return null;
    }

    /**
     * Llaves con las que puede venir firmado un evento.
     *
     * @return array<string, string>
     */
    private function candidateSecrets(): array
    {
        $candidates = [];

        if (filled(config('bold.secret_key'))) {
            $candidates['SECRET_KEY_BOLD'] = (string) config('bold.secret_key');
        }

        foreach ((array) config('bold.webhook.extra_secrets') as $index => $secret) {
            $candidates['extra #'.($index + 1)] = (string) $secret;
        }

        // Cada negocio que cobra con su propia cuenta firma con su propia llave:
        // el endpoint es uno solo, así que hay que reconocerlas todas.
        foreach (BusinessBoldSetting::query()->where('active', true)->get() as $setting) {
            if (filled($setting->secret_key)) {
                $candidates['negocio #'.$setting->business_id] = (string) $setting->secret_key;
            }
        }

        // La llave vacía se suma a las reales, no las reemplaza: así un pago de
        // prueba firmado con llave real se sigue aceptando.
        if (config('bold.webhook.test_mode')) {
            $candidates['llave vacía (modo pruebas)'] = '';
        }

        return $candidates;
    }

    /** Mensaje que explica por qué no cuadró la firma, para la bitácora. */
    private function signatureFailureDetail(string $signature): string
    {
        if ($signature === '') {
            return 'Llegó sin la cabecera de firma: no se procesó.';
        }

        $tried = implode(', ', array_keys($this->candidateSecrets()));

        if ($tried === '') {
            return 'Firma inválida: no hay ninguna llave configurada con la cual verificarla.';
        }

        return 'Firma inválida: no coincide con ninguna de las llaves probadas ('.$tried.'). '
            .'Si el pago sí ocurrió, es que Bold firmó con otra llave; agrégala en '
            .'BOLD_WEBHOOK_EXTRA_SECRETS.';
    }

    private function statusFor(string $type): string
    {
        return match ($type) {
            BoldWebhookEvent::TYPE_SALE_REJECTED => 'REJECTED',
            BoldWebhookEvent::TYPE_VOID_APPROVED => 'VOIDED',
            BoldWebhookEvent::TYPE_VOID_REJECTED => 'VOID_REJECTED',
            default => 'PROCESSING',
        };
    }

    private function ok(string $result): JsonResponse
    {
        return response()->json(['received' => true, 'result' => $result]);
    }
}
