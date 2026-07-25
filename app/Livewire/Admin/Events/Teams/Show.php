<?php

namespace App\Livewire\Admin\Events\Teams;

use App\Actions\Events\DeleteEventTeamAction;
use App\Models\EventTeam;
use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Gestión de eventos — Equipo de evento')]
class Show extends Component
{
    public EventTeam $event_team;

    public function mount(EventTeam $eventTeam): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('events.teams.view'), 403);

        $this->event_team = EventTeam::query()
            ->forAuthUser()
            ->with([
                'business:id,name',
                'roles:id,name,functions',
                'members.user:id,first_name,last_name',
                'members.role:id,name',
            ])
            ->findOrFail($eventTeam->id);
    }

    public function delete(): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('events.teams.delete'), 403);

        DeleteEventTeamAction::run($this->event_team->id);

        $this->dispatch('swal', [
            'title' => 'Equipo de evento eliminado correctamente.',
            'icon' => 'success',
        ]);

        $this->redirectRoute('admin.events.teams.index', navigate: true);
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.admin.events.teams.show', [
            'can_edit' => $user?->can('events.teams.edit'),
            'can_delete' => $this->event_team->canDelete($user),
        ]);
    }
}
