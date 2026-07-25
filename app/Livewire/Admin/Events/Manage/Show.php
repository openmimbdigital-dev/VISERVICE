<?php

namespace App\Livewire\Admin\Events\Manage;

use App\Actions\Events\DeleteEventAction;
use App\Models\Event;
use App\Models\EventCategory;
use App\Support\ChurchEventsAccess;
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
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('events.events.view'), 403);

        $this->event_category = $eventCategory;

        $this->event = Event::query()
            ->forAuthUser()
            ->where('event_category_id', $eventCategory->id)
            ->with(['business:id,name', 'category:id,name,type', 'teams:id,name'])
            ->findOrFail($event->id);
    }

    public function delete(): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('events.events.delete'), 403);

        DeleteEventAction::run($this->event->id);

        $this->dispatch('swal', [
            'title' => 'Evento eliminado correctamente.',
            'icon' => 'success',
        ]);

        $this->redirectRoute('admin.events.manage.category.index', $this->event_category, navigate: true);
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.admin.events.manage.show', [
            'can_edit' => $user?->can('events.events.edit')
                && ($user->hasRole('superAdmin') || $user->belongsToBusiness($this->event->business_id)),
            'can_delete' => $this->event->canDelete($user),
        ]);
    }
}
