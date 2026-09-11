<?php

namespace App\Actions\Dian;

use App\Actions\LogUserHistoricalAction;
use App\Actions\Workshop\RecordInvoiceStatusHistoryAction;
use App\Enums\ElectronicInvoiceStatus;
use App\Models\BusinessDianSetting;
use App\Models\ElectronicCreditNote;
use App\Models\ElectronicInvoice;
use App\Models\WorkOrderInvoice;
use App\Models\WorkOrderInvoiceStatusHistory;
use App\Services\Dian\CreditNoteDocumentBuilder;
use App\Services\Dian\DianRequestException;
use App\Services\Dian\TitanioClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Emite la nota crédito que deja sin efecto una factura ya validada.
 *
 * Una factura validada existe ante la DIAN para siempre; no hay manera de
 * borrarla. La nota crédito es el documento con el que se la acredita, y por eso
 * se emite por su cuenta: tiene su propio perfil en el proveedor, su propia
 * numeración y su propio CUDE.
 *
 * Solo cubre la anulación completa —se acredita exactamente lo que se cobró—.
 * Una devolución parcial es otro documento y otra conversación.
 */
class EmitCreditNoteAction
{
    use AsAction;

    public function handle(
        WorkOrderInvoice $invoice,
        ?string $reason_code = null,
        ?string $reason_description = null,
    ): ElectronicCreditNote {
        abort_unless(auth()->user()?->can('workshop.invoices.void'), 403);

        $setting = BusinessDianSetting::query()
            ->with('business.city')
            ->where('business_id', $invoice->business_id)
            ->first();

        $original = $this->originalDocument($invoice);

        $this->guardSetting($setting);

        $reason_code ??= (string) config('dian.credit_note.void_reason_code', '2');
        $reason_description ??= (string) (config('dian.credit_note.reasons')[$reason_code] ?? 'Anulación de factura electrónica');

        $credit_note = $this->reserveConsecutive($invoice, $setting);

        $document = (new CreditNoteDocumentBuilder())->buildCreditNote(
            $credit_note,
            $original,
            $invoice,
            $setting,
            $reason_code,
            $reason_description,
        );

        $credit_note->forceFill([
            'request_document' => $document,
            'attempts'         => $credit_note->attempts + 1,
            'error_id'         => null,
            'error_message'    => null,
        ])->save();

        try {
            $result = TitanioClient::for($setting->environment)
                ->forInvoice($credit_note)
                ->emit((int) $setting->credit_note_tr_tipo_id, $document);
        } catch (DianRequestException $exception) {
            $credit_note->forceFill([
                'status'           => ElectronicInvoiceStatus::Error,
                'error_id'         => $exception->errorId,
                'error_message'    => $exception->getMessage(),
                'response_payload' => $exception->response !== [] ? $exception->response : null,
            ])->save();

            throw $exception;
        }

        $credit_note->forceFill([
            'status'           => ElectronicInvoiceStatus::Sent,
            'transaction_id'   => $result['tr_id'],
            'cufe'             => $result['cufe'] !== '' ? $result['cufe'] : null,
            'qr_code'          => $result['qr'] !== '' ? $result['qr'] : null,
            'response_payload' => $result['raw'],
            'sent_at'          => now(),
        ])->save();

        RecordInvoiceStatusHistoryAction::run(
            invoice: $invoice,
            kind: WorkOrderInvoiceStatusHistory::KIND_EMISSION,
            to_status: ElectronicInvoiceStatus::Sent->value,
            from_status: ElectronicInvoiceStatus::Accepted->value,
            comment: "Nota crédito {$credit_note->document_number}: {$reason_description}",
            metadata: [
                'credit_note_number' => $credit_note->document_number,
                'voided_document'    => $original->document_number,
                'reason_code'        => $reason_code,
                'transaction_id'     => $credit_note->transaction_id,
            ],
        );

        LogUserHistoricalAction::run(
            action: 'created',
            module: 'workshop.invoices',
            description: "Emitió la nota crédito {$credit_note->document_number} que anula {$original->document_number}",
            subject: $invoice,
            subject_label: $invoice->reference,
            properties: [
                'credit_note_number' => $credit_note->document_number,
                'voided_document'    => $original->document_number,
                'reason_code'        => $reason_code,
                'cufe'               => $credit_note->cufe,
            ],
            business_id: (int) $invoice->business_id,
        );

        return $credit_note->refresh();
    }

    /**
     * El documento que se va a acreditar.
     *
     * Tiene que estar validado y traer su CUFE: la nota crédito se identifica
     * ante la DIAN por la referencia a ese CUFE, y sin él el documento nace
     * inservible con un consecutivo ya gastado.
     */
    private function originalDocument(WorkOrderInvoice $invoice): ElectronicInvoice
    {
        $original = ElectronicInvoice::query()
            ->where('work_order_invoice_id', $invoice->id)
            ->first();

        if (! $original || ! $original->status->isValidatedByDian()) {
            throw ValidationException::withMessages([
                'dian' => 'Solo se emite nota crédito sobre una factura que la DIAN ya validó.',
            ]);
        }

        if (blank($original->cufe)) {
            throw ValidationException::withMessages([
                'dian' => "El documento {$original->document_number} no tiene CUFE guardado, "
                    .'y la nota crédito tiene que referenciarlo. Actualiza su estado antes de continuar.',
            ]);
        }

        return $original;
    }

    private function guardSetting(?BusinessDianSetting $setting): void
    {
        if (! $setting) {
            throw ValidationException::withMessages([
                'dian' => 'Este negocio no tiene configurada la facturación electrónica.',
            ]);
        }

        if (! $setting->canEmitCreditNotes()) {
            throw ValidationException::withMessages([
                'dian' => 'Falta configurar las notas crédito: su prefijo y su perfil en el proveedor. '
                    .'Se registra desde la configuración DIAN del negocio.',
            ]);
        }
    }

    /**
     * Reserva el siguiente número de nota crédito.
     *
     * Con el mismo candado que la facturación: el número se toma dentro de una
     * transacción para que dos anulaciones a la vez no se lleven el mismo.
     */
    private function reserveConsecutive(WorkOrderInvoice $invoice, BusinessDianSetting $setting): ElectronicCreditNote
    {
        return DB::transaction(function () use ($invoice, $setting) {
            $locked = BusinessDianSetting::query()
                ->whereKey($setting->id)
                ->lockForUpdate()
                ->firstOrFail();

            $consecutive = $locked->upcomingCreditNoteConsecutive();

            $credit_note = ElectronicCreditNote::query()->create([
                'business_id'           => $invoice->business_id,
                'work_order_invoice_id' => $invoice->id,
                'environment'           => $locked->environment,
                'prefix'                => $locked->credit_note_prefix,
                'consecutive'           => $consecutive,
                'document_number'       => $locked->credit_note_prefix.$consecutive,
                'status'                => ElectronicInvoiceStatus::Pending,
                'issued_at'             => now(),
                'created_by'            => auth()->id(),
            ]);

            $locked->update(['credit_note_next_consecutive' => $consecutive + 1]);

            return $credit_note;
        });
    }
}
