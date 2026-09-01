<?php

namespace App\Livewire\Admin\Settings\Catalog\ProductTypes;

use App\Actions\Settings\Catalog\CreateOrUpdateProductTypeAction;
use App\Livewire\Admin\Settings\Catalog\CatalogProductsSettingsConfig;
use App\Livewire\Forms\Admin\Settings\Catalog\ProductTypeForm;
use App\Models\ProductType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Tipos de producto')]
class Index extends Component
{
    public ProductTypeForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.product_types.view'), 403);

        $edit_id = request()->integer('edit');

        if ($edit_id > 0) {
            $this->openEdit($edit_id);
        }
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->can('settings.product_types.create'), 403);

        $this->form->reset();
        $this->form->active = true;
        $this->showModal    = true;
        $this->resetValidation();
    }

    #[On('open-product-type-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->can('settings.product_types.edit'), 403);

        $product_type = ProductType::query()->visibleToUser()->findOrFail($id);
        abort_unless($product_type->isEditableBy(), 403);

        $this->form->setProductType($product_type);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('product-type-deleted')]
    public function onRecordDeleted(): void {}

    #[On('product-type-saved')]
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
            auth()->user()->can($this->form->isEditing() ? 'settings.product_types.edit' : 'settings.product_types.create'),
            403
        );

        if ($this->form->isEditing()) {
            abort_unless(
                ProductType::query()->visibleToUser()->whereKey($this->form->product_type_id)->exists(),
                404
            );
        }

        $was_editing = $this->form->isEditing();

        CreateOrUpdateProductTypeAction::run(
            $this->form->product_type_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Tipo actualizado' : 'Tipo creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('product-type-saved');
    }

    public function render()
    {
        return view('livewire.admin.settings.catalog.product-types.index', [
            'config'         => CatalogProductsSettingsConfig::sectionOrFail('types'),
            'is_super_admin' => $this->form->isSuperAdmin(),
        ]);
    }
}
