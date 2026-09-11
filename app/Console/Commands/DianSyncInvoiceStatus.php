<?php

namespace App\Console\Commands;

use App\Enums\ElectronicInvoiceStatus;
use App\Models\ElectronicInvoice;
use App\Services\Dian\DianRequestException;
use App\Services\Dian\TitanioClient;
use Illuminate\Console\Command;
use Throwable;

class DianSyncInvoiceStatus extends Command
{
    protected $signature = 'dian:sync-status {--limit=50 : Máximo de documentos a consultar}';

    protected $description = 'Consulta en el proveedor el estado de las facturas electrónicas pendientes de validación';

    public function handle(): int
    {
        // No se insiste indefinidamente: lo que lleva días sin resolverse no se
        // arregla preguntando más veces, y cada consulta es una llamada al
        // proveedor. Queda el botón de la pantalla para revisarlo a mano.
        $max_age_days = max(1, (int) config('dian.status_sync.max_age_days', 7));

        $pending = ElectronicInvoice::query()
            ->whereNotNull('transaction_id')
            ->whereIn('status', [ElectronicInvoiceStatus::Sent->value])
            ->where(fn ($query) => $query
                ->whereNull('sent_at')
                ->orWhere('sent_at', '>=', now()->subDays($max_age_days)))
            ->orderBy('sent_at')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No hay facturas electrónicas pendientes de validación.');

            return self::SUCCESS;
        }

        $this->line("Consultando {$pending->count()} documento(s)...");
        $updated = 0;

        foreach ($pending as $electronic_invoice) {
            try {
                $timeline = TitanioClient::for($electronic_invoice->environment)
                    ->forInvoice($electronic_invoice)
                    ->documentStatus((int) $electronic_invoice->transaction_id);
            } catch (DianRequestException $exception) {
                $this->warn("  {$electronic_invoice->document_number}: {$exception->getMessage()}");

                continue;
            } catch (Throwable $exception) {
                $this->warn("  {$electronic_invoice->document_number}: {$exception->getMessage()}");

                continue;
            }

            $electronic_invoice->applyProviderTimeline($timeline);

            $this->line("  {$electronic_invoice->document_number}: {$electronic_invoice->status->label()}");
            $updated++;
        }

        $this->info("Actualizados {$updated} documento(s).");

        return self::SUCCESS;
    }
}
