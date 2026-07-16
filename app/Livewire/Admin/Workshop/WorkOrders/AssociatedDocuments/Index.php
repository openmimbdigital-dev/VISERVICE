<?php

namespace App\Livewire\Admin\Workshop\WorkOrders\AssociatedDocuments;

use App\Actions\Workshop\CreateOrUpdateAssociatedDocumentOtAction;
use App\Livewire\Forms\Admin\Workshop\AssociatedDocumentOtForm;
use App\Models\Business;
use App\Models\GeneralConfig;
use App\Support\CurrentBusiness;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Documentos asociados OT')]
class Index extends Component
{
    public AssociatedDocumentOtForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.associated-documents.view'), 403);

        $edit_id = request()->integer('edit');

        if ($edit_id > 0) {
            $this->openEdit($edit_id);
        }
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.associated-documents.create'), 403);

        $this->form->reset();

        if ($this->form->isSuperAdmin()) {
            $this->form->business_id = CurrentBusiness::id() ?? auth()->user()?->business_id;
        }

        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('open-associated-document-ot-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.associated-documents.edit'), 403);

        $config = GeneralConfig::query()
            ->forAuthUser()
            ->associatedDocumentsOt()
            ->findOrFail($id);

        $this->form->setConfig($config);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('associated-document-ot-deleted')]
    public function onRecordDeleted(): void {}

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->form->reset();
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()->can(
                $this->form->isEditing()
                    ? 'workshop.work-orders.associated-documents.edit'
                    : 'workshop.work-orders.associated-documents.create'
            ),
            403
        );

        $was_editing = $this->form->isEditing();

        CreateOrUpdateAssociatedDocumentOtAction::run(
            $this->form->config_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Documento actualizado' : 'Documento creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('associated-document-ot-saved');
    }

    public function render()
    {
        $query = GeneralConfig::query()->forAuthUser()->associatedDocumentsOt();

        return view('livewire.admin.workshop.work-orders.associated-documents.index', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'businesses'     => $this->form->isSuperAdmin()
                ? Business::query()->orderBy('name')->get(['id', 'name'])
                : collect(),
            'stats' => [
                'total' => (clone $query)->count(),
            ],
        ]);
    }
}
