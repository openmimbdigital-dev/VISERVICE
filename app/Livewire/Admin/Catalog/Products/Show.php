<?php

namespace App\Livewire\Admin\Catalog\Products;

use App\Actions\Catalog\DeleteProductAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Producto')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public Product $product;

    public function mount(Product $product): void
    {
        abort_unless(auth()->user()->can('catalog.products.view'), 403);

        abort_unless(
            Product::query()->forAuthUser()->whereKey($product->id)->exists(),
            404
        );

        $this->product = $product->load([
            'business',
            'product_type',
            'product_category',
            'unit',
            'brand',
        ]);
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('catalog.products.delete'), 403);
        abort_unless($this->product->canDelete(), 403);

        $this->askDeleteConfirmation($this->product->id, '¿Estás seguro de querer eliminar este producto?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteProductAction::run($this->delete_id);

            $this->alertDeleteSuccess('Producto eliminado correctamente.');

            $this->redirectRoute('admin.catalog.products.index', navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el producto.');
        }
    }

    public function render()
    {
        return view('livewire.admin.catalog.products.show', [
            'can_edit'   => auth()->user()->can('catalog.products.edit') && $this->product->isEditableBy(),
            'can_delete' => auth()->user()->can('catalog.products.delete') && $this->product->canDelete(),
        ]);
    }
}
