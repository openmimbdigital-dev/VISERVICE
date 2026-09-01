<?php

namespace App\Livewire\Admin\Events\TeamRoles;

use App\Actions\Events\CreateOrUpdateEventTeamRoleAction;
use App\Livewire\Forms\Admin\Events\EventTeamRoleForm;
use App\Models\EventTeamRole;
use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public EventTeamRoleForm $form;

    public function mount(?EventTeamRole $eventTeamRole = null): void
    {
        ChurchEventsAccess::authorize();

        if ($eventTeamRole?->exists) {
            abort_unless(auth()->user()?->can('events.team_roles.edit'), 403);

            $role = EventTeamRole::query()
                ->forAuthUser()
                ->findOrFail($eventTeamRole->id);

            $this->form->setEventTeamRole($role);

            return;
        }

        abort_unless(auth()->user()?->can('events.team_roles.create'), 403);
        $this->form->reset();
        $this->form->active = true;

        if (! $this->form->isSuperAdmin()) {
            $this->form->business_id = auth()->user()?->business_id;
        }
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can(
                $this->form->isEditing()
                    ? 'events.team_roles.edit'
                    : 'events.team_roles.create'
            ),
            403
        );

        CreateOrUpdateEventTeamRoleAction::run(
            $this->form->resolvedBusinessId(),
            $this->form->event_team_role_id,
            $this->form->validated()
        );

        $this->dispatch('swal', [
            'title' => $this->form->isEditing()
                ? 'Rol del equipo actualizado correctamente.'
                : 'Rol del equipo creado correctamente.',
            'icon' => 'success',
        ]);

        $this->redirectRoute('admin.events.team-roles.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.events.team-roles.form', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'businesses' => $this->form->isSuperAdmin() ? $this->form->getBusinesses() : collect(),
        ])->layoutData([
            'title' => $this->form->isEditing()
                ? 'Gestión de eventos — Editar rol del equipo'
                : 'Gestión de eventos — Nuevo rol del equipo',
        ]);
    }
}
