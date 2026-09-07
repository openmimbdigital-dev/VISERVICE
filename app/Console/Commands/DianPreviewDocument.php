<?php

namespace App\Console\Commands;

use App\Actions\Dian\EmitElectronicInvoiceAction;
use App\Enums\ElectronicInvoiceStatus;
use App\Models\BusinessDianSetting;
use App\Models\ElectronicInvoice;
use App\Models\WorkOrderInvoice;
use App\Services\Dian\InvoiceDocumentBuilder;
use Illuminate\Console\Command;

class DianPreviewDocument extends Command
{
    protected $signature = 'dian:preview-document {invoice : ID de la factura de orden de trabajo}
                            {--save= : Ruta donde guardar el documento generado}';

    protected $description = 'Genera el documento de facturación electrónica de una factura sin enviarlo al proveedor';

    public function handle(): int
    {
        $invoice = WorkOrderInvoice::query()
            ->with(['business.city.country', 'workOrder.client.city.country', 'items.workOrderItem.catalogProduct.unit'])
            ->find($this->argument('invoice'));

        if (! $invoice) {
            $this->error('No existe la factura indicada.');

            return self::FAILURE;
        }

        $setting = BusinessDianSetting::query()->where('business_id', $invoice->business_id)->first();

        if (! $setting) {
            $this->error('El negocio de esta factura no tiene configuración de facturación electrónica.');

            return self::FAILURE;
        }

        $missing = EmitElectronicInvoiceAction::missingRequirements($invoice, $setting);

        if ($missing !== []) {
            $this->warn('Requisitos pendientes para poder emitir:');

            foreach ($missing as $item) {
                $this->line('  - '.$item);
            }

            $this->newLine();
        }

        // Documento en memoria: no reserva consecutivo ni toca la base de datos.
        $electronic_invoice = $invoice->electronicInvoice ?? new ElectronicInvoice([
            'business_id'           => $invoice->business_id,
            'work_order_invoice_id' => $invoice->id,
            'environment'           => $setting->environment,
            'prefix'                => $setting->prefix,
            'consecutive'           => $setting->upcomingConsecutive(),
            'document_number'       => $setting->prefix.$setting->upcomingConsecutive(),
            'status'                => ElectronicInvoiceStatus::Pending,
            'issued_at'             => now(),
        ]);

        $document = (new InvoiceDocumentBuilder())->build($electronic_invoice, $invoice, $setting);

        if ($path = $this->option('save')) {
            file_put_contents($path, $document);
            $this->info("Documento guardado en {$path}");

            return self::SUCCESS;
        }

        $this->line('Formato del perfil: '.$setting->document_format);
        $this->newLine();
        $this->line($document);

        return self::SUCCESS;
    }
}
