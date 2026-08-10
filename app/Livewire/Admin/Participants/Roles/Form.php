<?php

namespace App\Livewire\Admin\Participants\Roles;

use App\Actions\Business\CreateOrUpdateParticipantRoleAction;
use App\Livewire\Forms\Admin\ParticipantRoleForm;
use App\Models\ParticipantRole;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public ParticipantRoleForm $form;

    public function mount(?ParticipantRole $participantRole = null): void
    {
        if ($participantRole?->exists) {
            abort_unless(auth()->user()?->can('participants.roles.edit'), 403);

            $role = ParticipantRole::query()
                ->forAuthUser()
                ->findOrFail($participantRole->id);

            abort_unless($role->canEdit(), 403);

            $this->form->setParticipantRole($role);

            return;
        }

        abort_unless(auth()->user()?->can('participants.roles.create'), 403);
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
                    ? 'participants.roles.edit'
                    : 'participants.roles.create'
            ),
            403
        );

        CreateOrUpdateParticipantRoleAction::run(
            $this->form->resolvedBusinessId(),
            $this->form->participant_role_id,
            $this->form->validated()
        );

        $this->dispatch('swal', [
            'title' => $this->form->isEditing()
                ? 'Rol de participante actualizado correctamente.'
                : 'Rol de participante creado correctamente.',
            'icon' => 'success',
        ]);

        $this->redirectRoute('admin.participants.roles.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.participants.roles.form', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'businesses' => $this->form->isSuperAdmin() ? $this->form->getBusinesses() : collect(),
        ])->layoutData([
            'title' => $this->form->isEditing()
                ? 'Editar rol de participante'
                : 'Nuevo rol de participante',
        ]);
    }
}
