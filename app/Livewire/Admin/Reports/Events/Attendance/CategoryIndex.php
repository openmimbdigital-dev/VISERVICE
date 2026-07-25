<?php

namespace App\Livewire\Admin\Reports\Events\Attendance;

use App\Exports\EventAttendanceExport;
use App\Models\Event;
use App\Models\EventCategory;
use App\Support\EventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('layouts.app')]
class CategoryIndex extends Component
{
    use WithPagination;

    public EventCategory $event_category;

    public string $name = '';

    public string $date = '';

    public string $month = '';

    public function mount(EventCategory $eventCategory): void
    {
        EventsAccess::authorizeViewAttendanceReport();

        $this->event_category = $eventCategory;
    }

    public function updatedName(): void
    {
        $this->resetPage();
    }

    public function updatedDate(): void
    {
        $this->resetPage();
    }

    public function updatedMonth(string $value): void
    {
        $this->resetPage();

        if ($value === '') {
            return;
        }

        if (! ctype_digit($value) || (int) $value < 1 || (int) $value > 12) {
            $this->month = '';
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['name', 'date', 'month']);
        $this->resetPage();
    }

    public function exportAttendance(int $event_id): BinaryFileResponse
    {
        EventsAccess::authorizeExportAttendanceReport();

        $event = Event::query()
            ->forAuthUser()
            ->where('event_category_id', $this->event_category->id)
            ->with([
                'business.organization_type:id,label',
                'category:id,name',
                'attendee_types' => fn ($query) => $query->orderBy('name'),
            ])
            ->findOrFail($event_id);

        $filename = 'asistencia-evento-'.$event->id.'-'.$event->date?->format('Y-m-d').'.xlsx';

        return Excel::download(
            new EventAttendanceExport($event, auth()->user()),
            $filename
        );
    }

    public function render()
    {
        $events_query = Event::query()
            ->forAuthUser()
            ->where('event_category_id', $this->event_category->id)
            ->orderByDesc('date')
            ->orderByDesc('id');

        $name = trim($this->name);

        if ($name !== '') {
            $events_query->where('events.name', 'like', '%'.$name.'%');
        }

        if ($this->date !== '') {
            $events_query->whereDate('events.date', $this->date);
        }

        $month = $this->resolvedMonth();

        if ($month !== null) {
            $events_query->whereMonth('events.date', $month);
        }

        $events = $events_query->paginate(15);
        $has_filters = $name !== '' || $this->date !== '' || $month !== null;

        return view('livewire.admin.reports.events.attendance.category-index', [
            'events' => $events,
            'events_count' => $events->total(),
            'has_filters' => $has_filters,
            'can_export' => EventsAccess::canExportAttendanceReport(),
        ])->layoutData([
            'title' => 'Reportes — '.$this->event_category->name,
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
