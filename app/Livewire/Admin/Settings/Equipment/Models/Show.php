<?php

namespace App\Livewire\Admin\Settings\Equipment\Models;

use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\EquipmentModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Modelo')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public EquipmentModel $equipment_model;

    public int $equipment_count = 0;

    public function mount(EquipmentModel $equipmentModel): void
    {
        abort_unless(auth()->user()->can('settings.model_equipment.view'), 403);

        $visible = EquipmentModel::query()
            ->visibleToUser()
            ->whereKey($equipmentModel->id)
            ->exists();

        abort_unless($visible, 404);

        $this->equipment_model = $equipmentModel->load(['business', 'brand']);
        $this->equipment_count = $equipmentModel->equipment()->count();
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('settings.model_equipment.delete'), 403);

        $this->askDeleteConfirmation($this->equipment_model->id, '¿Estás seguro de querer eliminar este modelo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            abort_unless(auth()->user()->can('settings.model_equipment.delete'), 403);

            $equipment_model = EquipmentModel::query()->visibleToUser()->findOrFail($this->delete_id);

            if (! $equipment_model->canDelete()) {
                $message = $equipment_model->isGeneralReadonly()
                    ? 'No se puede eliminar: es un modelo general del sistema.'
                    : ($equipment_model->hasDependencies()
                        ? 'No se puede eliminar: tiene equipos asociados.'
                        : 'No tienes permiso para eliminar este modelo.');

                $this->alertDeleteWarning($message);

                return;
            }

            $equipment_model->delete();

            $this->alertDeleteSuccess('Modelo eliminado correctamente.');

            $this->redirect(route('admin.settings.equipment.models'), navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el modelo.');
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.equipment.models.show', [
            'can_edit'            => auth()->user()->can('settings.model_equipment.edit') && $this->equipment_model->isEditableBy(),
            'can_delete'          => auth()->user()->can('settings.model_equipment.delete') && $this->equipment_model->canDelete(),
            'is_general_readonly' => $this->equipment_model->isGeneralReadonly(),
        ]);
    }
}
