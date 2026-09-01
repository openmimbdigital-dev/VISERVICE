<?php

namespace App\Livewire\Admin\Events\Teams;

use App\Actions\Events\CreateOrUpdateEventTeamAction;
use App\Livewire\Forms\Admin\Events\EventTeamForm;
use App\Models\EventTeam;
use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public EventTeamForm $form;

    public function mount(?EventTeam $eventTeam = null): void
    {
        ChurchEventsAccess::authorize();

        if ($eventTeam?->exists) {
            abort_unless(auth()->user()?->can('events.teams.edit'), 403);

            $event_team = EventTeam::query()
                ->forAuthUser()
                ->findOrFail($eventTeam->id);

            $this->form->setEventTeam($event_team);

            return;
        }

        abort_unless(auth()->user()?->can('events.teams.create'), 403);
        $this->form->reset();
        $this->form->active = true;

        if (! $this->form->isSuperAdmin()) {
            $this->form->business_id = auth()->user()?->business_id;
        }
    }

    public function updatedFormBusinessId(): void
    {
        $this->form->role_ids = [];
        $this->form->members = [];
    }

    public function updatedFormRoleIds(): void
    {
        $role_ids = array_map('intval', $this->form->role_ids);

        $this->form->members = collect($this->form->members)
            ->filter(fn (array $member) => in_array((int) ($member['event_team_role_id'] ?? 0), $role_ids, true))
            ->values()
            ->all();
    }

    public function addMember(): void
    {
        $this->form->addMember();
    }

    public function removeMember(int $index): void
    {
        $this->form->removeMember($index);
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can(
                $this->form->isEditing()
                    ? 'events.teams.edit'
                    : 'events.teams.create'
            ),
            403
        );

        CreateOrUpdateEventTeamAction::run(
            $this->form->resolvedBusinessId(),
            $this->form->event_team_id,
            $this->form->validated()
        );

        $this->dispatch('swal', [
            'title' => $this->form->isEditing()
                ? 'Equipo de evento actualizado correctamente.'
                : 'Equipo de evento creado correctamente.',
            'icon' => 'success',
        ]);

        $this->redirectRoute('admin.events.teams.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.events.teams.form', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'businesses' => $this->form->isSuperAdmin() ? $this->form->getBusinesses() : collect(),
            'roles' => $this->form->getRoles(),
            'participants' => $this->form->getParticipants(),
        ])->layoutData([
            'title' => $this->form->isEditing()
                ? 'Gestión de eventos — Editar equipo'
                : 'Gestión de eventos — Nuevo equipo',
        ]);
    }
}
