<?php

namespace App\Livewire\Admin\Events\Manage;

use App\Models\Event;
use App\Models\EventCategory;
use App\Support\EventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CategoryIndex extends Component
{
    public EventCategory $event_category;

    public string $date = '';

    public string $month = '';

    public function mount(EventCategory $eventCategory): void
    {
        EventsAccess::authorizeViewEvents();

        $this->event_category = $eventCategory;
    }

    public function updatedMonth(string $value): void
    {
        if ($value === '') {
            return;
        }

        if (! ctype_digit($value) || (int) $value < 1 || (int) $value > 12) {
            $this->month = '';
        }
    }

    public function render()
    {
        $events_query = Event::query()
            ->forAuthUser()
            ->where('event_category_id', $this->event_category->id);

        if ($this->date !== '') {
            $events_query->whereDate('date', $this->date);
        }

        $month = $this->resolvedMonth();

        if ($month !== null) {
            $events_query->whereMonth('date', $month);
        }

        return view('livewire.admin.events.manage.category-index', [
            'events_count' => $events_query->count(),
            'date_filter' => $this->date !== '' ? $this->date : null,
            'month_filter' => $month,
            'can_create' => EventsAccess::canCreateEvents(),
        ])->layoutData([
            'title' => 'Gestión de eventos — '.$this->event_category->name,
        ]);
    }

    private function resolvedMonth(): ?int
    {
        if ($this->month === '' || ! ctype_digit($this->month)) {
            return null;
        }

        $month = (int) $this->month;

        return ($month >= 1 && $month <= 12) ? $month : null;
    }
}
