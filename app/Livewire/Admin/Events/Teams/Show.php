<?php

namespace App\Livewire\Admin\Events\Teams;

use App\Actions\Events\DeleteEventTeamAction;
use App\Models\Event;
use App\Models\EventTeam;
use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Gestión de eventos — Equipo de evento')]
class Show extends Component
{
    public EventTeam $event_team;

    #[Url(as: 'from_event')]
    public ?int $from_event = null;

    public function mount(EventTeam $eventTeam): void
    {
        ChurchEventsAccess::authorize();

        $user = auth()->user();
        abort_unless(
            $user?->can('events.teams.view') || $user?->can('events.schedule.view'),
            403
        );

        $this->event_team = EventTeam::query()
            ->forAuthUser()
            ->with([
                'business:id,name',
                'roles:id,name,functions',
                'members.participant:id,first_name,last_name',
                'members.role:id,name,functions',
            ])
            ->findOrFail($eventTeam->id);

        if ($this->from_event !== null) {
            $event_exists = Event::query()
                ->forAuthUser()
                ->whereKey($this->from_event)
                ->exists();

            if (! $event_exists) {
                $this->from_event = null;
            }
        }
    }

    public function delete(): void
    {
        abort_if($this->from_event !== null, 403);

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

        $from_schedule = $this->from_event !== null;
        $has_permission_delete = (bool) $user?->can('events.teams.delete');
        $assigned_to_event = $this->event_team->hasDependencies();

        return view('livewire.admin.events.teams.show', [
            'can_edit' => ! $from_schedule && $user?->can('events.teams.edit'),
            'can_delete' => ! $from_schedule && $has_permission_delete,
            'delete_disabled' => $assigned_to_event,
            'delete_disabled_title' => 'No se puede eliminar: el equipo está asignado a uno o más eventos',
            'back_url' => $from_schedule
                ? route('admin.events.schedule.show', $this->from_event)
                : route('admin.events.teams.index'),
            'back_label' => $from_schedule ? 'Volver al evento' : 'Volver',
        ]);
    }
}
