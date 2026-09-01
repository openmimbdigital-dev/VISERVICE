<?php

namespace App\Livewire\Admin\Businesses\CustomTaxes;

use App\Actions\Business\CreateOrUpdateCustomTaxAction;
use App\Livewire\Forms\Admin\Businesses\CustomTaxForm;
use App\Models\CustomTax;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Impuestos personalizados')]
class Index extends Component
{
    public CustomTaxForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('custom_taxes.view'), 403);

        $edit_id = request()->integer('edit');

        if ($edit_id > 0) {
            $this->openEdit($edit_id);
        }
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('custom_taxes.create'), 403);

        $this->form->reset();
        $this->form->active = true;

        if (! $this->form->isSuperAdmin()) {
            $this->form->business_id = auth()->user()->businessIds()[0] ?? null;
        }

        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('open-custom-tax-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('custom_taxes.edit'), 403);

        $tax = CustomTax::query()->forAuthUser()->findOrFail($id);
        abort_unless($tax->isEditableBy(auth()->user(), 'custom_taxes.edit'), 403);

        $this->form->setCustomTax($tax);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('custom-tax-deleted')]
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
            auth()->user()?->can($this->form->isEditing() ? 'custom_taxes.edit' : 'custom_taxes.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        CreateOrUpdateCustomTaxAction::run(
            $this->form->custom_tax_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Impuesto actualizado' : 'Impuesto creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('custom-tax-saved');
    }

    public function render()
    {
        $query = CustomTax::query()->forAuthUser();

        return view('livewire.admin.businesses.custom-taxes.index', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'businesses'     => $this->form->getBusinesses(),
            'stats'          => [
                'total'  => (clone $query)->count(),
                'active' => (clone $query)->where('active', true)->count(),
            ],
        ]);
    }
}
