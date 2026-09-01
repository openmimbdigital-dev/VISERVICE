<?php

namespace App\Livewire\Admin\Businesses\PaymentMethods;

use App\Actions\Business\CreateOrUpdateBusinessPaymentMethodAction;
use App\Livewire\Forms\Admin\Businesses\BusinessPaymentMethodForm;
use App\Models\BusinessPaymentMethod;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Métodos de pago')]
class Index extends Component
{
    public BusinessPaymentMethodForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('business_payment_methods.view'), 403);

        $edit_id = request()->integer('edit');

        if ($edit_id > 0) {
            $this->openEdit($edit_id);
        }
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('business_payment_methods.create'), 403);

        $this->form->reset();
        $this->form->active = true;

        if ($this->form->isSuperAdmin()) {
            $this->form->general = true;
        } else {
            $this->form->business_id = auth()->user()->businessIds()[0] ?? null;
        }

        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('open-business-payment-method-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('business_payment_methods.edit'), 403);

        $method = BusinessPaymentMethod::query()->visibleToUser()->findOrFail($id);
        abort_unless($method->isEditableBy(auth()->user(), 'business_payment_methods.edit'), 403);

        $this->form->setPaymentMethod($method);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('business-payment-method-deleted')]
    public function onRecordDeleted(): void {}

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->form->reset();
        $this->form->active = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can($this->form->isEditing() ? 'business_payment_methods.edit' : 'business_payment_methods.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        CreateOrUpdateBusinessPaymentMethodAction::run(
            $this->form->payment_method_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Método actualizado' : 'Método creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('business-payment-method-saved');
    }

    public function render()
    {
        $query = BusinessPaymentMethod::query()->visibleToUser();

        return view('livewire.admin.businesses.payment-methods.index', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'businesses'     => $this->form->getBusinesses(),
            'stats'          => [
                'total'   => (clone $query)->count(),
                'active'  => (clone $query)->where('active', true)->count(),
                'general' => (clone $query)->where('general', true)->count(),
                'default' => (clone $query)->where('is_default', true)->count(),
            ],
        ]);
    }
}
