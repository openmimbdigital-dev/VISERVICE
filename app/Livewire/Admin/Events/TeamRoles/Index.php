<?php

namespace App\Livewire\Admin\Events\TeamRoles;

use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Gestión de eventos — Roles del equipo')]
class Index extends Component
{
    public function mount(): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('events.team_roles.view'), 403);
    }

    public function render()
    {
        return view('livewire.admin.events.team-roles.index');
    }
}
