<?php

namespace App\Livewire\Admin\Workshop\WorkOrders\AssociatedDocuments;

use App\Actions\Workshop\CreateOrUpdateAssociatedDocumentTypeAction;
use App\Livewire\Forms\Admin\Workshop\AssociatedDocumentTypeForm;
use App\Models\AssociatedDocumentType;
use App\Models\Business;
use App\Support\CurrentBusiness;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Documentos asociados OT')]
class Index extends Component
{
    public AssociatedDocumentTypeForm $form;

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

    #[On('open-associated-document-type-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.associated-documents.edit'), 403);

        $type = AssociatedDocumentType::query()
            ->forAuthUser()
            ->findOrFail($id);

        $this->form->setDocumentType($type);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('associated-document-type-deleted')]
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

        CreateOrUpdateAssociatedDocumentTypeAction::run(
            $this->form->document_type_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Documento actualizado' : 'Documento creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('associated-document-type-saved');
    }

    public function render()
    {
        $query = AssociatedDocumentType::query()->forAuthUser();

        return view('livewire.admin.workshop.work-orders.associated-documents.index', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'businesses'     => $this->form->isSuperAdmin()
                ? Business::query()->orderBy('name')->get(['id', 'name'])
                : collect(),
            'stats' => [
                'total'  => (clone $query)->count(),
                'active' => (clone $query)->where('active', true)->count(),
            ],
        ]);
    }
}
