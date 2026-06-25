<?php

namespace App\Livewire\Admin\Settings\Equipment\Models;

use App\Models\EquipmentModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Modelo')]
class Show extends Component
{
    public EquipmentModel $equipment_model;

    public int $equipment_count = 0;

    public function mount(EquipmentModel $equipmentModel): void
    {
        abort_unless(auth()->user()->can('settings.view'), 403);

        $visible = EquipmentModel::query()
            ->visibleToUser()
            ->whereKey($equipmentModel->id)
            ->exists();

        abort_unless($visible, 404);

        $this->equipment_model = $equipmentModel->load(['business', 'brand']);
        $this->equipment_count = $equipmentModel->equipment()->count();
    }

    public function delete(): void
    {
        if (! $this->equipment_model->canDelete()) {
            $message = $this->equipment_model->hasDependencies()
                ? 'No se puede eliminar: tiene equipos asociados'
                : 'No tienes permiso para eliminar este modelo';

            $this->dispatch('swal', ['title' => $message, 'icon' => 'error']);

            return;
        }

        $this->equipment_model->delete();

        $this->dispatch('swal', ['title' => 'Modelo eliminado', 'icon' => 'warning']);

        $this->redirect(route('admin.settings.equipment.models'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.settings.equipment.models.show', [
            'can_edit'   => $this->equipment_model->isEditableBy(),
            'can_delete' => $this->equipment_model->canDelete(),
        ]);
    }
}
