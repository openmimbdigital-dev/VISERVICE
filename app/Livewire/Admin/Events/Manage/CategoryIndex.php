<?php

namespace App\Livewire\Admin\Events\Manage;

use App\Models\Event;
use App\Models\EventCategory;
use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CategoryIndex extends Component
{
    public EventCategory $event_category;

    public function mount(EventCategory $eventCategory): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('events.events.view'), 403);

        $this->event_category = $eventCategory;
    }

    public function render()
    {
        $events_count = Event::query()
            ->forAuthUser()
            ->where('event_category_id', $this->event_category->id)
            ->count();

        return view('livewire.admin.events.manage.category-index', [
            'events_count' => $events_count,
        ])->layoutData([
            'title' => 'Gestión de eventos — '.$this->event_category->name,
        ]);
    }
}
