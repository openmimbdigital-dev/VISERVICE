<?php

namespace App\Livewire\Admin\Settings\Catalog\ItemTypes;

use App\Actions\Settings\Catalog\CreateOrUpdateItemTypeAction;
use App\Livewire\Admin\Settings\Catalog\CatalogProductsSettingsConfig;
use App\Livewire\Forms\Admin\Settings\Catalog\ItemTypeForm;
use App\Models\ItemType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Tipos de producto')]
class Index extends Component
{
    public ItemTypeForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.item_types.view'), 403);

        $edit_id = request()->integer('edit');

        if ($edit_id > 0) {
            $this->openEdit($edit_id);
        }
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->can('settings.item_types.create'), 403);

        $this->form->reset();
        $this->form->active = true;
        $this->showModal    = true;
        $this->resetValidation();
    }

    #[On('open-item-type-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->can('settings.item_types.edit'), 403);

        $item_type = ItemType::query()->visibleToUser()->findOrFail($id);
        abort_unless($item_type->isEditableBy(), 403);

        $this->form->setItemType($item_type);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('item-type-deleted')]
    public function onRecordDeleted(): void {}

    #[On('item-type-saved')]
    public function onRecordSaved(): void {}

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
            auth()->user()->can($this->form->isEditing() ? 'settings.item_types.edit' : 'settings.item_types.create'),
            403
        );

        if ($this->form->isEditing()) {
            abort_unless(
                ItemType::query()->visibleToUser()->whereKey($this->form->item_type_id)->exists(),
                404
            );
        }

        $was_editing = $this->form->isEditing();

        CreateOrUpdateItemTypeAction::run(
            $this->form->item_type_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Tipo actualizado' : 'Tipo creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('item-type-saved');
    }

    public function render()
    {
        return view('livewire.admin.settings.catalog.item-types.index', [
            'config'         => CatalogProductsSettingsConfig::sectionOrFail('types'),
            'is_super_admin' => $this->form->isSuperAdmin(),
        ]);
    }
}
