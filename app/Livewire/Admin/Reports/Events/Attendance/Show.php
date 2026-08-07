<?php

namespace App\Livewire\Admin\Reports\Events\Attendance;

use App\Models\Event;
use App\Models\EventCategory;
use App\Support\EventAttendanceReport;
use App\Support\EventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public EventCategory $event_category;

    public Event $event;

    public function mount(EventCategory $eventCategory, Event $event): void
    {
        EventsAccess::authorizeViewAttendanceReport();

        $this->event_category = $eventCategory;

        $this->event = Event::query()
            ->forAuthUser()
            ->where('event_category_id', $eventCategory->id)
            ->where('multi_day', false)
            ->with([
                'business:id,name',
                'category:id,name,type',
                'teams:id,name',
                'parent:id,name,date_start,date_end',
                'attendee_types' => fn ($query) => $query->orderBy('name'),
            ])
            ->findOrFail($event->id);
    }

    public function refreshAttendanceChart(): void
    {
        EventsAccess::authorizeViewAttendanceReport();

        $this->reloadEvent();

        $chart = EventAttendanceReport::chartForEvent($this->event);

        $this->dispatch(
            'attendance-chart-updated',
            labels: $chart['labels'],
            values: $chart['values'],
        );
    }

    public function render()
    {
        $chart = EventAttendanceReport::chartForEvent($this->event);

        return view('livewire.admin.reports.events.attendance.show', [
            'attendance_rows' => $chart['rows'],
            'attendance_chart_labels' => $chart['labels'],
            'attendance_chart_values' => $chart['values'],
            'attendance_total' => array_sum($chart['values']),
        ])->layoutData([
            'title' => 'Reportes — '.$this->event->name,
        ]);
    }

    private function reloadEvent(): void
    {
        $this->event = Event::query()
            ->forAuthUser()
            ->where('event_category_id', $this->event_category->id)
            ->where('multi_day', false)
            ->with([
                'business:id,name',
                'category:id,name,type',
                'teams:id,name',
                'parent:id,name,date_start,date_end',
                'attendee_types' => fn ($query) => $query->orderBy('name'),
            ])
            ->findOrFail($this->event->id);
    }
}
