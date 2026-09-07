<?php

namespace App\Actions\Dian;

use App\Models\ElectronicInvoice;
use App\Services\Dian\TitanioClient;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Consulta en el proveedor la cronología de estados del documento y actualiza
 * el estado local (enviada / validada / rechazada).
 */
class SyncElectronicInvoiceStatusAction
{
    use AsAction;

    public function handle(ElectronicInvoice $electronic_invoice): ElectronicInvoice
    {
        abort_unless(auth()->user()?->can('workshop.invoices.view'), 403);

        if (! $electronic_invoice->transaction_id) {
            throw ValidationException::withMessages([
                'dian' => 'La factura aún no tiene una transacción en el proveedor.',
            ]);
        }

        $timeline = TitanioClient::for($electronic_invoice->environment)
            ->forInvoice($electronic_invoice)
            ->documentStatus((int) $electronic_invoice->transaction_id);

        return $electronic_invoice->applyProviderTimeline($timeline)->refresh();
    }
}
