<?php

namespace App\Livewire\Admin\Events\Schedule;

use App\Actions\Events\CloseEventAttendanceAction;
use App\Actions\Events\ReopenEventAttendanceAction;
use App\Actions\Events\StartEventAttendanceAction;
use App\Actions\Events\UpdateEventAttendanceCountAction;
use App\Models\AttendeeType;
use App\Models\Event;
use App\Support\ActionConfirmationAlert;
use App\Support\EventsAccess;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Gestión de eventos — Evento en agenda')]
class Show extends Component
{
    use LivewireAlert;

    public Event $event;

    /** @var list<int|string> */
    public array $selected_attendee_type_ids = [];

    public function mount(Event $event): void
    {
        EventsAccess::authorizeViewSchedule();

        $this->event = Event::query()
            ->forAuthUser()
            ->with([
                'business:id,name',
                'category:id,name,type',
                'teams:id,name',
                'attendee_types' => fn ($query) => $query->orderBy('name'),
            ])
            ->findOrFail($event->id);
    }

    public function startAttendance(): void
    {
        abort_unless(EventsAccess::canStartAttendance($this->event), 403);

        $this->validate([
            'selected_attendee_type_ids' => ['required', 'array', 'min:1'],
            'selected_attendee_type_ids.*' => ['integer'],
        ], [
            'selected_attendee_type_ids.required' => 'Selecciona al menos un tipo de asistencia.',
            'selected_attendee_type_ids.min' => 'Selecciona al menos un tipo de asistencia.',
        ]);

        try {
            StartEventAttendanceAction::run(
                $this->event->id,
                array_map('intval', $this->selected_attendee_type_ids)
            );
            $this->reloadEvent();
        } catch (ValidationException $exception) {
            throw $exception;
        }

        $this->dispatch('swal', [
            'title' => 'Toma de asistencia iniciada.',
            'icon' => 'success',
        ]);
    }

    public function incrementAttendance(int $attendee_type_id): void
    {
        abort_unless(! $this->event->attendance_closed, 403);

        UpdateEventAttendanceCountAction::run(
            $this->event->id,
            $attendee_type_id,
            'increment'
        );

        $this->reloadEvent();
    }

    public function decrementAttendance(int $attendee_type_id): void
    {
        abort_unless(! $this->event->attendance_closed, 403);

        try {
            UpdateEventAttendanceCountAction::run(
                $this->event->id,
                $attendee_type_id,
                'decrement'
            );
            $this->reloadEvent();
        } catch (ValidationException) {
            // Ya en cero: no hacer nada visible agresivo.
        }
    }

    public function confirmCloseAttendance(): void
    {
        abort_unless(EventsAccess::canCloseAttendance($this->event), 403);

        $this->confirm(
            '¿Cerrar la toma de asistencia?',
            ActionConfirmationAlert::options(
                title: 'Cerrar toma de asistencia',
                text: 'No podrás seguir incrementando ni decrementando los contadores.',
                confirm_button_text: 'Cerrar asistencia',
                on_confirmed: 'attendance-close-confirmed',
            )
        );
    }

    #[On('attendance-close-confirmed')]
    public function closeAttendance(): void
    {
        abort_unless(EventsAccess::canCloseAttendance($this->event), 403);

        CloseEventAttendanceAction::run($this->event->id);
        $this->reloadEvent();

        $this->dispatch('swal', [
            'title' => 'Toma de asistencia cerrada.',
            'icon' => 'success',
        ]);
    }

    public function confirmReopenAttendance(): void
    {
        abort_unless(EventsAccess::canCloseAttendance($this->event), 403);

        $this->confirm(
            '¿Retomar la toma de asistencia?',
            ActionConfirmationAlert::options(
                title: 'Desbloquear toma de asistencia',
                text: 'Se habilitarán de nuevo los botones para incrementar y decrementar.',
                confirm_button_text: 'Desbloquear',
                on_confirmed: 'attendance-reopen-confirmed',
            )
        );
    }

    #[On('attendance-reopen-confirmed')]
    public function reopenAttendance(): void
    {
        abort_unless(EventsAccess::canCloseAttendance($this->event), 403);

        ReopenEventAttendanceAction::run($this->event->id);
        $this->reloadEvent();

        $this->dispatch('swal', [
            'title' => 'Toma de asistencia desbloqueada.',
            'icon' => 'success',
        ]);
    }

    public function refreshAttendanceChart(): void
    {
        $this->reloadEvent();

        $rows = $this->event->attendee_types->sortBy('name')->values();

        $this->dispatch(
            'attendance-chart-updated',
            labels: $rows->pluck('name')->map(fn ($name) => (string) $name)->all(),
            values: $rows->map(fn ($type) => (int) $type->pivot->attendance)->all(),
        );
    }

    public function render()
    {
        $has_category = $this->event->category !== null;
        $can_start_attendance = EventsAccess::canStartAttendance($this->event);
        $can_close_attendance = EventsAccess::canCloseAttendance($this->event);
        $attendance_capture = $this->event->attendanceCaptureState();
        $attendance_rows = $this->event->attendee_types
            ->sortBy('name')
            ->values();
        $attendance_started = $attendance_rows->isNotEmpty();
        $attendance_closed = (bool) $this->event->attendance_closed;

        return view('livewire.admin.events.schedule.show', [
            'can_manage' => $has_category && EventsAccess::canManageEvent($this->event),
            'can_edit' => $has_category && EventsAccess::canEditEvent($this->event),
            'attendance_capture' => $attendance_capture,
            'participation_capture' => $this->event->participationCaptureState(),
            'now_date_label' => now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY'),
            'now_time_label' => now()->locale('es')->isoFormat('h:mm a'),
            'can_start_attendance' => $can_start_attendance,
            'can_close_attendance' => $can_close_attendance,
            'attendance_started' => $attendance_started,
            'attendance_closed' => $attendance_closed,
            'attendance_rows' => $attendance_rows,
            'attendee_type_options' => $this->attendeeTypeOptions(),
            'attendance_chart_labels' => $attendance_rows->pluck('name')->map(fn ($name) => (string) $name)->all(),
            'attendance_chart_values' => $attendance_rows->map(fn ($type) => (int) $type->pivot->attendance)->all(),
        ]);
    }

    /** @return Collection<int, AttendeeType> */
    private function attendeeTypeOptions(): Collection
    {
        return AttendeeType::query()
            ->where(function ($query) {
                $query->where('general', true)
                    ->orWhere('business_id', $this->event->business_id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'minimum_range', 'maximum_range']);
    }

    private function reloadEvent(): void
    {
        $this->event = Event::query()
            ->forAuthUser()
            ->with([
                'business:id,name',
                'category:id,name,type',
                'teams:id,name',
                'attendee_types' => fn ($query) => $query->orderBy('name'),
            ])
            ->findOrFail($this->event->id);
    }
}
