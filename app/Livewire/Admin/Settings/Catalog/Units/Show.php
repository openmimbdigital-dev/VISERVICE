<?php

namespace App\Livewire\Admin\Settings\Catalog\Units;

use App\Actions\Settings\Catalog\DeleteUnitAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Unidad')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public Unit $unit;

    public int $items_count = 0;

    public function mount(Unit $unit): void
    {
        abort_unless(auth()->user()->can('settings.units.view'), 403);

        abort_unless(
            Unit::query()->visibleToUser()->whereKey($unit->id)->exists(),
            404
        );

        $this->unit        = $unit->load('business');
        $this->items_count = $unit->items()->count();
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('settings.units.delete'), 403);

        $this->askDeleteConfirmation($this->unit->id, '¿Estás seguro de querer eliminar esta unidad?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteUnitAction::run($this->delete_id);

            $this->alertDeleteSuccess('Unidad eliminada correctamente.');

            $this->redirectRoute('admin.settings.catalog-products.units.index', navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar la unidad.');
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.catalog.units.show', [
            'can_edit'            => auth()->user()->can('settings.units.edit') && $this->unit->isEditableBy(),
            'can_delete'          => auth()->user()->can('settings.units.delete') && $this->unit->canDelete(),
            'is_general_readonly' => $this->unit->isGeneralReadonly(),
        ]);
    }
}
