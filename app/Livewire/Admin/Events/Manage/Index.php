<?php

namespace App\Livewire\Admin\Events\Manage;

use App\Models\Event;
use App\Models\EventCategory;
use App\Support\EventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Gestión de eventos — Administrar eventos')]
class Index extends Component
{
    public function mount(): void
    {
        EventsAccess::authorizeViewEvents();
    }

    public function render()
    {
        $categories = EventCategory::query()
            ->visibleToUser()
            ->withCount(['events' => fn ($query) => $query->forAuthUser()])
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'type', 'general']);

        $events_query = Event::query()->forAuthUser();

        return view('livewire.admin.events.manage.index', [
            'categories' => $categories,
            'can_create' => EventsAccess::canCreateEvents(),
            'can_view_schedule' => EventsAccess::canViewSchedule(),
            'stats' => [
                'categories' => $categories->count(),
                'events' => (clone $events_query)->count(),
            ],
        ]);
    }
}
