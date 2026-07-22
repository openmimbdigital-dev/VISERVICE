<?php

namespace App\Livewire\Admin\Events\TeamRoles;

use App\Actions\Events\DeleteEventTeamRoleAction;
use App\Models\EventTeamRole;
use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Evento — Rol del equipo')]
class Show extends Component
{
    public EventTeamRole $event_team_role;

    public function mount(EventTeamRole $eventTeamRole): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('events.team_roles.view'), 403);

        $this->event_team_role = EventTeamRole::query()
            ->forAuthUser()
            ->with(['business:id,name', 'teams:id,name'])
            ->withCount('members')
            ->findOrFail($eventTeamRole->id);
    }

    public function delete(): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('events.team_roles.delete'), 403);

        DeleteEventTeamRoleAction::run($this->event_team_role->id);

        $this->dispatch('swal', [
            'title' => 'Rol del equipo eliminado correctamente.',
            'icon' => 'success',
        ]);

        $this->redirectRoute('admin.events.team-roles.index', navigate: true);
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.admin.events.team-roles.show', [
            'can_edit' => $user?->can('events.team_roles.edit'),
            'can_delete' => $this->event_team_role->canDelete($user),
        ]);
    }
}
