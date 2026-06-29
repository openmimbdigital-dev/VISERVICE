<?php

namespace App\Livewire\Admin\Settings\Equipment;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Equipos')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.view'), 403);
    }

    public function render()
    {
        return view('livewire.admin.settings.equipment.index', [
            'sections' => collect(EquipmentSettingsConfig::sections())
                ->filter(fn (array $section) => auth()->user()->can($section['permission'] ?? 'settings.view'))
                ->all(),
        ]);
    }
}
