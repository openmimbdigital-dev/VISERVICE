<?php

namespace App\Livewire\Admin\Presentation\Events;

use App\Support\Presentation\EventDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Eventos')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
    }

    public function render()
    {
        return view('livewire.admin.presentation.events.index', [
            'events' => EventDemoData::events(),
        ]);
    }
}
