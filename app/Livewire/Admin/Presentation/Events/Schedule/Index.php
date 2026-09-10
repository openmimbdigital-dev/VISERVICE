<?php

namespace App\Livewire\Admin\Presentation\Events\Schedule;

use App\Support\Presentation\EventDemoData;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Agenda')]
class Index extends Component
{
    public int $year;

    public int $month;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);

        $today = now();
        $this->year = (int) $today->year;
        $this->month = (int) $today->month;
    }

    public function previousMonth(): void
    {
        $cursor = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = (int) $cursor->year;
        $this->month = (int) $cursor->month;
    }

    public function nextMonth(): void
    {
        $cursor = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = (int) $cursor->year;
        $this->month = (int) $cursor->month;
    }

    public function goToday(): void
    {
        $today = now();
        $this->year = (int) $today->year;
        $this->month = (int) $today->month;
    }

    public function render()
    {
        $start = Carbon::create($this->year, $this->month, 1)->locale('es');
        $grid_start = $start->copy()->startOfWeek(Carbon::MONDAY);
        $grid_end = $start->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $by_date = EventDemoData::eventsByDate();
        $weeks = [];
        $cursor = $grid_start->copy();

        while ($cursor->lte($grid_end)) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $key = $cursor->toDateString();
                $week[] = [
                    'date' => $key,
                    'day' => (int) $cursor->day,
                    'in_month' => (int) $cursor->month === $this->month,
                    'is_today' => $cursor->isToday(),
                    'events' => $by_date[$key] ?? [],
                ];
                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        return view('livewire.admin.presentation.events.schedule.index', [
            'month_label' => $start->isoFormat('MMMM YYYY'),
            'weekday_labels' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            'weeks' => $weeks,
        ]);
    }
}
