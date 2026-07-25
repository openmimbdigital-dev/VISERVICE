<?php

namespace App\Livewire\Admin\Events\Manage;

use App\Models\Event;
use App\Models\EventCategory;
use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Gestión de eventos — Administrar eventos')]
class Index extends Component
{
    public function mount(): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('events.events.view'), 403);
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
            'stats' => [
                'categories' => $categories->count(),
                'events' => (clone $events_query)->count(),
            ],
        ]);
    }
}
