<?php

namespace App\Livewire\Admin\Settings\General;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — General')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        abort_unless(auth()->user()->can('settings.statuses.view'), 403);
    }

    public function render()
    {
        return view('livewire.admin.settings.general.index', [
            'sections' => collect(GeneralSettingsConfig::sections())
                ->filter(fn (array $section) => auth()->user()->can($section['permission'] ?? 'settings.view'))
                ->all(),
        ]);
    }
}
