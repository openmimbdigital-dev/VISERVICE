<?php

namespace App\Livewire\Admin\Workshop\QuotationServiceTypes;

use App\Actions\Workshop\CreateOrUpdateQuotationServiceTypeAction;
use App\Livewire\Forms\Admin\Workshop\QuotationServiceTypeForm;
use App\Models\QuotationServiceType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tipos de servicio')]
class Index extends Component
{
    public QuotationServiceTypeForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('workshop.quotation_service_types.view'), 403);

        $edit_id = request()->integer('edit');

        if ($edit_id > 0) {
            $this->openEdit($edit_id);
        }
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('workshop.quotation_service_types.create'), 403);

        $this->form->reset();
        $this->form->active = true;
        $this->showModal    = true;
        $this->resetValidation();
    }

    #[On('open-quotation-service-type-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('workshop.quotation_service_types.edit'), 403);

        $service_type = QuotationServiceType::query()->visibleToUser()->findOrFail($id);
        abort_unless($service_type->isEditableBy(), 403);

        $this->form->setServiceType($service_type);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('quotation-service-type-deleted')]
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
            auth()->user()->can($this->form->isEditing() ? 'workshop.quotation_service_types.edit' : 'workshop.quotation_service_types.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        CreateOrUpdateQuotationServiceTypeAction::run(
            $this->form->service_type_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Tipo actualizado' : 'Tipo creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('quotation-service-type-saved');
    }

    public function render()
    {
        $query = QuotationServiceType::query()->visibleToUser();

        return view('livewire.admin.workshop.quotation-service-types.index', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'stats'          => [
                'total'   => (clone $query)->count(),
                'active'  => (clone $query)->where('active', true)->count(),
                'general' => (clone $query)->where('general', true)->count(),
            ],
        ]);
    }
}
