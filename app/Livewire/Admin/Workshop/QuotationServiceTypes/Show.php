<?php

namespace App\Livewire\Admin\Workshop\QuotationServiceTypes;

use App\Actions\Workshop\DeleteQuotationServiceTypeAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\QuotationServiceType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tipo de servicio')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public QuotationServiceType $service_type;

    public function mount(QuotationServiceType $quotationServiceType): void
    {
        abort_unless(auth()->user()?->can('workshop.quotation_service_types.view'), 403);

        abort_unless(
            QuotationServiceType::query()->visibleToUser()->whereKey($quotationServiceType->id)->exists(),
            404
        );

        $this->service_type = $quotationServiceType->load('business');
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()?->can('workshop.quotation_service_types.delete'), 403);
        $this->askDeleteConfirmation($this->service_type->id, '¿Eliminar este tipo de servicio?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteQuotationServiceTypeAction::run($this->delete_id);
            $this->alertDeleteSuccess('Tipo eliminado correctamente.');
            $this->redirectRoute('admin.workshop.quotation-service-types.index', navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el tipo.');
        }
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.admin.workshop.quotation-service-types.show', [
            'can_edit'            => $this->service_type->isEditableBy($user)
                && $user->can('workshop.quotation_service_types.edit'),
            'can_delete'          => $this->service_type->canDelete($user),
            'is_general_readonly' => $this->service_type->isGeneralReadonly($user),
        ]);
    }
}
