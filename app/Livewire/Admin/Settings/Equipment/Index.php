<?php

namespace App\Livewire\Admin\Settings\Equipment;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Equipos')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.admin.settings.equipment.index', [
            'sections' => EquipmentSettingsConfig::sections(),
        ]);
    }
}
