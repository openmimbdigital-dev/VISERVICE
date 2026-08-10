<?php

namespace App\Livewire\Admin\Events\Schedule;

use App\Actions\Events\CloseEventAttendanceAction;
use App\Actions\Events\CloseEventParticipationAction;
use App\Actions\Events\ReopenEventAttendanceAction;
use App\Actions\Events\ReopenEventParticipationAction;
use App\Actions\Events\SetEventParticipantAttendanceAction;
use App\Actions\Events\StartEventAttendanceAction;
use App\Actions\Events\UpdateEventAttendanceCountAction;
use App\Models\AttendeeType;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\Participant;
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

    public string $participant_search = '';

    public function mount(Event $event): void
    {
        EventsAccess::authorizeViewSchedule();

        $this->event = Event::query()
            ->forAuthUser()
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

    public function setParticipantAttendance(int $participant_id, bool $attended): void
    {
        abort_unless(! $this->event->participation_closed, 403);

        SetEventParticipantAttendanceAction::run(
            $this->event->id,
            $participant_id,
            $attended
        );
    }

    public function confirmCloseParticipation(): void
    {
        abort_unless(EventsAccess::canCloseParticipation($this->event), 403);

        $this->confirm(
            '¿Cerrar la toma de participación?',
            ActionConfirmationAlert::options(
                title: 'Cerrar toma de participación',
                text: 'No podrás seguir marcando o desmarcando participantes.',
                confirm_button_text: 'Cerrar participación',
                on_confirmed: 'participation-close-confirmed',
            )
        );
    }

    #[On('participation-close-confirmed')]
    public function closeParticipation(): void
    {
        abort_unless(EventsAccess::canCloseParticipation($this->event), 403);

        CloseEventParticipationAction::run($this->event->id);
        $this->reloadEvent();

        $this->dispatch('swal', [
            'title' => 'Toma de participación cerrada.',
            'icon' => 'success',
        ]);
    }

    public function confirmReopenParticipation(): void
    {
        abort_unless(EventsAccess::canCloseParticipation($this->event), 403);

        $this->confirm(
            '¿Abrir la toma de participación?',
            ActionConfirmationAlert::options(
                title: 'Abrir toma de participación',
                text: 'Se habilitarán los checks para marcar participantes.',
                confirm_button_text: 'Abrir participación',
                on_confirmed: 'participation-reopen-confirmed',
            )
        );
    }

    #[On('participation-reopen-confirmed')]
    public function reopenParticipation(): void
    {
        abort_unless(EventsAccess::canCloseParticipation($this->event), 403);

        ReopenEventParticipationAction::run($this->event->id);
        $this->reloadEvent();

        $this->dispatch('swal', [
            'title' => 'Toma de participación abierta.',
            'icon' => 'success',
        ]);
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
        $participation_capture = $this->event->participationCaptureState();
        $attendance_rows = $this->event->attendee_types
            ->sortBy('name')
            ->values();
        $attendance_started = $attendance_rows->isNotEmpty();
        $attendance_closed = (bool) $this->event->attendance_closed;

        $manage_event = $this->event->parent ?? $this->event;
        $participant_attendance_states = $this->participantAttendanceStates();
        $attended_count = $participant_attendance_states->filter(fn ($attended) => $attended === true)->count();
        $participation_closed = (bool) $this->event->participation_closed;

        return view('livewire.admin.events.schedule.show', [
            'can_manage' => $has_category && EventsAccess::canManageEvent($manage_event),
            'can_edit' => $has_category && EventsAccess::hasEditPermission($manage_event),
            'edit_disabled' => $manage_event->hasStartedAttendance(),
            'edit_disabled_title' => 'Ya se inició la toma de asistencia',
            'attendance_capture' => $attendance_capture,
            'participation_capture' => $participation_capture,
            'now_date_label' => now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY'),
            'now_time_label' => now()->locale('es')->isoFormat('h:mm a'),
            'can_start_attendance' => $can_start_attendance,
            'can_close_attendance' => $can_close_attendance,
            'can_close_participation' => EventsAccess::canCloseParticipation($this->event),
            'attendance_started' => $attendance_started,
            'attendance_closed' => $attendance_closed,
            'participation_closed' => $participation_closed,
            'attendance_rows' => $attendance_rows,
            'attendee_type_options' => $this->attendeeTypeOptions(),
            'attendance_chart_labels' => $attendance_rows->pluck('name')->map(fn ($name) => (string) $name)->all(),
            'attendance_chart_values' => $attendance_rows->map(fn ($type) => (int) $type->pivot->attendance)->all(),
            'participants' => $this->participantsForParticipation(),
            'participant_attendance_states' => $participant_attendance_states,
            'attended_count' => $attended_count,
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

    /** @return Collection<int, Participant> */
    private function participantsForParticipation(): Collection
    {
        $query = Participant::query()
            ->forAuthUser()
            ->where('business_id', $this->event->business_id)
            ->where('status', true)
            ->orderBy('last_name')
            ->orderBy('first_name');

        $search = trim($this->participant_search);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%");
            });
        }

        return $query->get(['id', 'first_name', 'last_name', 'email', 'document_number']);
    }

    /**
     * Estados de participación ya registrados (solo quienes tienen fila en event_attendances).
     *
     * @return Collection<int, bool>
     */
    private function participantAttendanceStates(): Collection
    {
        if ($this->event->date_start === null) {
            return collect();
        }

        return EventAttendance::query()
            ->where('event_id', $this->event->id)
            ->where('attendable_type', Participant::class)
            ->whereDate('date_event', $this->event->date_start->toDateString())
            ->get(['attendable_id', 'attendance'])
            ->mapWithKeys(fn (EventAttendance $row) => [
                (int) $row->attendable_id => (bool) $row->attendance,
            ]);
    }

    private function reloadEvent(): void
    {
        $this->event = Event::query()
            ->forAuthUser()
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
