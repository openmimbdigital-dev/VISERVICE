<?php

namespace App\Livewire\Admin\Settings\Events\AttendeeTypes;

use App\Models\AttendeeType;
use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Tipo de asistente')]
class Show extends Component
{
    public AttendeeType $attendee_type;

    public function mount(AttendeeType $attendeeType): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('settings.attendee_types.view'), 403);

        $this->attendee_type = AttendeeType::query()
            ->visibleToUser()
            ->with('business:id,name')
            ->findOrFail($attendeeType->id);
    }

    public function render()
    {
        return view('livewire.admin.settings.events.attendee-types.show', [
            'can_edit' => auth()->user()->can('settings.attendee_types.edit')
                && $this->attendee_type->isEditableBy(),
        ]);
    }
}
