<?php

namespace App\Console\Commands;

use App\Actions\Subscriptions\SyncBoldPaymentStatusAction;
use App\Models\SubscriptionInvoice;
use App\Services\Bold\BoldClient;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Red de seguridad del webhook de Bold.
 *
 * Si la notificación no llega —no está registrada para ese entorno, viene
 * firmada con otra llave, o se perdió— el comercio pagó y su cuenta sigue sin
 * activar. Esta tarea le pregunta a Bold por cada link pendiente y confirma los
 * que ya están cobrados, así que el pago se acredita igual, con unos minutos de
 * demora en vez de nunca.
 */
class BoldSyncPayments extends Command
{
    protected $signature = 'bold:sync-payments
        {--invoice= : Un cobro puntual, por número, id o referencia}
        {--days=30 : Antigüedad máxima del link, en días}';

    protected $description = 'Consulta en Bold los cobros con link pendiente y confirma los que ya están pagados';

    public function handle(): int
    {
        if (! BoldClient::make()->isConfigured()) {
            $this->warn('La pasarela Bold no está configurada: no hay nada que consultar.');

            return self::SUCCESS;
        }

        $invoices = $this->invoices();

        if ($invoices->isEmpty()) {
            $this->info('No hay cobros con link de Bold por consultar.');

            return self::SUCCESS;
        }

        $rows = [];
        $confirmed = 0;

        foreach ($invoices as $invoice) {
            $result = SyncBoldPaymentStatusAction::run($invoice);

            if ($result['changed']) {
                $confirmed++;
            }

            $rows[] = [
                $invoice->invoice_number,
                $invoice->bold_payment_link,
                $result['status'],
                $result['detail'],
            ];
        }

        $this->table(['Cobro', 'Link', 'Estado en Bold', 'Resultado'], $rows);
        $this->info("Cobros confirmados en esta pasada: {$confirmed}");

        return self::SUCCESS;
    }

    /** @return Collection<int, SubscriptionInvoice> */
    private function invoices(): Collection
    {
        $query = SubscriptionInvoice::query()->whereNotNull('bold_payment_link');

        $wanted = trim((string) $this->option('invoice'));

        // Un cobro puntual se consulta aunque ya figure pagado o el link esté
        // viejo: es justo lo que uno quiere al revisar un caso concreto.
        if ($wanted !== '') {
            return $query
                ->where(fn ($sub) => $sub
                    ->where('invoice_number', $wanted)
                    ->orWhere('bold_reference', $wanted)
                    ->orWhere('id', $wanted))
                ->get();
        }

        $days = max(1, (int) $this->option('days'));

        return $query
            ->where('status', 'pending')
            ->where(fn ($sub) => $sub
                ->whereNull('bold_link_created_at')
                ->orWhere('bold_link_created_at', '>=', now()->subDays($days)))
            ->orderBy('id')
            ->get();
    }
}
