<?php

namespace App\Livewire\Admin\TeamPositions;

use App\Actions\Business\DeleteTeamPositionAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\TeamPosition;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cargo del equipo')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public TeamPosition $teamPosition;

    public int $users_count = 0;

    public function mount(TeamPosition $teamPosition): void
    {
        abort_unless(auth()->user()->can('team_positions.view'), 403);

        abort_unless(
            TeamPosition::query()->visibleToUser()->whereKey($teamPosition->id)->exists(),
            404
        );

        $this->teamPosition = $teamPosition->load([
            'business',
            'business_type',
        ]);

        $this->users_count = $this->teamPosition->users()->count();
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('team_positions.delete'), 403);

        $this->askDeleteConfirmation($this->teamPosition->id, '¿Eliminar este cargo del equipo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteTeamPositionAction::run($this->delete_id);

            $this->alertDeleteSuccess('Cargo eliminado correctamente.');

            $this->redirectRoute('admin.team-positions.index', navigate: true);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->alertDeleteWarning($e->getMessage() ?: 'No se pudo eliminar el cargo.');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el cargo.');
        }
    }

    public function render()
    {
        $users = $this->teamPosition->users()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(25)
            ->get(['id', 'username', 'first_name', 'last_name', 'email', 'status']);

        return view('livewire.admin.team-positions.show', [
            'users'               => $users,
            'can_edit'            => auth()->user()->can('team_positions.edit') && $this->teamPosition->isEditableBy(),
            'can_delete'          => auth()->user()->can('team_positions.delete') && $this->teamPosition->canDelete(),
            'is_general_readonly' => $this->teamPosition->isGeneralReadonly(),
        ]);
    }
}
