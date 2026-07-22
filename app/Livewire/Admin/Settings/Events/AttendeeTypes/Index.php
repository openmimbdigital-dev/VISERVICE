<?php

namespace App\Livewire\Admin\Settings\Events\AttendeeTypes;

use App\Livewire\Admin\Settings\Events\EventsSettingsConfig;
use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Tipos de asistente')]
class Index extends Component
{
    public function mount(): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('settings.attendee_types.view'), 403);
    }

    public function render()
    {
        return view('livewire.admin.settings.events.attendee-types.index', [
            'config' => EventsSettingsConfig::sectionOrFail('attendee-types'),
        ]);
    }
}
