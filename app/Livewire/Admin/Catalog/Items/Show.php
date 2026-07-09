<?php

namespace App\Livewire\Admin\Catalog\Items;

use App\Actions\Catalog\DeleteItemAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Item;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Producto')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public Item $item;

    public function mount(Item $item): void
    {
        abort_unless(auth()->user()->can('catalog.items.view'), 403);

        abort_unless(
            Item::query()->forAuthUser()->whereKey($item->id)->exists(),
            404
        );

        $this->item = $item->load([
            'business',
            'item_type',
            'item_category',
            'unit',
            'brand',
        ]);
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('catalog.items.delete'), 403);
        abort_unless($this->item->canDelete(), 403);

        $this->askDeleteConfirmation($this->item->id, '¿Estás seguro de querer eliminar este producto?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteItemAction::run($this->delete_id);

            $this->alertDeleteSuccess('Producto eliminado correctamente.');

            $this->redirectRoute('admin.catalog.items.index', navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el producto.');
        }
    }

    public function render()
    {
        return view('livewire.admin.catalog.items.show', [
            'can_edit'   => auth()->user()->can('catalog.items.edit') && $this->item->isEditableBy(),
            'can_delete' => auth()->user()->can('catalog.items.delete') && $this->item->canDelete(),
        ]);
    }
}
