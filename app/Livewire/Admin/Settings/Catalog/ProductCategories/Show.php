<?php

namespace App\Livewire\Admin\Settings\Catalog\ProductCategories;

use App\Actions\Settings\Catalog\DeleteProductCategoryAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\ProductCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Categoría')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public ProductCategory $product_category;

    public int $products_count = 0;

    public function mount(ProductCategory $productCategory): void
    {
        abort_unless(auth()->user()->can('settings.product_categories.view'), 403);

        abort_unless(
            ProductCategory::query()->visibleToUser()->whereKey($productCategory->id)->exists(),
            404
        );

        $this->product_category = $productCategory->load('business');
        $this->products_count   = $productCategory->products()->count();
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('settings.product_categories.delete'), 403);

        $this->askDeleteConfirmation($this->product_category->id, '¿Estás seguro de querer eliminar esta categoría?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteProductCategoryAction::run($this->delete_id);

            $this->alertDeleteSuccess('Categoría eliminada correctamente.');

            $this->redirectRoute('admin.settings.catalog-products.product-categories.index', navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar la categoría.');
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.catalog.product-categories.show', [
            'can_edit'            => auth()->user()->can('settings.product_categories.edit') && $this->product_category->isEditableBy(),
            'can_delete'          => auth()->user()->can('settings.product_categories.delete') && $this->product_category->canDelete(),
            'is_general_readonly' => $this->product_category->isGeneralReadonly(),
        ]);
    }
}
