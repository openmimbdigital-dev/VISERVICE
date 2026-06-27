<?php

namespace App\Livewire\Admin\Settings\Equipment;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SectionIndex extends Component
{
    public string $section;

    public function mount(string $section): void
    {
        abort_unless(auth()->user()->can('settings.view'), 403);

        $this->section = $section;
        EquipmentSettingsConfig::sectionOrFail($section);
    }

    public function getConfig(): array
    {
        return EquipmentSettingsConfig::sectionOrFail($this->section);
    }

    public function render()
    {
        $config = $this->getConfig();

        return view('livewire.admin.settings.equipment.section-index', [
            'config'  => $config,
            'section' => $this->section,
        ])->layoutData(['title' => "Configuración — {$config['title']}"]);
    }
}
