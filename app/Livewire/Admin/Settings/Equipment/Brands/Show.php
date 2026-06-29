<?php

namespace App\Livewire\Admin\Settings\Equipment\Brands;

use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Brand;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Marca')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public Brand $brand;

    public int $equipment_count = 0;

    public function mount(Brand $brand): void
    {
        abort_unless(auth()->user()->can('settings.view'), 403);

        $visible = Brand::query()
            ->visibleToUser()
            ->whereKey($brand->id)
            ->exists();

        abort_unless($visible, 404);

        $this->brand = $brand->load([
            'business',
            'equipmentTypes' => fn ($query) => $query->orderBy('name'),
        ]);
        $this->equipment_count = $brand->equipment()->count();
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('settings.edit'), 403);

        $this->askDeleteConfirmation($this->brand->id, '¿Estás seguro de querer eliminar esta marca?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            abort_unless(auth()->user()->can('settings.edit'), 403);

            $brand = Brand::findOrFail($this->delete_id);

            if (! $brand->canDelete()) {
                $message = $brand->isGeneralReadonly()
                    ? 'No se puede eliminar: es una marca general del sistema.'
                    : ($brand->hasDependencies()
                        ? 'No se puede eliminar: tiene equipos asociados.'
                        : 'No tienes permiso para eliminar esta marca.');

                $this->alertDeleteWarning($message);

                return;
            }

            $brand->delete();

            $this->alertDeleteSuccess('Marca eliminada correctamente.');

            $this->redirect(route('admin.settings.equipment.brands'), navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar la marca.');
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.equipment.brands.show', [
            'can_edit'            => auth()->user()->can('settings.edit') && $this->brand->isEditableBy(),
            'can_delete'          => auth()->user()->can('settings.edit') && $this->brand->canDelete(),
            'is_general_readonly' => $this->brand->isGeneralReadonly(),
        ]);
    }
}
