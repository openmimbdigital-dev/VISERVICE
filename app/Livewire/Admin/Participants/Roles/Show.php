<?php

namespace App\Livewire\Admin\Participants\Roles;

use App\Actions\Business\DeleteParticipantRoleAction;
use App\Models\ParticipantRole;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Support\DeleteConfirmationAlert;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
#[Title('Rol de participante')]
class Show extends Component
{
    use LivewireAlert;

    public ParticipantRole $participant_role;

    public function mount(ParticipantRole $participantRole): void
    {
        abort_unless(auth()->user()?->can('participants.roles.view'), 403);

        $this->participant_role = ParticipantRole::query()
            ->forAuthUser()
            ->with(['business:id,name'])
            ->withCount('participants')
            ->findOrFail($participantRole->id);
    }

    public function delete(): void
    {
        // Pregunta con el diálogo del proyecto; la acción va en deleteConfirmed().
        $this->confirm('¿Eliminar este rol de participante?', DeleteConfirmationAlert::options('participant-role-delete-confirmed'));
    }

    #[On('participant-role-delete-confirmed')]
    public function deleteConfirmed(): void
    {
        abort_unless(auth()->user()?->can('participants.roles.delete'), 403);
        abort_if($this->participant_role->hasDependencies(), 422);

        DeleteParticipantRoleAction::run($this->participant_role->id);

        $this->dispatch('swal', [
            'title' => 'Rol de participante eliminado correctamente.',
            'icon' => 'success',
        ]);

        $this->redirectRoute('admin.participants.roles.index', navigate: true);

    }

    public function render()
    {
        $user = auth()->user();
        $in_use = $this->participant_role->hasDependencies();

        return view('livewire.admin.participants.roles.show', [
            'can_edit' => $user?->can('participants.roles.edit'),
            'can_delete' => $user?->can('participants.roles.delete'),
            'edit_disabled' => $in_use,
            'delete_disabled' => $in_use,
            'disabled_title' => 'No se puede modificar: el rol está asignado a uno o más participantes',
        ]);
    }
}
