<?php

namespace App\Livewire\Admin\Events\Manage;

use App\Actions\Events\DeleteEventAction;
use App\Models\Event;
use App\Models\EventCategory;
use App\Support\EventsAccess;
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
            ->with(['business:id,name', 'category:id,name,type', 'teams:id,name'])
            ->findOrFail($event->id);
    }

    public function delete(): void
    {
        EventsAccess::authorizeDeleteEvent($this->event);

        DeleteEventAction::run($this->event->id);

        $this->dispatch('swal', [
            'title' => 'Evento eliminado correctamente.',
            'icon' => 'success',
        ]);

        $this->redirectRoute('admin.events.manage.category.index', $this->event_category, navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.events.manage.show', [
            'can_edit' => EventsAccess::canEditEvent($this->event),
            'can_delete' => EventsAccess::canDeleteEvent($this->event),
            'can_view_schedule' => EventsAccess::canViewSchedule(),
        ]);
    }
}
