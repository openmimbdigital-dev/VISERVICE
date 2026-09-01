<?php

namespace App\Livewire\Admin\Workshop\Equipment;

use App\Models\Equipment;
use App\Models\EquipmentType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class TypeIndex extends Component
{
    public EquipmentType $equipment_type;

    public function mount(EquipmentType $equipmentType): void
    {
        abort_unless(auth()->user()->can('workshop.equipment.view'), 403);

        abort_unless($equipmentType->isAccessibleToUser(), 404);

        if (! auth()->user()->hasRole('superAdmin') && ! $equipmentType->active) {
            abort(404);
        }

        $this->equipment_type = $equipmentType;
    }

    #[On('equipment-deleted')]
    public function onEquipmentDeleted(): void {}

    #[On('equipment-saved')]
    public function onEquipmentSaved(): void {}

    public function render()
    {
        $equipment_query = Equipment::query()
            ->forAuthUser()
            ->where('equipment_type_id', $this->equipment_type->id);

        return view('livewire.admin.workshop.equipment.type-index', [
            'stats' => [
                'total'  => (clone $equipment_query)->count(),
                'active' => (clone $equipment_query)->where('status', true)->count(),
            ],
        ])->layoutData([
            'title' => 'Equipos — ' . $this->equipment_type->name,
        ]);
    }
}
