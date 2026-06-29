<?php

namespace App\Livewire\Admin\Workshop\Equipment;

use App\Models\Equipment;
use App\Models\EquipmentType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Equipos')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->can('workshop.equipment.view'), 403);
    }

    public function render()
    {
        $user = auth()->user();

        $types_query = EquipmentType::query()
            ->visibleToUser()
            ->withCount(['equipment' => fn ($query) => $query->forAuthUser()])
            ->orderBy('name');

        if (! $user->hasRole('superAdmin')) {
            $types_query->active();
        }

        $equipment_types = $types_query->get(['id', 'name', 'label', 'active', 'general']);

        $equipment_query = Equipment::query()->forAuthUser();

        return view('livewire.admin.workshop.equipment.index', [
            'equipment_types' => $equipment_types,
            'is_super_admin'  => $user->hasRole('superAdmin'),
            'stats'           => [
                'total'  => (clone $equipment_query)->count(),
                'active' => (clone $equipment_query)->where('status', true)->count(),
            ],
        ]);
    }
}
