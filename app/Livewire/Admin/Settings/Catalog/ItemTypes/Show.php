<?php

namespace App\Livewire\Admin\Settings\Catalog\ItemTypes;

use App\Actions\Settings\Catalog\DeleteItemTypeAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\ItemType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Tipo de producto')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public ItemType $item_type;

    public int $items_count = 0;

    public function mount(ItemType $itemType): void
    {
        abort_unless(auth()->user()->can('settings.item_types.view'), 403);

        abort_unless(
            ItemType::query()->visibleToUser()->whereKey($itemType->id)->exists(),
            404
        );

        $this->item_type   = $itemType->load('business');
        $this->items_count = $itemType->items()->count();
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('settings.item_types.delete'), 403);

        $this->askDeleteConfirmation($this->item_type->id, '¿Estás seguro de querer eliminar este tipo de producto?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteItemTypeAction::run($this->delete_id);

            $this->alertDeleteSuccess('Tipo eliminado correctamente.');

            $this->redirectRoute('admin.settings.catalog-products.item-types.index', navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el tipo.');
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.catalog.item-types.show', [
            'can_edit'            => auth()->user()->can('settings.item_types.edit') && $this->item_type->isEditableBy(),
            'can_delete'          => auth()->user()->can('settings.item_types.delete') && $this->item_type->canDelete(),
            'is_general_readonly' => $this->item_type->isGeneralReadonly(),
        ]);
    }
}
