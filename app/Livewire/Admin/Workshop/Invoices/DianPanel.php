<?php

namespace App\Livewire\Admin\Workshop\Invoices;

use App\Actions\Dian\DownloadElectronicInvoiceFilesAction;
use App\Actions\Dian\EmitElectronicInvoiceAction;
use App\Actions\Dian\SyncElectronicInvoiceStatusAction;
use App\Models\BusinessDianSetting;
use App\Models\DianRequestLog;
use App\Models\ElectronicInvoice;
use App\Models\WorkOrderInvoice;
use App\Services\Dian\DianRequestException;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class DianPanel extends Component
{
    public WorkOrderInvoice $invoice;

    public bool $show_xml = false;

    public bool $show_logs = false;

    public ?int $expanded_log_id = null;

    public function mount(WorkOrderInvoice $invoice): void
    {
        abort_unless(auth()->user()?->can('workshop.invoices.view'), 403);

        $this->invoice = $invoice;
    }

    public function send(): void
    {
        abort_unless(auth()->user()?->can('workshop.invoices.dian.send'), 403);

        try {
            EmitElectronicInvoiceAction::run($this->invoice);
        } catch (DianRequestException $exception) {
            $this->dispatch('swal', [
                'title' => 'La DIAN rechazó el documento',
                'text'  => $exception->getMessage(),
                'icon'  => 'error',
            ]);

            return;
        } catch (ValidationException $exception) {
            $this->dispatch('swal', [
                'title' => 'No se pudo emitir',
                'text'  => collect($exception->errors())->flatten()->first(),
                'icon'  => 'warning',
            ]);

            return;
        }

        $this->invoice->refresh();

        $this->dispatch('swal', [
            'title' => 'Factura enviada a la DIAN',
            'icon'  => 'success',
        ]);
    }

    public function syncStatus(): void
    {
        $electronic_invoice = $this->electronicInvoice();

        if (! $electronic_invoice) {
            return;
        }

        try {
            SyncElectronicInvoiceStatusAction::run($electronic_invoice);
        } catch (DianRequestException|ValidationException $exception) {
            $this->dispatch('swal', [
                'title' => 'No se pudo consultar el estado',
                'text'  => $exception->getMessage(),
                'icon'  => 'warning',
            ]);

            return;
        }

        $this->invoice->refresh();

        $this->dispatch('swal', ['title' => 'Estado actualizado', 'icon' => 'success']);
    }

    public function downloadFiles(): void
    {
        abort_unless(auth()->user()?->can('workshop.invoices.dian.download'), 403);

        $electronic_invoice = $this->electronicInvoice();

        if (! $electronic_invoice) {
            return;
        }

        try {
            DownloadElectronicInvoiceFilesAction::run($electronic_invoice);
        } catch (DianRequestException|ValidationException $exception) {
            $this->dispatch('swal', [
                'title' => 'No se pudieron descargar los archivos',
                'text'  => $exception->getMessage(),
                'icon'  => 'warning',
            ]);

            return;
        }

        $this->invoice->refresh();

        $this->dispatch('swal', ['title' => 'Archivos descargados', 'icon' => 'success']);
    }

    public function toggleXml(): void
    {
        $this->show_xml = ! $this->show_xml;
    }

    public function toggleLogs(): void
    {
        $this->show_logs = ! $this->show_logs;
        $this->expanded_log_id = null;
    }

    public function toggleLogDetail(int $log_id): void
    {
        $this->expanded_log_id = $this->expanded_log_id === $log_id ? null : $log_id;
    }

    private function electronicInvoice(): ?ElectronicInvoice
    {
        return ElectronicInvoice::query()
            ->forAuthUser()
            ->where('work_order_invoice_id', $this->invoice->id)
            ->first();
    }

    public function render()
    {
        $electronic_invoice = $this->electronicInvoice();

        $setting = BusinessDianSetting::query()
            ->with('business.city')
            ->where('business_id', $this->invoice->business_id)
            ->first();

        $logs = DianRequestLog::query()
            ->forAuthUser()
            ->where('work_order_invoice_id', $this->invoice->id)
            ->with('createdBy')
            ->latest()
            ->limit(30)
            ->get();

        return view('livewire.admin.workshop.invoices.dian-panel', [
            'electronic_invoice' => $electronic_invoice,
            'setting'            => $setting,
            'logs'               => $logs,
            'missing'            => EmitElectronicInvoiceAction::missingRequirements($this->invoice, $setting),
            'can_send'           => auth()->user()->can('workshop.invoices.dian.send'),
            'can_download'       => auth()->user()->can('workshop.invoices.dian.download'),
        ]);
    }
}
