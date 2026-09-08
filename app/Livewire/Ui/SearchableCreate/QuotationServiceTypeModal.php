<?php

namespace App\Livewire\Ui\SearchableCreate;

use App\Actions\Workshop\CreateOrUpdateQuotationServiceTypeAction;
use App\Livewire\Forms\Admin\Workshop\QuotationServiceTypeForm;
use App\Models\QuotationServiceType;
use Livewire\Component;

class QuotationServiceTypeModal extends Component
{
    public QuotationServiceTypeForm $form;

    public function mount(string $search = ''): void
    {
        abort_unless(auth()->user()?->can('workshop.quotation_service_types.create'), 403);

        $this->form->name = $search;
        $this->form->active = true;
    }

    public function close(): void
    {
        $this->dispatch('searchable-create-closed', modelClass: QuotationServiceType::class);
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('workshop.quotation_service_types.create'), 403);

        $service_type = CreateOrUpdateQuotationServiceTypeAction::run(null, $this->form->validated());

        $this->dispatch('swal', [
            'title' => 'Tipo de servicio creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('searchable-created', id: $service_type->id, modelClass: QuotationServiceType::class);
    }

    public function render()
    {
        return view('livewire.ui.searchable-create.quotation-service-type-modal', [
            'is_super_admin' => $this->form->isSuperAdmin(),
        ]);
    }
}
