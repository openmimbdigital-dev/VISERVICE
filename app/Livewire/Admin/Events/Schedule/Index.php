<?php

namespace App\Livewire\Admin\Events\Schedule;

use App\Support\EventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Gestión de eventos — Agenda')]
class Index extends Component
{
    public function mount(): void
    {
        EventsAccess::authorizeViewSchedule();
    }

    public function render()
    {
        return view('livewire.admin.events.schedule.index', [
            'events_feed_url' => route('admin.events.schedule.feed'),
            'can_manage_events' => EventsAccess::canViewEvents(),
        ]);
    }
}
