<?php

namespace App\Console\Commands;

use App\Models\BoldWebhookEvent;
use App\Models\SubscriptionInvoice;
use App\Services\Bold\BoldClient;
use Illuminate\Console\Command;
use Throwable;

/**
 * Responde la pregunta que uno se hace cuando un pago en línea no se acredita:
 * ¿avisó Bold y no supimos con qué quedarnos, o nunca avisó?
 *
 * Mira las tres cosas que pueden fallar —la configuración, lo que llegó al
 * webhook, y lo que Bold dice del link— y las pone juntas, que es donde se ve
 * la respuesta. No cambia nada: solo cuenta lo que hay.
 */
class BoldDiagnose extends Command
{
    protected $signature = 'bold:diagnose {--events=10 : Cuántos eventos del webhook mostrar}';

    protected $description = 'Revisa la integración con Bold: configuración, eventos recibidos y estado real de los cobros';

    public function handle(): int
    {
        $this->configuration();
        $this->connection();
        $events = $this->events();
        $pending = $this->pendingLinks();

        $this->verdict($events, $pending);

        return self::SUCCESS;
    }

    private function configuration(): void
    {
        $identity = (string) config('bold.identity_key');
        $secret = (string) config('bold.secret_key');
        $extras = (array) config('bold.webhook.extra_secrets');

        $this->components->info('Configuración');

        $this->table(['Dato', 'Valor'], [
            ['URL de la API', config('bold.base_url')],
            ['IDENTITY_KEY_BOLD', $identity !== '' ? mb_substr($identity, 0, 6).'… ('.mb_strlen($identity).' caracteres)' : 'AUSENTE'],
            ['SECRET_KEY_BOLD', $secret !== '' ? mb_substr($secret, 0, 4).'… ('.mb_strlen($secret).' caracteres)' : 'AUSENTE'],
            ['Llaves adicionales', $extras === [] ? 'ninguna' : count($extras).' en BOLD_WEBHOOK_EXTRA_SECRETS'],
            ['Modo de pruebas', config('bold.webhook.test_mode') ? 'SÍ (acepta también firma con llave vacía)' : 'no'],
            ['URL del webhook', route('webhooks.bold')],
        ]);

        if (config('bold.webhook.test_mode')) {
            $this->components->warn('BOLD_WEBHOOK_TEST_MODE está en true. En producción debe ir en false.');
        }
    }

    private function connection(): void
    {
        $this->components->info('Conexión con Bold');

        if (! BoldClient::make()->isConfigured()) {
            $this->components->error('Falta la llave de identidad: no se puede consultar nada.');

            return;
        }

        try {
            $methods = BoldClient::make()->paymentMethods();
            $this->components->twoColumnDetail('Métodos habilitados', implode(', ', array_keys($methods)) ?: 'ninguno');
        } catch (Throwable $exception) {
            $this->components->error('Bold no respondió: '.$exception->getMessage());
        }
    }

    /** @return int Cuántos eventos hay registrados en total. */
    private function events(): int
    {
        $total = BoldWebhookEvent::query()->count();

        $this->components->info("Eventos recibidos en el webhook (total: {$total})");

        if ($total === 0) {
            $this->line('  No hay ninguno. Bold nunca ha llamado a esta URL.');

            return 0;
        }

        $rows = BoldWebhookEvent::query()
            ->latest('id')
            ->limit(max(1, (int) $this->option('events')))
            ->get()
            ->map(fn (BoldWebhookEvent $event) => [
                $event->received_at?->format('d/m H:i:s') ?? $event->created_at?->format('d/m H:i:s'),
                $event->type,
                $event->reference ?? '—',
                $event->amount !== null ? number_format((float) $event->amount, 0, ',', '.') : '—',
                $event->signature_valid ? ($event->signed_with ?? 'sí') : 'NO',
                $event->processed ? 'sí' : 'no',
                $event->response_status.' · '.$event->duration_ms.'ms',
                mb_substr((string) $event->result, 0, 45),
            ])
            ->all();

        $this->table(
            ['Recibido', 'Tipo', 'Referencia', 'Monto', 'Firmado con', 'Procesado', 'Respuesta', 'Resultado'],
            $rows
        );

        $this->line('  El cuerpo completo de cada entrega está en «raw_body» y «headers».');

        return $total;
    }

    /** @return int Cuántos cobros pendientes tienen un link de Bold. */
    private function pendingLinks(): int
    {
        $invoices = SubscriptionInvoice::query()
            ->whereNotNull('bold_payment_link')
            ->where('status', 'pending')
            ->latest('id')
            ->limit(10)
            ->get();

        $this->components->info("Cobros pendientes con link de Bold ({$invoices->count()})");

        if ($invoices->isEmpty()) {
            $this->line('  Ninguno: no hay pagos en línea sin acreditar.');

            return 0;
        }

        $rows = [];

        foreach ($invoices as $invoice) {
            $rows[] = [
                $invoice->invoice_number,
                $invoice->bold_payment_link,
                $invoice->bold_status ?? '—',
                $this->remoteStatus($invoice),
            ];
        }

        $this->table(['Cobro', 'Link', 'Estado guardado', 'Estado real en Bold'], $rows);

        return $invoices->count();
    }

    /** Lo que Bold dice hoy del link, que es la verdad frente a lo que tengamos guardado. */
    private function remoteStatus(SubscriptionInvoice $invoice): string
    {
        try {
            $link = BoldClient::make()->linkStatus((string) $invoice->bold_payment_link);
        } catch (Throwable $exception) {
            return 'no se pudo consultar';
        }

        $status = (string) ($link['status'] ?? '?');
        $sandbox = ! empty($link['is_sandbox']) ? ' · entorno de pruebas' : ' · producción';

        return $status.$sandbox;
    }

    private function verdict(int $events, int $pending): void
    {
        $this->components->info('Conclusión');

        if ($events === 0) {
            $this->line('  Bold no ha llamado nunca al webhook. El endpoint responde —se puede');
            $this->line('  comprobar con un POST firmado—, así que lo que falta está del lado de');
            $this->line('  Bold: revisar en el panel que el webhook esté registrado para «Pagos en');
            $this->line('  línea» y en el mismo entorno (pruebas o producción) por el que entró el pago.');

            return;
        }

        $unsigned = BoldWebhookEvent::query()->where('signature_valid', false)->count();
        $unmatched = BoldWebhookEvent::query()->whereNull('subscription_invoice_id')->count();

        if ($unsigned > 0) {
            $this->line("  Hay {$unsigned} evento(s) con firma que no cuadró: Bold firmó con una llave");
            $this->line('  distinta a las configuradas. Agrégala en BOLD_WEBHOOK_EXTRA_SECRETS.');
        }

        if ($unmatched > 0) {
            $this->line("  Hay {$unmatched} evento(s) que no se pudieron asociar a un cobro. Mirar su");
            $this->line('  «payload» en bold_webhook_events para ver con qué referencia vinieron.');
        }

        if ($pending > 0) {
            $this->line('  Para acreditar lo que Bold ya dé por pagado: php artisan bold:sync-payments');
        }

        if ($unsigned === 0 && $unmatched === 0 && $pending === 0) {
            $this->line('  Todo en orden: los eventos llegan, se validan y se procesan.');
        }
    }
}
