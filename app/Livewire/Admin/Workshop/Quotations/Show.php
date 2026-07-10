<?php

namespace App\Livewire\Admin\Workshop\Quotations;

use App\Actions\Workshop\DeleteQuotationAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Quotation;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cotización')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public Quotation $quotation;

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
            'can_edit'           => auth()->user()->can('workshop.quotations.edit') && $this->quotation->isEditable(),
            'can_delete'         => $this->quotation->canDelete(),
        ]);
    }
}
