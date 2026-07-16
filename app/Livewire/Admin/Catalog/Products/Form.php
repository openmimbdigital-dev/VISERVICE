<?php

namespace App\Livewire\Admin\Catalog\Products;

use App\Actions\Catalog\CreateOrUpdateProductAction;
use App\Livewire\Forms\Admin\Catalog\ProductForm;
use App\Models\Business;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Producto')]
class Form extends Component
{
    public ProductForm $form;

    public function mount(?Product $product = null): void
    {
        if ($product) {
            abort_unless(auth()->user()->can('catalog.products.edit'), 403);

            abort_unless(
                Product::query()->forAuthUser()->whereKey($product->id)->exists(),
                404
            );

            abort_unless($product->isEditableBy(), 403);

            $this->form->setProduct($product);

            return;
        }

        abort_unless(auth()->user()->can('catalog.products.create'), 403);

        if (! auth()->user()->hasRole('superAdmin')) {
            $this->form->business_id = auth()->user()->business_id;
        }
    }

    public function save(): void
    {
        abort_unless(
            $this->form->isEditing()
                ? auth()->user()->can('catalog.products.edit')
                : auth()->user()->can('catalog.products.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        $product = CreateOrUpdateProductAction::run(
            $this->form->product_id,
            $this->form->validated()
        );

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Producto actualizado' : 'Producto creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('product-saved');

        $this->redirectRoute(
            $was_editing ? 'admin.catalog.products.show' : 'admin.catalog.products.index',
            $was_editing ? ['product' => $product] : [],
            navigate: true
        );
    }

    public function render()
    {
        $is_super_admin = auth()->user()->hasRole('superAdmin');

        return view('livewire.admin.catalog.products.form', [
            'is_editing'         => $this->form->isEditing(),
            'is_super_admin'     => $is_super_admin,
            'businesses'         => $is_super_admin
                ? Business::where('status', true)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'product_types'      => $this->form->getProductTypes(),
            'product_categories' => $this->form->getProductCategories(),
            'units'              => $this->form->getUnits(),
            'brands'             => $this->form->getBrands(),
        ]);
    }
}
