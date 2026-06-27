<?php

namespace App\Livewire\Admin\Settings\Equipment\Brands;

use App\Models\Brand;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Marca')]
class Show extends Component
{
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

        $this->brand = $brand->load('business');
        $this->equipment_count = $brand->equipment()->count();
    }

    public function delete(): void
    {
        abort_unless(auth()->user()->can('settings.edit'), 403);

        if (! $this->brand->canDelete()) {
            $message = $this->brand->isGeneralReadonly()
                ? 'No se puede eliminar: es una marca general del sistema'
                : ($this->brand->hasDependencies()
                    ? 'No se puede eliminar: tiene equipos asociados'
                    : 'No tienes permiso para eliminar esta marca');

            $this->dispatch('swal', ['title' => $message, 'icon' => 'error']);

            return;
        }

        $this->brand->delete();

        $this->dispatch('swal', ['title' => 'Marca eliminada', 'icon' => 'warning']);

        $this->redirect(route('admin.settings.equipment.brands'), navigate: true);
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
