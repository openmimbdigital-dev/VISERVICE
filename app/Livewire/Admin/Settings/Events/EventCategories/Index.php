<?php

namespace App\Livewire\Admin\Settings\Events\EventCategories;

use App\Livewire\Admin\Settings\Events\EventsSettingsConfig;
use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Categorías de eventos')]
class Index extends Component
{
    public function mount(): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('settings.event_categories.view'), 403);
    }

    public function render()
    {
        return view('livewire.admin.settings.events.event-categories.index', [
            'config' => EventsSettingsConfig::sectionOrFail('event-categories'),
        ]);
    }
}
