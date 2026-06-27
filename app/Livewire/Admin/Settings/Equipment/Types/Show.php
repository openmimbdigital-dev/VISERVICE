<?php

namespace App\Livewire\Admin\Settings\Equipment\Types;

use App\Models\EquipmentType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Tipo de equipo')]
class Show extends Component
{
    public EquipmentType $equipment_type;

    public int $equipment_count = 0;

    public function mount(EquipmentType $equipmentType): void
    {
        abort_unless(auth()->user()->can('settings.view'), 403);

        $visible = EquipmentType::query()
            ->visibleToUser()
            ->whereKey($equipmentType->id)
            ->exists();

        abort_unless($visible, 404);

        $this->equipment_type = $equipmentType->load('business');
        $this->equipment_count = $equipmentType->equipment()->count();
    }

    public function delete(): void
    {
        abort_unless(auth()->user()->can('settings.edit'), 403);

        if (! $this->equipment_type->canDelete()) {
            $message = $this->equipment_type->isGeneralReadonly()
                ? 'No se puede eliminar: es un tipo general del sistema'
                : ($this->equipment_type->hasDependencies()
                    ? 'No se puede eliminar: tiene equipos asociados'
                    : 'No tienes permiso para eliminar este tipo');

            $this->dispatch('swal', ['title' => $message, 'icon' => 'error']);

            return;
        }

        $this->equipment_type->delete();

        $this->dispatch('swal', ['title' => 'Tipo eliminado', 'icon' => 'warning']);

        $this->redirect(route('admin.settings.equipment.types'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.settings.equipment.types.show', [
            'can_edit'            => auth()->user()->can('settings.edit') && $this->equipment_type->isEditableBy(),
            'can_delete'          => auth()->user()->can('settings.edit') && $this->equipment_type->canDelete(),
            'is_general_readonly' => $this->equipment_type->isGeneralReadonly(),
        ]);
    }
}
