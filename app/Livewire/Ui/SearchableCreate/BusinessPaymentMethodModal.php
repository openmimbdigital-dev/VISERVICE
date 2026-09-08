<?php

namespace App\Livewire\Ui\SearchableCreate;

use App\Actions\Business\CreateOrUpdateBusinessPaymentMethodAction;
use App\Livewire\Forms\Admin\Businesses\BusinessPaymentMethodForm;
use App\Models\BusinessPaymentMethod;
use Livewire\Component;

class BusinessPaymentMethodModal extends Component
{
    public BusinessPaymentMethodForm $form;

    public function mount(string $search = ''): void
    {
        abort_unless(auth()->user()?->can('business_payment_methods.create'), 403);

        $this->form->name = $search;
        $this->form->active = true;

        if ($this->form->isSuperAdmin()) {
            $this->form->general = true;
        } else {
            $this->form->general = false;
            $this->form->business_id = auth()->user()->businessIds()[0] ?? null;
        }
    }

    public function updatedFormGeneral(mixed $value): void
    {
        $this->form->updatedGeneral((bool) $value);
    }

    public function close(): void
    {
        $this->dispatch('searchable-create-closed', modelClass: BusinessPaymentMethod::class);
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('business_payment_methods.create'), 403);

        $method = CreateOrUpdateBusinessPaymentMethodAction::run(null, $this->form->validated());

        $this->dispatch('swal', [
            'title' => 'Forma de pago creada',
            'icon'  => 'success',
        ]);

        $this->dispatch('searchable-created', id: $method->id, modelClass: BusinessPaymentMethod::class);
    }

    public function render()
    {
        return view('livewire.ui.searchable-create.business-payment-method-modal', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'businesses'     => $this->form->getBusinesses(),
        ]);
    }
}
