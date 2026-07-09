<?php

namespace App\Livewire\Admin\Settings\Catalog\ItemCategories;

use App\Actions\Settings\Catalog\CreateOrUpdateItemCategoryAction;
use App\Livewire\Admin\Settings\Catalog\CatalogProductsSettingsConfig;
use App\Livewire\Forms\Admin\Settings\Catalog\ItemCategoryForm;
use App\Models\ItemCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Categorías')]
class Index extends Component
{
    public ItemCategoryForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.item_categories.view'), 403);

        $edit_id = request()->integer('edit');

        if ($edit_id > 0) {
            $this->openEdit($edit_id);
        }
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->can('settings.item_categories.create'), 403);

        $this->form->reset();
        $this->form->active = true;
        $this->showModal    = true;
        $this->resetValidation();
    }

    #[On('open-item-category-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->can('settings.item_categories.edit'), 403);

        $item_category = ItemCategory::query()->visibleToUser()->findOrFail($id);
        abort_unless($item_category->isEditableBy(), 403);

        $this->form->setItemCategory($item_category);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('item-category-deleted')]
    public function onRecordDeleted(): void {}

    #[On('item-category-saved')]
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
            auth()->user()->can($this->form->isEditing() ? 'settings.item_categories.edit' : 'settings.item_categories.create'),
            403
        );

        if ($this->form->isEditing()) {
            abort_unless(
                ItemCategory::query()->visibleToUser()->whereKey($this->form->item_category_id)->exists(),
                404
            );
        }

        $was_editing = $this->form->isEditing();

        CreateOrUpdateItemCategoryAction::run(
            $this->form->item_category_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Categoría actualizada' : 'Categoría creada',
            'icon'  => 'success',
        ]);

        $this->dispatch('item-category-saved');
    }

    public function render()
    {
        return view('livewire.admin.settings.catalog.item-categories.index', [
            'config'         => CatalogProductsSettingsConfig::sectionOrFail('categories'),
            'is_super_admin' => $this->form->isSuperAdmin(),
        ]);
    }
}
