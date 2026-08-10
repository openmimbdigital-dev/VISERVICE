<?php

namespace App\Livewire\Admin\Participants;

use App\Actions\Business\CreateOrUpdateParticipantAction;
use App\Enums\DocumentType;
use App\Livewire\Forms\Admin\ParticipantForm;
use App\Models\Participant;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Participante — Negocios')]
class Form extends Component
{
    public ParticipantForm $form;

    public function mount(?Participant $participant = null): void
    {
        if ($participant?->exists) {
            abort_unless(auth()->user()?->can('participants.edit'), 403);

            abort_unless(
                Participant::query()->forAuthUser()->whereKey($participant->id)->exists(),
                404
            );

            $this->form->setParticipant($participant);

            return;
        }

        abort_unless(auth()->user()?->can('participants.create'), 403);

        $this->form->status = true;

        if (! $this->form->isSuperAdmin()) {
            $this->form->business_id = auth()->user()?->business_id;
        }
    }

    public function updatedFormBusinessId(): void
    {
        $this->form->participant_role_id = null;
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can(
                $this->form->isEditing() ? 'participants.edit' : 'participants.create'
            ),
            403
        );

        CreateOrUpdateParticipantAction::run(
            $this->form->resolvedBusinessId(),
            $this->form->participant_id,
            $this->form->validated()
        );

        $this->dispatch('swal', [
            'title' => $this->form->isEditing()
                ? 'Participante actualizado correctamente.'
                : 'Participante creado correctamente.',
            'icon' => 'success',
        ]);

        $this->redirectRoute('admin.participants.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.participants.form', [
            'is_editing' => $this->form->isEditing(),
            'is_super_admin' => $this->form->isSuperAdmin(),
            'businesses' => $this->form->isSuperAdmin() ? $this->form->getBusinesses() : collect(),
            'roles' => $this->form->getRoles(),
            'cities' => $this->form->getCities(),
            'countries' => $this->form->getCountries(),
            'document_types' => DocumentType::options(),
        ])->layoutData([
            'title' => $this->form->isEditing()
                ? 'Editar participante — Negocios'
                : 'Nuevo participante — Negocios',
        ]);
    }
}
