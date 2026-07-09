<?php

namespace App\Livewire\Admin\Settings\Catalog\Brands;

use App\Actions\Settings\Catalog\DeleteCatalogBrandAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Brand;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Marca de producto')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public Brand $brand;

    public int $items_count = 0;

    public function mount(Brand $brand): void
    {
        abort_unless(auth()->user()->can('settings.brands.view'), 403);

        abort_unless(
            Brand::query()->visibleToUser()->forItemsCatalog()->whereKey($brand->id)->exists(),
            404
        );

        $this->brand       = $brand->load(['business', 'itemCategories']);
        $this->items_count = $brand->items()->count();
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('settings.brands.delete'), 403);

        $this->askDeleteConfirmation($this->brand->id, '¿Estás seguro de querer eliminar esta marca?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteCatalogBrandAction::run($this->delete_id);

            $this->alertDeleteSuccess('Marca eliminada correctamente.');

            $this->redirectRoute('admin.settings.catalog-products.brands.index', navigate: true);
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar la marca.');
        }
    }

    public function render()
    {
        $can_delete = auth()->user()->can('settings.brands.delete')
            && $this->brand->canDelete()
            && ! $this->brand->hasEquipmentUsage();

        return view('livewire.admin.settings.catalog.brands.show', [
            'can_edit'            => auth()->user()->can('settings.brands.edit') && $this->brand->isEditableBy(),
            'can_delete'          => $can_delete,
            'is_general_readonly' => $this->brand->isGeneralReadonly(),
            'has_equipment_usage' => $this->brand->hasEquipmentUsage(),
        ]);
    }
}
