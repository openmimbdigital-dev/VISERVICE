<?php

namespace App\Livewire\Admin\Events\Teams;

use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Evento — Equipos de evento')]
class Index extends Component
{
    public function mount(): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('events.teams.view'), 403);
    }

    public function render()
    {
        return view('livewire.admin.events.teams.index');
    }
}
