<?php

namespace App\Livewire\Admin\Catalog\Items;

use App\Actions\Catalog\CreateOrUpdateItemAction;
use App\Livewire\Forms\Admin\Catalog\ItemForm;
use App\Models\Business;
use App\Models\Item;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Producto')]
class Form extends Component
{
    public ItemForm $form;

    public function mount(?Item $item = null): void
    {
        if ($item) {
            abort_unless(auth()->user()->can('catalog.items.edit'), 403);

            abort_unless(
                Item::query()->forAuthUser()->whereKey($item->id)->exists(),
                404
            );

            abort_unless($item->isEditableBy(), 403);

            $this->form->setItem($item);

            return;
        }

        abort_unless(auth()->user()->can('catalog.items.create'), 403);

        if (! auth()->user()->hasRole('superAdmin')) {
            $this->form->business_id = auth()->user()->business_id;
        }
    }

    public function save(): void
    {
        abort_unless(
            $this->form->isEditing()
                ? auth()->user()->can('catalog.items.edit')
                : auth()->user()->can('catalog.items.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        $item = CreateOrUpdateItemAction::run(
            $this->form->item_id,
            $this->form->validated()
        );

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Producto actualizado' : 'Producto creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('item-saved');

        $this->redirectRoute(
            $was_editing ? 'admin.catalog.items.show' : 'admin.catalog.items.index',
            $was_editing ? ['item' => $item] : [],
            navigate: true
        );
    }

    public function render()
    {
        $is_super_admin = auth()->user()->hasRole('superAdmin');

        return view('livewire.admin.catalog.items.form', [
            'is_editing'     => $this->form->isEditing(),
            'is_super_admin' => $is_super_admin,
            'businesses'     => $is_super_admin
                ? Business::where('status', true)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'item_types'     => $this->form->getItemTypes(),
            'item_categories'=> $this->form->getItemCategories(),
            'units'          => $this->form->getUnits(),
            'brands'         => $this->form->getBrands(),
        ]);
    }
}
