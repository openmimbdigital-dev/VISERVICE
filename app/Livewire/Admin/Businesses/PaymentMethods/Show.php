<?php

namespace App\Livewire\Admin\Businesses\PaymentMethods;

use App\Actions\Business\DeleteBusinessPaymentMethodAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\BusinessPaymentMethod;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Método de pago')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public BusinessPaymentMethod $payment_method;

    public function mount(BusinessPaymentMethod $paymentMethod): void
    {
        abort_unless(auth()->user()?->can('business_payment_methods.view'), 403);

        abort_unless(
            BusinessPaymentMethod::query()->visibleToUser()->whereKey($paymentMethod->id)->exists(),
            404
        );

        $this->payment_method = $paymentMethod->load('business');
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()?->can('business_payment_methods.delete'), 403);

        $this->askDeleteConfirmation($this->payment_method->id, '¿Eliminar este método de pago?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteBusinessPaymentMethodAction::run($this->delete_id);

            $this->alertDeleteSuccess('Método eliminado correctamente.');

            $this->redirectRoute('admin.business-payment-methods.index', navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el método.');
        }
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.admin.businesses.payment-methods.show', [
            'can_edit'            => $this->payment_method->isEditableBy($user, 'business_payment_methods.edit'),
            'can_delete'          => $this->payment_method->canDelete($user),
            'is_general_readonly' => $this->payment_method->isGeneralReadonly($user),
        ]);
    }
}
