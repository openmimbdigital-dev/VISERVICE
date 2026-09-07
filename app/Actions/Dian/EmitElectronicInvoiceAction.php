<?php

namespace App\Actions\Dian;

use App\Actions\LogUserHistoricalAction;
use App\Enums\ElectronicInvoiceStatus;
use App\Models\BusinessDianSetting;
use App\Models\ElectronicInvoice;
use App\Models\WorkOrderInvoice;
use App\Services\Dian\DianRequestException;
use App\Services\Dian\InvoiceXmlBuilder;
use App\Services\Dian\TitanioClient;
use App\Support\DianNit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

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

        $xml = (new InvoiceXmlBuilder())->build($electronic_invoice, $invoice, $setting);

        $electronic_invoice->forceFill([
            'request_xml'   => $xml,
            'attempts'      => $electronic_invoice->attempts + 1,
            'error_id'      => null,
            'error_message' => null,
        ])->save();

        try {
            $result = TitanioClient::for($setting->environment)->emit((int) $setting->tr_tipo_id, $xml);
        } catch (DianRequestException $exception) {
            $electronic_invoice->forceFill([
                'status'           => ElectronicInvoiceStatus::Error,
                'error_id'         => $exception->errorId,
                'error_message'    => $exception->getMessage(),
                'response_payload' => $exception->response !== [] ? $exception->response : null,
            ])->save();

            throw $exception;
        }

        $electronic_invoice->forceFill([
            'status'           => ElectronicInvoiceStatus::Sent,
            'transaction_id'   => $result['tr_id'],
            'cufe'             => $result['cufe'] !== '' ? $result['cufe'] : null,
            'qr_code'          => $result['qr'] !== '' ? $result['qr'] : null,
            'response_payload' => $result['raw'],
            'sent_at'          => now(),
        ])->save();

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

        $client = $invoice->workOrder?->client;

        if (! $client) {
            $missing[] = 'La orden de trabajo no tiene cliente asociado.';
        } else {
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
     * Recupera el documento electrónico de la factura o reserva un consecutivo nuevo.
     * Un reintento conserva siempre el número ya asignado.
     */
    private function resolveElectronicInvoice(WorkOrderInvoice $invoice, BusinessDianSetting $setting): ElectronicInvoice
    {
        $existing = ElectronicInvoice::query()->where('work_order_invoice_id', $invoice->id)->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($invoice, $setting) {
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

            $electronic_invoice = ElectronicInvoice::query()->create([
                'business_id'           => $invoice->business_id,
                'work_order_invoice_id' => $invoice->id,
                'document_type'         => 'invoice',
                'environment'           => $locked_setting->environment,
                'prefix'                => $locked_setting->prefix,
                'consecutive'           => $consecutive,
                'document_number'       => $locked_setting->prefix.$consecutive,
                'status'                => ElectronicInvoiceStatus::Pending,
                'issued_at'             => now(),
                'created_by'            => auth()->id(),
            ]);

            $locked_setting->update(['next_consecutive' => $consecutive + 1]);

            return $electronic_invoice;
        });
    }
}
