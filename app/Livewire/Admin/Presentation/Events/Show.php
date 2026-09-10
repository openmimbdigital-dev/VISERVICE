<?php

namespace App\Livewire\Admin\Presentation\Events;

use App\Support\Presentation\EventDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Evento')]
class Show extends Component
{
    public string $event_id = '';

    public function mount(string $event): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        abort_unless(EventDemoData::event($event) !== null, 404);

        $this->event_id = $event;
    }

    public function render()
    {
        return view('livewire.admin.presentation.events.show', [
            'event' => EventDemoData::event($this->event_id),
        ]);
    }
}
