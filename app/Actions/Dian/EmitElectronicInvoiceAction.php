<?php

namespace App\Actions\Dian;

use App\Actions\LogUserHistoricalAction;
use App\Actions\Workshop\RecordInvoiceStatusHistoryAction;
use App\Enums\ElectronicInvoiceStatus;
use App\Models\BusinessDianSetting;
use App\Models\Client;
use App\Models\ElectronicInvoice;
use App\Models\WorkOrderInvoice;
use App\Models\WorkOrderInvoiceStatusHistory;
use App\Services\Dian\DianRequestException;
use App\Services\Dian\InvoiceDocumentBuilder;
use App\Services\Dian\TitanioClient;
use App\Support\DianNit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class EmitElectronicInvoiceAction
{
    use AsAction;

    public function handle(WorkOrderInvoice $invoice): ElectronicInvoice
    {
        abort_unless(auth()->user()?->can('workshop.invoices.dian.send'), 403);

        $invoice = WorkOrderInvoice::query()
            ->forAuthUser()
            ->with(['business.city.country', 'workOrder.client.city.country', 'items.workOrderItem.catalogProduct.unit'])
            ->findOrFail($invoice->id);

        $setting = BusinessDianSetting::query()
            ->with('business.city')
            ->where('business_id', $invoice->business_id)
            ->first();

        if (! $setting) {
            throw ValidationException::withMessages([
                'dian' => 'Este negocio no tiene configurada la facturación electrónica.',
            ]);
        }

        $missing = self::missingRequirements($invoice, $setting);

        if ($missing !== []) {
            throw ValidationException::withMessages(['dian' => $missing[0]]);
        }

        $electronic_invoice = $this->resolveElectronicInvoice($invoice, $setting);

        if ($electronic_invoice->status->isFinal()) {
            throw ValidationException::withMessages([
                'dian' => 'Esta factura ya fue validada por la DIAN.',
            ]);
        }

        $previous_status = $electronic_invoice->status;
        $retries_left = max(0, (int) config('dian.emission.duplicate_retries', 5));
        $asked_provider = false;

        while (true) {
            try {
                return $this->attemptEmission($invoice, $setting, $electronic_invoice, $previous_status);
            } catch (DianRequestException $exception) {
                // «Documento duplicado» no dice que la factura esté mal: dice que
                // nuestro contador viene atrasado frente a lo que el proveedor ya
                // tiene. Reintentar con el mismo número no arregla nada, así que se
                // toma el siguiente en vez de dejarlo en error esperando a alguien.
                if (! $exception->isDuplicateDocument() || $retries_left <= 0) {
                    $this->recordEmissionFailure($invoice, $electronic_invoice, $exception, $previous_status);

                    throw $exception;
                }

                $retries_left--;

                $this->noteBurntConsecutive($invoice, $electronic_invoice, $exception, $retries_left);

                // Al primer choque se le pregunta al proveedor hasta dónde llegó,
                // en vez de ir subiendo de uno en uno. Un contador muy atrasado
                // —el caso típico tras rehacer la base— se agotaría los reintentos
                // sin llegar nunca al número libre.
                if (! $asked_provider) {
                    $asked_provider = true;
                    $this->catchUpWithProvider($setting);
                }

                $electronic_invoice = $this->resolveElectronicInvoice($invoice, $setting, force_new_consecutive: true);
            }
        }
    }

    /**
     * Un intento completo de emisión: arma el documento, lo manda y deja anotado
     * el resultado. Si el proveedor lo rechaza, deja que la excepción salga para
     * que quien llama decida si vale la pena volver a intentarlo.
     */
    private function attemptEmission(
        WorkOrderInvoice $invoice,
        BusinessDianSetting $setting,
        ElectronicInvoice $electronic_invoice,
        ElectronicInvoiceStatus $previous_status,
    ): ElectronicInvoice {
        $document = (new InvoiceDocumentBuilder())->build($electronic_invoice, $invoice, $setting);

        $electronic_invoice->forceFill([
            'request_document' => $document,
            'attempts'      => $electronic_invoice->attempts + 1,
            'error_id'      => null,
            'error_message' => null,
        ])->save();

        $result = TitanioClient::for($setting->environment)
            ->forInvoice($electronic_invoice)
            ->emit((int) $setting->tr_tipo_id, $document);

        $electronic_invoice->forceFill([
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
            from_status: $previous_status->value,
            metadata: [
                'document_number' => $electronic_invoice->document_number,
                'transaction_id'  => $electronic_invoice->transaction_id,
                'cufe'            => $electronic_invoice->cufe,
                'environment'     => $electronic_invoice->environment,
                'attempt'         => $electronic_invoice->attempts,
            ],
        );

        LogUserHistoricalAction::run(
            action: 'created',
            module: 'workshop.invoices',
            description: "Emitió la factura electrónica {$electronic_invoice->document_number} de {$invoice->reference}",
            subject: $invoice,
            subject_label: $invoice->reference,
            properties: [
                'electronic_invoice_id' => $electronic_invoice->id,
                'document_number'       => $electronic_invoice->document_number,
                'transaction_id'        => $electronic_invoice->transaction_id,
                'cufe'                  => $electronic_invoice->cufe,
                'environment'           => $electronic_invoice->environment,
            ],
            business_id: (int) $invoice->business_id,
        );

        return $electronic_invoice->refresh();
    }

    /** Deja la factura en error, con el motivo que dio el proveedor. */
    private function recordEmissionFailure(
        WorkOrderInvoice $invoice,
        ElectronicInvoice $electronic_invoice,
        DianRequestException $exception,
        ElectronicInvoiceStatus $previous_status,
    ): void {
        $electronic_invoice->forceFill([
            'status'           => ElectronicInvoiceStatus::Error,
            'error_id'         => $exception->errorId,
            'error_message'    => $exception->getMessage(),
            'response_payload' => $exception->response !== [] ? $exception->response : null,
        ])->save();

        RecordInvoiceStatusHistoryAction::run(
            invoice: $invoice,
            kind: WorkOrderInvoiceStatusHistory::KIND_EMISSION,
            to_status: ElectronicInvoiceStatus::Error->value,
            from_status: $previous_status->value,
            comment: $exception->getMessage(),
            metadata: [
                'document_number' => $electronic_invoice->document_number,
                'error_id'        => $exception->errorId,
                'attempt'         => $electronic_invoice->attempts,
            ],
        );

    }

    /**
     * Adelanta la numeración hasta donde va la del proveedor.
     *
     * Si la consulta falla no pasa nada grave: el reintento sigue subiendo de uno
     * en uno, que es lento pero funciona. Lo que no puede es tumbar la emisión.
     */
    private function catchUpWithProvider(BusinessDianSetting $setting): void
    {
        try {
            SyncConsecutiveFromProviderAction::run($setting);
            $setting->refresh();
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Anota que ese número quedó ocupado en el proveedor y que se sigue con el
     * siguiente. El salto en la numeración queda explicado en la línea de tiempo.
     */
    private function noteBurntConsecutive(
        WorkOrderInvoice $invoice,
        ElectronicInvoice $electronic_invoice,
        DianRequestException $exception,
        int $retries_left,
    ): void {
        RecordInvoiceStatusHistoryAction::run(
            invoice: $invoice,
            kind: WorkOrderInvoiceStatusHistory::KIND_EMISSION,
            to_status: $electronic_invoice->status->value,
            from_status: $electronic_invoice->status->value,
            comment: "El proveedor ya tiene el documento {$electronic_invoice->document_number}: se reintenta con el siguiente número.",
            metadata: [
                'document_number' => $electronic_invoice->document_number,
                'error_id'        => $exception->errorId,
                'error_message'   => $exception->getMessage(),
                'retries_left'    => $retries_left,
            ],
        );
    }

    /**
     * Datos que impiden emitir la factura electrónica. Vacío significa que está lista.
     *
     * @return list<string>
     */
    public static function missingRequirements(WorkOrderInvoice $invoice, ?BusinessDianSetting $setting = null): array
    {
        $setting ??= BusinessDianSetting::query()->where('business_id', $invoice->business_id)->first();

        if (! $setting) {
            return ['Este negocio no tiene configurada la facturación electrónica.'];
        }

        $missing = $setting->missingRequirements();

        if ($invoice->status === 'anulada') {
            $missing[] = 'La factura está anulada.';
        }

        // Al consumidor final no se le piden datos: la DIAN acepta un adquiriente
        // no identificado, así que los del cliente real dejan de ser un requisito.
        if (! $invoice->bill_to_final_consumer) {
            $missing = array_merge($missing, self::missingClientRequirements($invoice->workOrder?->client));
        }

        if ($invoice->items->isEmpty()) {
            $missing[] = 'La factura no tiene ítems.';
        }

        if ((float) $invoice->total <= 0) {
            $missing[] = 'El total de la factura debe ser mayor a cero.';
        }

        return array_values(array_unique($missing));
    }

    /**
     * Datos del cliente que la DIAN exige en el documento.
     *
     * Se expone aparte para poder advertirle al usuario antes de generar la
     * factura, cuando todavía no existe el documento contra el cual validar.
     *
     * @return list<string>
     */
    public static function missingClientRequirements(?Client $client): array
    {
        if (! $client) {
            return ['La orden de trabajo no tiene cliente asociado.'];
        }

        $missing = [];

        if (DianNit::normalize($client->document_number) === '') {
            $missing[] = 'El cliente no tiene número de documento registrado.';
        }

        if (blank($client->address)) {
            $missing[] = 'El cliente no tiene dirección registrada.';
        }

        $city = $client->city;

        if ($city && blank($city->dane_code)) {
            $missing[] = "La ciudad del cliente («{$city->name}») no tiene código DANE configurado.";
        }

        return $missing;
    }

    /**
     * Recupera el documento electrónico de la factura o le reserva un consecutivo.
     *
     * Si el envío anterior ni siquiera llegó a crear transacción en el proveedor, el
     * número no se consumió y el reintento lo conserva. Pero si el proveedor ya creó
     * la transacción —aunque la DIAN la haya rechazado— ese número queda quemado: la
     * plataforma responde "Documento duplicado", así que el reintento toma uno nuevo.
     * El historial de cada intento queda en la bitácora de envíos.
     */
    private function resolveElectronicInvoice(
        WorkOrderInvoice $invoice,
        BusinessDianSetting $setting,
        bool $force_new_consecutive = false,
    ): ElectronicInvoice
    {
        $existing = ElectronicInvoice::query()->where('work_order_invoice_id', $invoice->id)->first();

        if ($existing && ! $existing->transaction_id && ! $force_new_consecutive) {
            return $existing;
        }

        return DB::transaction(function () use ($invoice, $setting, $existing) {
            $locked_setting = BusinessDianSetting::query()
                ->whereKey($setting->id)
                ->lockForUpdate()
                ->firstOrFail();

            $consecutive = $locked_setting->upcomingConsecutive();

            if ($locked_setting->range_to !== null && $consecutive > (int) $locked_setting->range_to) {
                throw ValidationException::withMessages([
                    'dian' => 'Se agotó el rango de numeración autorizado en la resolución.',
                ]);
            }

            $attributes = [
                'environment'     => $locked_setting->environment,
                'prefix'          => $locked_setting->prefix,
                'consecutive'     => $consecutive,
                'document_number' => $locked_setting->prefix.$consecutive,
                'status'          => ElectronicInvoiceStatus::Pending,
                'issued_at'       => now(),
            ];

            if ($existing) {
                // Numeración nueva: los datos de la transacción anterior ya no aplican.
                $existing->forceFill($attributes + [
                    'transaction_id' => null,
                    'cufe'           => null,
                    'qr_code'        => null,
                    'dian_status'    => null,
                    'xml_path'       => null,
                    'pdf_path'       => null,
                ])->save();

                $electronic_invoice = $existing;
            } else {
                $electronic_invoice = ElectronicInvoice::query()->create($attributes + [
                    'business_id'           => $invoice->business_id,
                    'work_order_invoice_id' => $invoice->id,
                    'document_type'         => 'invoice',
                    'created_by'            => auth()->id(),
                ]);
            }

            $locked_setting->update(['next_consecutive' => $consecutive + 1]);

            return $electronic_invoice;
        });
    }
}
