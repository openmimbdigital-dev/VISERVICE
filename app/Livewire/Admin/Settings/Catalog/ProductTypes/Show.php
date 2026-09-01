<?php

namespace App\Livewire\Admin\Settings\Catalog\ProductTypes;

use App\Actions\Settings\Catalog\DeleteProductTypeAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\ProductType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Tipo de producto')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public ProductType $product_type;

    public int $products_count = 0;

    public function mount(ProductType $productType): void
    {
        abort_unless(auth()->user()->can('settings.product_types.view'), 403);

        abort_unless(
            ProductType::query()->visibleToUser()->whereKey($productType->id)->exists(),
            404
        );

        $this->product_type    = $productType->load('business');
        $this->products_count  = $productType->products()->count();
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('settings.product_types.delete'), 403);

        $this->askDeleteConfirmation($this->product_type->id, '¿Estás seguro de querer eliminar este tipo de producto?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteProductTypeAction::run($this->delete_id);

            $this->alertDeleteSuccess('Tipo eliminado correctamente.');

            $this->redirectRoute('admin.settings.catalog-products.product-types.index', navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el tipo.');
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.catalog.product-types.show', [
            'can_edit'            => auth()->user()->can('settings.product_types.edit') && $this->product_type->isEditableBy(),
            'can_delete'          => auth()->user()->can('settings.product_types.delete') && $this->product_type->canDelete(),
            'is_general_readonly' => $this->product_type->isGeneralReadonly(),
        ]);
    }
}
