<?php

namespace App\Livewire\Admin\Settings\Catalog\ProductCategories;

use App\Actions\Settings\Catalog\CreateOrUpdateProductCategoryAction;
use App\Livewire\Admin\Settings\Catalog\CatalogProductsSettingsConfig;
use App\Livewire\Forms\Admin\Settings\Catalog\ProductCategoryForm;
use App\Models\ProductCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Categorías')]
class Index extends Component
{
    public ProductCategoryForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.product_categories.view'), 403);

        $edit_id = request()->integer('edit');

        if ($edit_id > 0) {
            $this->openEdit($edit_id);
        }
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->can('settings.product_categories.create'), 403);

        $this->form->reset();
        $this->form->active = true;
        $this->showModal    = true;
        $this->resetValidation();
    }

    #[On('open-product-category-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->can('settings.product_categories.edit'), 403);

        $product_category = ProductCategory::query()->visibleToUser()->findOrFail($id);
        abort_unless($product_category->isEditableBy(), 403);

        $this->form->setProductCategory($product_category);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('product-category-deleted')]
    public function onRecordDeleted(): void {}

    #[On('product-category-saved')]
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
            auth()->user()->can($this->form->isEditing() ? 'settings.product_categories.edit' : 'settings.product_categories.create'),
            403
        );

        if ($this->form->isEditing()) {
            abort_unless(
                ProductCategory::query()->visibleToUser()->whereKey($this->form->product_category_id)->exists(),
                404
            );
        }

        $was_editing = $this->form->isEditing();

        CreateOrUpdateProductCategoryAction::run(
            $this->form->product_category_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Categoría actualizada' : 'Categoría creada',
            'icon'  => 'success',
        ]);

        $this->dispatch('product-category-saved');
    }

    public function render()
    {
        return view('livewire.admin.settings.catalog.product-categories.index', [
            'config'         => CatalogProductsSettingsConfig::sectionOrFail('categories'),
            'is_super_admin' => $this->form->isSuperAdmin(),
        ]);
    }
}
