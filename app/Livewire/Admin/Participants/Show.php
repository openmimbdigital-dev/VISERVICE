<?php

namespace App\Livewire\Admin\Participants;

use App\Actions\Business\DeleteParticipantAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Participant;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Participante — Negocios')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public Participant $participant;

    public function mount(Participant $participant): void
    {
        abort_unless(auth()->user()?->can('participants.view'), 403);

        abort_unless(
            Participant::query()->forAuthUser()->whereKey($participant->id)->exists(),
            404
        );

        $this->participant = $participant->load([
            'business:id,name',
            'participant_role:id,name',
            'city:id,name',
            'country:id,name',
        ]);
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()?->can('participants.delete'), 403);

        if ($this->participant->hasDependencies()) {
            $this->dispatch('swal', [
                'title' => 'No se puede eliminar',
                'text' => 'El participante está siendo utilizado en otras referencias.',
                'icon' => 'warning',
            ]);

            return;
        }

        $this->askDeleteConfirmation($this->participant->id, '¿Eliminar este participante?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteParticipantAction::run($this->delete_id);

            $this->alertDeleteSuccess('Participante eliminado correctamente.');

            $this->redirectRoute('admin.participants.index', navigate: true);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->alertDeleteWarning($e->getMessage() ?: 'No se pudo eliminar el participante.');
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar el participante.');
        }
    }

    public function render()
    {
        $has_dependencies = $this->participant->hasDependencies();

        return view('livewire.admin.participants.show', [
            'can_edit' => auth()->user()->can('participants.edit'),
            'can_delete' => auth()->user()->can('participants.delete'),
            'has_dependencies' => $has_dependencies,
        ]);
    }
}
