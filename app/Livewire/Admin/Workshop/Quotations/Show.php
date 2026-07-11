<?php

namespace App\Livewire\Admin\Workshop\Quotations;

use App\Actions\Workshop\DeleteQuotationAction;
use App\Actions\Workshop\UpdateQuotationStatusAction;
use App\Enums\QuotationStatus;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Quotation;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cotización')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public Quotation $quotation;

    public string $status = '';

    public string $reject_reason = '';

    public function mount(Quotation $quotation): void
    {
        abort_unless(auth()->user()?->can('workshop.quotations.view'), 403);

        abort_unless(
            Quotation::query()->forAuthUser()->whereKey($quotation->id)->exists(),
            404
        );

        $this->quotation = $quotation->load([
            'client', 'equipment', 'quotationServiceType', 'paymentMethod', 'bankAccount',
            'items.itemType', 'items.itemCategory', 'items.catalogItem', 'createdBy', 'business',
        ]);

        $this->status        = $this->quotation->status->value;
        $this->reject_reason = (string) ($this->quotation->reject_reason ?? '');
    }

    public function updatedStatus(string $value): void
    {
        if ($value !== QuotationStatus::Rechazada->value) {
            return;
        }

        $this->quotation->refresh();
        $this->reject_reason = (string) ($this->quotation->reject_reason ?? '');
    }

    public function updateStatus(): void
    {
        abort_unless(auth()->user()?->can('workshop.quotations.edit'), 403);

        $this->validate([
            'status'        => ['required', Rule::enum(QuotationStatus::class)],
            'reject_reason' => ['required_if:status,' . QuotationStatus::Rechazada->value, 'nullable', 'string', 'max:500'],
        ], [
            'status.required'           => 'Selecciona un estado.',
            'status.enum'               => 'El estado seleccionado no es válido.',
            'reject_reason.required_if' => 'Indica el motivo del rechazo.',
            'reject_reason.max'         => 'El motivo no puede superar 500 caracteres.',
        ]);

        $new_status    = QuotationStatus::from($this->status);
        $reject_reason = $new_status === QuotationStatus::Rechazada
            ? trim($this->reject_reason)
            : null;

        $status_unchanged = $new_status === $this->quotation->status;
        $reason_unchanged = (string) ($reject_reason ?? '') === (string) ($this->quotation->reject_reason ?? '');

        if ($status_unchanged && ($new_status !== QuotationStatus::Rechazada || $reason_unchanged)) {
            $this->dispatch('swal', [
                'title' => 'Sin cambios',
                'text'  => 'La cotización ya tiene ese estado.',
                'icon'  => 'info',
            ]);

            return;
        }

        $this->quotation = UpdateQuotationStatusAction::run(
            $this->quotation->id,
            $new_status,
            $reject_reason
        );

        $this->status        = $this->quotation->status->value;
        $this->reject_reason = (string) ($this->quotation->reject_reason ?? '');

        $this->dispatch('swal', [
            'title' => 'Estado actualizado',
            'text'  => 'La cotización ahora está: ' . $this->quotation->status->label(),
            'icon'  => 'success',
        ]);
    }

    public function deleteQuotation(): void
    {
        abort_unless(auth()->user()?->can('workshop.quotations.delete'), 403);
        $this->askDeleteConfirmation($this->quotation->id, '¿Eliminar esta cotización?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteQuotationAction::run($this->delete_id);
            $this->alertDeleteSuccess('Cotización eliminada correctamente.');
            $this->redirectRoute('admin.workshop.quotations.index', navigate: true);
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar la cotización.');
        }
    }

    public function render()
    {
        return view('livewire.admin.workshop.quotations.show', [
            'category_subtotals' => $this->quotation->subtotalsByPdfCategory(),
            'can_change_status'  => auth()->user()->can('workshop.quotations.edit'),
            'status_options'     => QuotationStatus::options(),
        ]);
    }
}
