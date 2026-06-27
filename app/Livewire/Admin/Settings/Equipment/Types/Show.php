<?php

namespace App\Livewire\Admin\Settings\Equipment\Types;

use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\EquipmentType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Tipo de equipo')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

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

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('settings.edit'), 403);

        $this->askDeleteConfirmation($this->equipment_type->id, '¿Estás seguro de querer eliminar este tipo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            abort_unless(auth()->user()->can('settings.edit'), 403);

            $equipment_type = EquipmentType::findOrFail($this->delete_id);

            if (! $equipment_type->canDelete()) {
                $message = $equipment_type->isGeneralReadonly()
                    ? 'No se puede eliminar: es un tipo general del sistema.'
                    : ($equipment_type->hasDependencies()
                        ? 'No se puede eliminar: tiene equipos asociados.'
                        : 'No tienes permiso para eliminar este tipo.');

                $this->alertDeleteWarning($message);

                return;
            }

            $equipment_type->delete();

            $this->alertDeleteSuccess('Tipo eliminado correctamente.');

            $this->redirect(route('admin.settings.equipment.types'), navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el tipo.');
        }
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
