<?php

namespace App\Livewire\Admin\Settings\Equipment\Attributes;

use App\Livewire\Admin\Settings\Equipment\EquipmentSettingsConfig;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Atributos')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.attributes.view'), 403);
    }

    public function render()
    {
        return view('livewire.admin.settings.equipment.attributes.index', [
            'config' => EquipmentSettingsConfig::sectionOrFail('attributes'),
        ]);
    }
}
