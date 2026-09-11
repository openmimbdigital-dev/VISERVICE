<?php

namespace App\Actions\Workshop;

use App\Actions\Dian\EmitCreditNoteAction;
use App\Actions\LogUserHistoricalAction;
use App\Enums\ElectronicInvoiceStatus;
use App\Enums\WorkOrderStatus;
use App\Models\BusinessDianSetting;
use App\Models\ElectronicInvoice;
use App\Models\WorkOrderInvoice;
use App\Models\WorkOrderInvoiceStatusHistory;
use App\Services\Dian\DianRequestException;
use App\Services\Dian\TitanioClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Anula una factura de orden de trabajo y, con ella, su OT.
 *
 * Lo que se puede hacer depende por completo de dónde esté el documento
 * electrónico, y la frontera la marca la DIAN:
 *
 *  - **Sin emitir**: no hay nada que deshacer afuera.
 *  - **Emitida pero sin validar**: el manual del proveedor permite borrar la
 *    transacción «solo si no ha sido enviada a la DIAN», y eso además libera el
 *    número para volver a usarlo. Se intenta.
 *  - **Validada por la DIAN**: no se borra nada, se emite una nota crédito que
 *    la acredita. Una factura validada existe ante la DIAN para siempre, así
 *    que marcarla «anulada» por dentro sin más dejaría nuestros libros
 *    diciendo una cosa y los de la DIAN otra. La nota es lo que hace que
 *    vuelvan a coincidir. Si el negocio todavía no tiene configuradas las
 *    notas crédito, no se anula: primero hay que poder acreditarla.
 *
 * La OT se cancela junto con la factura. Se pasa «cascade: false» cuando la
 * llamada viene de la propia cancelación de la OT, para que no se llamen en
 * círculo.
 */
class CancelWorkOrderInvoiceAction
{
    use AsAction;

    public function handle(
        WorkOrderInvoice $invoice,
        string $reason,
        bool $cascade_work_order = true,
    ): WorkOrderInvoice {
        abort_unless(auth()->user()?->can('workshop.invoices.void'), 403);

        $invoice = WorkOrderInvoice::query()->forAuthUser()->findOrFail($invoice->id);

        $reason = trim($reason);

        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'Indica el motivo de la anulación.',
            ]);
        }

        if ($invoice->status === 'anulada') {
            throw ValidationException::withMessages([
                'invoice' => 'Esta factura ya está anulada.',
            ]);
        }

        $electronic_invoice = ElectronicInvoice::query()
            ->where('work_order_invoice_id', $invoice->id)
            ->first();

        $this->guardAgainstValidatedDocument($invoice);

        // Si la DIAN ya la validó, primero se acredita. Si la nota falla, la
        // factura se queda viva a propósito: anularla por dentro dejando el
        // documento en pie ante la DIAN es justo el descuadre que se evita.
        $credit_note = $electronic_invoice?->status->isValidatedByDian()
            ? EmitCreditNoteAction::run($invoice)
            : null;

        $withdrawn = $credit_note ? null : $this->withdrawFromProvider($electronic_invoice);

        $previous_status = (string) $invoice->status;

        DB::transaction(function () use ($invoice, $reason, $previous_status, $withdrawn, $credit_note) {
            $invoice->forceFill(['status' => 'anulada'])->save();

            RecordInvoiceStatusHistoryAction::run(
                invoice: $invoice,
                kind: WorkOrderInvoiceStatusHistory::KIND_BILLING,
                to_status: 'anulada',
                from_status: $previous_status,
                comment: $reason,
                metadata: array_filter([
                    'withdrawn_from_provider' => $withdrawn ?: null,
                    'credit_note'             => $credit_note?->document_number,
                ]),
            );
        });

        LogUserHistoricalAction::run(
            action: 'updated',
            module: 'workshop.invoices',
            description: "Anuló la factura {$invoice->reference}",
            subject: $invoice,
            subject_label: $invoice->reference,
            properties: [
                'from'                    => $previous_status,
                'reason'                  => $reason,
                'withdrawn_from_provider' => $withdrawn,
                'credit_note'             => $credit_note?->document_number,
            ],
            business_id: (int) $invoice->business_id,
        );

        if ($cascade_work_order) {
            $this->cancelWorkOrder($invoice, $reason);
        }

        return $invoice->refresh();
    }

    /**
     * Motivo por el que esta factura no se puede anular, o null si sí se puede.
     *
     * Se expone para poder avisar antes de intentarlo —al cancelar la OT, o al
     * pintar el botón— en vez de dejar que reviente a mitad de camino.
     */
    public static function blockingReason(WorkOrderInvoice $invoice): ?string
    {
        $electronic_invoice = ElectronicInvoice::query()
            ->where('work_order_invoice_id', $invoice->id)
            ->first();

        if ($electronic_invoice?->status->isValidatedByDian() !== true) {
            return null;
        }

        $setting = BusinessDianSetting::query()
            ->where('business_id', $invoice->business_id)
            ->first();

        // Con notas crédito configuradas sí se puede: se acredita en vez de borrar.
        if ($setting?->canEmitCreditNotes()) {
            return null;
        }

        return "La DIAN ya validó el documento {$electronic_invoice->document_number}, "
            .'así que dejarlo sin efecto exige una nota crédito. Este negocio todavía no las tiene '
            .'configuradas: hay que registrar su prefijo y su perfil en la configuración DIAN.';
    }

    /**
     * Una factura ya validada no se anula: se corrige con una nota crédito.
     */
    private function guardAgainstValidatedDocument(WorkOrderInvoice $invoice): void
    {
        $reason = self::blockingReason($invoice);

        if ($reason !== null) {
            throw ValidationException::withMessages(['invoice' => $reason]);
        }
    }

    /**
     * Retira el documento del proveedor, si todavía se puede.
     *
     * Que falle no impide anular la factura en nuestros libros: el documento no
     * llegó a la DIAN, así que lo peor que queda es una transacción huérfana allá
     * y un número que no se reutiliza. Quede dicho en la bitácora.
     */
    private function withdrawFromProvider(?ElectronicInvoice $electronic_invoice): ?string
    {
        if (! $electronic_invoice || ! $electronic_invoice->transaction_id) {
            return null;
        }

        try {
            TitanioClient::for($electronic_invoice->environment)
                ->deleteTransaction((int) $electronic_invoice->transaction_id);
        } catch (DianRequestException $exception) {
            $electronic_invoice->forceFill([
                'status'        => ElectronicInvoiceStatus::Cancelled,
                'error_message' => 'No se pudo retirar del proveedor al anular: '.$exception->getMessage(),
            ])->save();

            return 'falló: '.$exception->getMessage();
        }

        $document_number = (string) $electronic_invoice->document_number;

        $electronic_invoice->forceFill([
            'status'         => ElectronicInvoiceStatus::Cancelled,
            'transaction_id' => null,
            'error_message'  => null,
        ])->save();

        return "transacción retirada; el número {$document_number} quedó libre en el proveedor";
    }

    /** Cancela la OT de la factura, si todavía no lo está. */
    private function cancelWorkOrder(WorkOrderInvoice $invoice, string $reason): void
    {
        $work_order = $invoice->workOrder;

        if (! $work_order || $work_order->status === WorkOrderStatus::Cancelled) {
            return;
        }

        UpdateWorkOrderStatusAction::run(
            work_order_id: (int) $work_order->id,
            status: WorkOrderStatus::Cancelled,
            comment: "Se anuló la factura {$invoice->reference}: {$reason}",
            force: true,
            cascade_invoices: false,
        );
    }
}
