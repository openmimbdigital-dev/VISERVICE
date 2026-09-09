<?php

namespace App\Actions\Dian;

use App\Actions\Workshop\RecordInvoiceStatusHistoryAction;
use App\Models\ElectronicInvoice;
use App\Models\WorkOrderInvoiceStatusHistory;
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

        $previous_status = $electronic_invoice->status;

        $summary = TitanioClient::for($electronic_invoice->environment)
            ->forInvoice($electronic_invoice)
            ->transactionSummary((int) $electronic_invoice->transaction_id);

        $electronic_invoice->applyProviderTimeline($summary['timeline']);

        // El motivo de rechazo de la DIAN solo viene en el detalle de la transacción.
        if ($summary['dian_error'] !== null) {
            $electronic_invoice->forceFill(['error_message' => $summary['dian_error']])->save();
        }

        // Solo se anota cuando la consulta movió el estado: consultar cada rato
        // no debe llenar la línea de tiempo de pasos repetidos.
        if ($electronic_invoice->status !== $previous_status && $electronic_invoice->invoice) {
            RecordInvoiceStatusHistoryAction::run(
                invoice: $electronic_invoice->invoice,
                kind: WorkOrderInvoiceStatusHistory::KIND_EMISSION,
                to_status: $electronic_invoice->status->value,
                from_status: $previous_status->value,
                comment: $summary['dian_error'],
                metadata: [
                    'document_number' => $electronic_invoice->document_number,
                    'transaction_id'  => $electronic_invoice->transaction_id,
                    'provider_timeline' => $summary['timeline'] !== '' ? $summary['timeline'] : null,
                ],
            );
        }

        return $electronic_invoice->refresh();
    }
}
