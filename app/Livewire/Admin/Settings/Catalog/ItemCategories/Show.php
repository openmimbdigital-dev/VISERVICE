<?php

namespace App\Livewire\Admin\Settings\Catalog\ItemCategories;

use App\Actions\Settings\Catalog\DeleteItemCategoryAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\ItemCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Categoría')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public ItemCategory $item_category;

    public int $items_count = 0;

    public function mount(ItemCategory $itemCategory): void
    {
        abort_unless(auth()->user()->can('settings.item_categories.view'), 403);

        abort_unless(
            ItemCategory::query()->visibleToUser()->whereKey($itemCategory->id)->exists(),
            404
        );

        $this->item_category = $itemCategory->load('business');
        $this->items_count   = $itemCategory->items()->count();
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('settings.item_categories.delete'), 403);

        $this->askDeleteConfirmation($this->item_category->id, '¿Estás seguro de querer eliminar esta categoría?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteItemCategoryAction::run($this->delete_id);

            $this->alertDeleteSuccess('Categoría eliminada correctamente.');

            $this->redirectRoute('admin.settings.catalog-products.item-categories.index', navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar la categoría.');
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.catalog.item-categories.show', [
            'can_edit'            => auth()->user()->can('settings.item_categories.edit') && $this->item_category->isEditableBy(),
            'can_delete'          => auth()->user()->can('settings.item_categories.delete') && $this->item_category->canDelete(),
            'is_general_readonly' => $this->item_category->isGeneralReadonly(),
        ]);
    }
}
