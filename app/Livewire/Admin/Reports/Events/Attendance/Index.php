<?php

namespace App\Livewire\Admin\Reports\Events\Attendance;

use App\Models\Event;
use App\Models\EventCategory;
use App\Support\EventAttendanceReport;
use App\Support\EventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Reportes — Asistencia de eventos')]
class Index extends Component
{
    public function mount(): void
    {
        EventsAccess::authorizeViewAttendanceReport();
    }

    public function refreshGeneralChart(): void
    {
        EventsAccess::authorizeViewAttendanceReport();

        $chart = EventAttendanceReport::generalChartByAttendeeType();

        $this->dispatch(
            'attendance-chart-updated',
            labels: $chart['labels'],
            values: $chart['values'],
        );
    }

    public function render()
    {
        $categories = EventCategory::query()
            ->visibleToUser()
            ->withCount(['events' => fn ($query) => $query->forAuthUser()])
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'type', 'general']);

        $events_query = Event::query()->forAuthUser();
        $chart = EventAttendanceReport::generalChartByAttendeeType();

        return view('livewire.admin.reports.events.attendance.index', [
            'categories' => $categories,
            'chart_labels' => $chart['labels'],
            'chart_values' => $chart['values'],
            'stats' => [
                'categories' => $categories->count(),
                'events' => (clone $events_query)->count(),
                'attendance_total' => array_sum($chart['values']),
            ],
        ]);
    }
}
