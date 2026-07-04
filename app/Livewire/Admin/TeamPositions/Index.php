<?php

namespace App\Livewire\Admin\TeamPositions;

use App\Actions\Business\CreateOrUpdateTeamPositionAction;
use App\Livewire\Forms\Admin\TeamPositionForm;
use App\Models\TeamPosition;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cargos del equipo')]
class Index extends Component
{
    public TeamPositionForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('team_positions.view'), 403);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('team_positions.create'), 403);

        $this->form->reset();
        $this->form->active = true;

        if (! $this->form->isSuperAdmin()) {
            $this->form->business_type_id = auth()->user()?->primaryBusiness()?->business_type_id;
        }

        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('open-team-position-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('team_positions.edit'), 403);

        $team_position = TeamPosition::query()->visibleToUser()->findOrFail($id);
        abort_unless($team_position->isEditableBy(), 403);

        $this->form->setTeamPosition($team_position);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('team-position-deleted')]
    public function onTeamPositionDeleted(): void {}

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->form->reset();
        $this->form->active = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can($this->form->isEditing() ? 'team_positions.edit' : 'team_positions.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        CreateOrUpdateTeamPositionAction::run(
            $this->form->team_position_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Cargo actualizado.' : 'Cargo creado.',
            'icon'  => 'success',
        ]);

        $this->dispatch('team-position-saved');
    }

    public function render()
    {
        $user  = auth()->user();
        $stats_query = TeamPosition::query()->visibleToUser($user);

        return view('livewire.admin.team-positions.index', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'business_types' => $this->form->getBusinessTypes(),
            'stats'          => [
                'total'  => (clone $stats_query)->count(),
                'active' => (clone $stats_query)->where('active', true)->count(),
                'general'=> (clone $stats_query)->where('general', true)->count(),
            ],
        ]);
    }
}
