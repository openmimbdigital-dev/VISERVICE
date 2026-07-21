<?php

namespace App\Livewire\Admin\Settings\Events;

use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Eventos')]
class Index extends Component
{
    public function mount(): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('settings.view'), 403);
    }

    public function render()
    {
        return view('livewire.admin.settings.events.index', [
            'sections' => collect(EventsSettingsConfig::sections())
                ->filter(fn (array $section) => auth()->user()->can($section['permission']))
                ->all(),
        ]);
    }
}
