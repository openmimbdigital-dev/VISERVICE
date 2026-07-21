<?php

namespace App\Livewire\Admin\Settings\Events\AttendeeTypes;

use App\Actions\Settings\Events\CreateOrUpdateAttendeeTypeAction;
use App\Livewire\Forms\Admin\Settings\Events\AttendeeTypeForm;
use App\Models\AttendeeType;
use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public AttendeeTypeForm $form;

    public function mount(?AttendeeType $attendeeType = null): void
    {
        ChurchEventsAccess::authorize();

        if ($attendeeType?->exists) {
            abort_unless(auth()->user()?->can('settings.attendee_types.edit'), 403);

            $attendee_type = AttendeeType::query()
                ->visibleToUser()
                ->findOrFail($attendeeType->id);

            abort_unless($attendee_type->isEditableBy(), 403);
            $this->form->setAttendeeType($attendee_type);

            return;
        }

        abort_unless(auth()->user()?->can('settings.attendee_types.create'), 403);
        $this->form->reset();
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can(
                $this->form->isEditing()
                    ? 'settings.attendee_types.edit'
                    : 'settings.attendee_types.create'
            ),
            403
        );

        CreateOrUpdateAttendeeTypeAction::run(
            $this->form->attendee_type_id,
            $this->form->validated()
        );

        $this->dispatch('swal', [
            'title' => $this->form->isEditing()
                ? 'Tipo de asistente actualizado correctamente.'
                : 'Tipo de asistente creado correctamente.',
            'icon' => 'success',
        ]);

        $this->redirectRoute('admin.settings.events.attendee-types.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.settings.events.attendee-types.form', [
            'is_super_admin' => $this->form->isSuperAdmin(),
        ])->layoutData([
            'title' => $this->form->isEditing()
                ? 'Configuración — Editar tipo de asistente'
                : 'Configuración — Nuevo tipo de asistente',
        ]);
    }
}
