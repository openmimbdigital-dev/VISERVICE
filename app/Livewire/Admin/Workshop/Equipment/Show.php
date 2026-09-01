<?php

namespace App\Livewire\Admin\Workshop\Equipment;

use App\Actions\Workshop\Equipment\DeleteEquipmentAction;
use App\Enums\AttributeType;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\AttributeEquipmentType;
use App\Models\Equipment;
use App\Models\EquipmentType;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public EquipmentType $equipment_type;

    public Equipment $equipment;

    /** @var Collection<int, AttributeEquipmentType> */
    public Collection $attribute_rows;

    public function mount(EquipmentType $equipmentType, Equipment $equipment): void
    {
        abort_unless(auth()->user()->can('workshop.equipment.view'), 403);

        abort_unless($equipmentType->isAccessibleToUser(), 404);

        abort_unless(
            (int) $equipment->equipment_type_id === (int) $equipmentType->id,
            404
        );

        abort_unless(
            Equipment::query()->forAuthUser()->whereKey($equipment->id)->exists(),
            404
        );

        $this->equipment_type = $equipmentType;
        $this->equipment      = $equipment->load(['client', 'business', 'equipmentBrand', 'equipmentModel']);
        $this->equipment->loadCount(['workOrders', 'quotations']);

        $this->attribute_rows = AttributeEquipmentType::query()
            ->where('model_type', Equipment::class)
            ->where('model_id', $equipment->id)
            ->whereNull('deleted_at')
            ->with('attribute')
            ->orderBy('id')
            ->get()
            ->filter(fn (AttributeEquipmentType $row) => $row->attribute !== null)
            ->values();
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('workshop.equipment.delete'), 403);
        abort_unless($this->equipment->canDelete(), 403);

        $this->askDeleteConfirmation($this->equipment->id, '¿Estás seguro de querer eliminar este equipo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            abort_unless(auth()->user()->can('workshop.equipment.delete'), 403);

            $equipment = Equipment::query()->forAuthUser()->findOrFail($this->delete_id);

            if (! $equipment->canDelete()) {
                $this->alertDeleteWarning(
                    $equipment->dependencyBlockReason() ?? 'No se puede eliminar el equipo.'
                );

                return;
            }

            DeleteEquipmentAction::run($equipment, $this->equipment_type);

            $this->alertDeleteSuccess('Equipo eliminado correctamente.');

            $this->redirectRoute('admin.workshop.equipment.type', $this->equipment_type, navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el equipo.');
        }
    }

    public function formatAttributeValue(AttributeEquipmentType $row): string
    {
        if ($row->value === null || $row->value === '') {
            return '—';
        }

        if ($row->attribute?->type === AttributeType::CHECKBOX) {
            $values = json_decode($row->value, true);

            return is_array($values) && $values !== []
                ? implode(', ', $values)
                : '—';
        }

        if ($row->attribute?->type === AttributeType::COLOR) {
            return $row->value;
        }

        return $row->value;
    }

    public function render()
    {
        return view('livewire.admin.workshop.equipment.show', [
            'can_edit'             => auth()->user()->can('workshop.equipment.edit'),
            'can_delete'           => $this->equipment->canDelete(),
            'delete_block_reason'  => $this->equipment->dependencyBlockReason(),
            'work_orders_count'    => $this->equipment->work_orders_count,
            'quotations_count'     => $this->equipment->quotations_count,
            'is_super_admin'       => auth()->user()->hasRole('superAdmin'),
        ])->layoutData([
            'title' => 'Equipo — ' . $this->equipment->plate,
        ]);
    }
}
