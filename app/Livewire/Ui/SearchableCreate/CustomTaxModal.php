<?php

namespace App\Livewire\Ui\SearchableCreate;

use App\Actions\Business\CreateOrUpdateCustomTaxAction;
use App\Livewire\Forms\Admin\Businesses\CustomTaxForm;
use App\Models\CustomTax;
use Livewire\Component;

class CustomTaxModal extends Component
{
    public CustomTaxForm $form;

    public function mount(string $search = ''): void
    {
        abort_unless(auth()->user()?->can('custom_taxes.create'), 403);

        $this->form->name = $search;
        $this->form->active = true;

        if (! $this->form->isSuperAdmin()) {
            $this->form->business_id = auth()->user()->businessIds()[0] ?? null;
        }
    }

    public function close(): void
    {
        $this->dispatch('searchable-create-closed', modelClass: CustomTax::class);
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('custom_taxes.create'), 403);

        $tax = CreateOrUpdateCustomTaxAction::run(null, $this->form->validated());

        $this->dispatch('swal', [
            'title' => 'Impuesto creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('searchable-created', id: $tax->id, modelClass: CustomTax::class);
    }

    public function render()
    {
        return view('livewire.ui.searchable-create.custom-tax-modal', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'businesses'     => $this->form->getBusinesses(),
        ]);
    }
}
