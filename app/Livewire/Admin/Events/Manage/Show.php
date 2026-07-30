<?php

namespace App\Livewire\Admin\Events\Manage;

use App\Actions\Events\DeleteEventAction;
use App\Models\Event;
use App\Models\EventCategory;
use App\Support\EventsAccess;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Gestión de eventos — Evento')]
class Show extends Component
{
    public EventCategory $event_category;

    public Event $event;

    public function mount(EventCategory $eventCategory, Event $event): void
    {
        EventsAccess::authorizeViewEvents();

        $this->event_category = $eventCategory;

        $this->event = Event::query()
            ->forAuthUser()
            ->where('event_category_id', $eventCategory->id)
            ->whereNull('parent_id')
            ->with([
                'business:id,name',
                'category:id,name,type',
                'teams:id,name',
                'children' => fn ($query) => $query->orderBy('date_start'),
            ])
            ->findOrFail($event->id);
    }

    public function delete(): void
    {
        try {
            DeleteEventAction::run($this->event->id);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first()
                ?? 'No se pudo eliminar el evento.';

            $this->dispatch('swal', [
                'title' => $message,
                'icon' => 'error',
            ]);

            return;
        }

        $this->dispatch('swal', [
            'title' => 'Evento eliminado correctamente.',
            'icon' => 'success',
        ]);

        $this->redirectRoute('admin.events.manage.category.index', $this->event_category, navigate: true);
    }

    public function render()
    {
        $attendance_locked = $this->event->hasStartedAttendance();
        $locked_title = 'Ya se inició la toma de asistencia';

        return view('livewire.admin.events.manage.show', [
            'can_edit' => EventsAccess::hasEditPermission($this->event),
            'can_delete' => EventsAccess::hasDeletePermission($this->event),
            'edit_disabled' => $attendance_locked,
            'delete_disabled' => $attendance_locked,
            'edit_disabled_title' => $locked_title,
            'delete_disabled_title' => $locked_title,
            'can_view_schedule' => EventsAccess::canViewSchedule(),
        ]);
    }
}
