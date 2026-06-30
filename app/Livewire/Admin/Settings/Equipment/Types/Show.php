<?php

namespace App\Livewire\Admin\Settings\Equipment\Types;

use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Attribute;
use App\Models\AttributeEquipmentType;
use App\Models\EquipmentType;
use Illuminate\Support\Collection;
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

    /** @var Collection<int, Attribute> */
    public Collection $linked_attributes;

    public function mount(EquipmentType $equipmentType): void
    {
        abort_unless(auth()->user()->can('settings.equipment_types.view'), 403);

        abort_unless($equipmentType->isAccessibleToUser(), 404);

        $this->equipment_type = $equipmentType->load(['businesses']);
        $this->equipment_count = $equipmentType->equipment()->count();

        $attribute_ids = AttributeEquipmentType::query()
            ->where('model_type', EquipmentType::class)
            ->where('model_id', $equipmentType->id)
            ->pluck('attribute_id')
            ->unique();

        $this->linked_attributes = $attribute_ids->isEmpty()
            ? collect()
            : Attribute::query()
                ->forAuthUser()
                ->whereIn('id', $attribute_ids)
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'required', 'general', 'nullable_creation']);
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('settings.equipment_types.delete'), 403);

        $this->askDeleteConfirmation($this->equipment_type->id, '¿Estás seguro de querer eliminar este tipo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            abort_unless(auth()->user()->can('settings.equipment_types.delete'), 403);

            $equipment_type = EquipmentType::query()
                ->when(
                    ! auth()->user()->hasRole('superAdmin'),
                    fn ($query) => $query->visibleToUser()
                )
                ->findOrFail($this->delete_id);

            if (! $equipment_type->canDelete()) {
                $message = $equipment_type->hasDependencies()
                    ? 'No se puede eliminar: tiene equipos asociados.'
                    : 'No tienes permiso para eliminar este tipo.';

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
            'can_edit'            => auth()->user()->can('settings.equipment_types.edit') && $this->equipment_type->isEditableBy(),
            'can_delete'          => auth()->user()->can('settings.equipment_types.delete') && $this->equipment_type->canDelete(),
            'can_view_attributes' => auth()->user()->can('settings.attributes.view'),
        ]);
    }
}
