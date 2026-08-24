<?php

namespace App\Livewire\Admin\Businesses\CustomTaxes;

use App\Actions\Business\DeleteCustomTaxAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\CustomTax;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Impuesto personalizado')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public CustomTax $custom_tax;

    public function mount(CustomTax $customTax): void
    {
        abort_unless(auth()->user()?->can('custom_taxes.view'), 403);

        abort_unless(
            CustomTax::query()->forAuthUser()->whereKey($customTax->id)->exists(),
            404
        );

        $this->custom_tax = $customTax->load('business');
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()?->can('custom_taxes.delete'), 403);

        $this->askDeleteConfirmation($this->custom_tax->id, '¿Eliminar este impuesto?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteCustomTaxAction::run($this->delete_id);

            $this->alertDeleteSuccess('Impuesto eliminado correctamente.');

            $this->redirectRoute('admin.custom-taxes.index', navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el impuesto.');
        }
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.admin.businesses.custom-taxes.show', [
            'can_edit'   => $user->can('custom_taxes.edit')
                && $this->custom_tax->isEditableBy($user, 'custom_taxes.edit'),
            'can_delete' => $user->can('custom_taxes.delete')
                && $this->custom_tax->canDelete($user),
        ]);
    }
}
