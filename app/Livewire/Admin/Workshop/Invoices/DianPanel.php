<?php

namespace App\Livewire\Admin\Workshop\Invoices;

use App\Actions\Dian\DownloadElectronicInvoiceFilesAction;
use App\Actions\Dian\EmitElectronicInvoiceAction;
use App\Actions\Dian\SyncElectronicInvoiceStatusAction;
use App\Enums\ElectronicInvoiceStatus;
use App\Models\BusinessDianSetting;
use App\Models\DianRequestLog;
use App\Models\ElectronicInvoice;
use App\Models\WorkOrderInvoice;
use App\Services\Dian\DianRequestException;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class DianPanel extends Component
{
    /** Cada cuánto se le vuelve a preguntar al proveedor mientras se espera. */
    private const POLL_SECONDS = 30;

    /**
     * Cuántas veces seguidas antes de soltar el asunto.
     *
     * Diez consultas cada treinta segundos son cinco minutos, que cubren de sobra
     * el caso normal: la DIAN suele resolver en segundos. Pasado eso no tiene
     * sentido seguir preguntando desde una pantalla abierta, y la tarea programada
     * sigue haciéndolo por su cuenta.
     */
    private const MAX_POLLS = 10;

    public WorkOrderInvoice $invoice;

    public int $polls = 0;

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

    /**
     * Consulta el estado sin que nadie lo pida.
     *
     * Es lo mismo que hace el botón, con dos diferencias: no avisa de nada
     * —está corriendo solo, interrumpir con un cartel sería absurdo— y si el
     * proveedor falla se calla y espera al siguiente turno.
     */
    public function pollStatus(): void
    {
        $electronic_invoice = $this->electronicInvoice();

        if (! $electronic_invoice || ! $this->isAwaitingDian($electronic_invoice)) {
            return;
        }

        $this->polls++;

        try {
            SyncElectronicInvoiceStatusAction::run($electronic_invoice);
        } catch (DianRequestException|ValidationException $exception) {
            return;
        }

        $this->invoice->refresh();
    }

    /** Hay transacción en el proveedor y la DIAN todavía no ha resuelto. */
    private function isAwaitingDian(?ElectronicInvoice $electronic_invoice): bool
    {
        return $electronic_invoice !== null
            && $electronic_invoice->transaction_id !== null
            && $electronic_invoice->status === ElectronicInvoiceStatus::Sent;
    }

    public function downloadFiles(): void
    {
        abort_unless(auth()->user()?->can('workshop.invoices.dian.download'), 403);

        $electronic_invoice = $this->electronicInvoice();

        if (! $electronic_invoice) {
            return;
        }

        try {
            $result = DownloadElectronicInvoiceFilesAction::run($electronic_invoice);
        } catch (ValidationException $exception) {
            $this->dispatch('swal', [
                'title' => 'No se pudieron descargar los archivos',
                'text'  => collect($exception->errors())->flatten()->first(),
                'icon'  => 'warning',
            ]);

            return;
        } catch (DianRequestException $exception) {
            $this->dispatch('swal', [
                'title' => 'No se pudieron descargar los archivos',
                'text'  => $exception->getMessage(),
                'icon'  => 'warning',
            ]);

            return;
        }

        $this->invoice->refresh();

        $this->dispatch('swal', $result['notice']
            ? ['title' => 'Descarga parcial', 'text' => $result['notice'], 'icon' => 'info']
            : ['title' => 'Archivos descargados', 'icon' => 'success']);
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
            'auto_sync'          => $this->isAwaitingDian($electronic_invoice) && $this->polls < self::MAX_POLLS,
            'poll_exhausted'     => $this->isAwaitingDian($electronic_invoice) && $this->polls >= self::MAX_POLLS,
            'poll_seconds'       => self::POLL_SECONDS,
            'can_send'           => auth()->user()->can('workshop.invoices.dian.send'),
            'can_download'       => auth()->user()->can('workshop.invoices.dian.download'),
        ]);
    }
}
