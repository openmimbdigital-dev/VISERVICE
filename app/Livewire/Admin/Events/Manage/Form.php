<?php

namespace App\Livewire\Admin\Events\Manage;

use App\Actions\Events\CreateMultiDayEventAction;
use App\Actions\Events\CreateOrUpdateEventAction;
use App\Actions\Events\CreatePeriodicEventsAction;
use App\Enums\Weekday;
use App\Livewire\Forms\Admin\Events\EventForm;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventTeam;
use App\Support\EventsAccess;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public EventForm $form;

    public EventCategory $event_category;

    public bool $show_team_modal = false;

    public ?int $preview_team_id = null;

    public function mount(EventCategory $eventCategory, ?Event $event = null): void
    {
        $this->event_category = $eventCategory;

        if ($event?->exists) {
            $record = Event::query()
                ->forAuthUser()
                ->where('event_category_id', $eventCategory->id)
                ->whereNull('parent_id')
                ->with([
                    'teams:id',
                    'children' => fn ($query) => $query->orderBy('date_start'),
                ])
                ->findOrFail($event->id);

            EventsAccess::authorizeEditEvent($record);

            $this->form->setEvent($record);

            return;
        }

        EventsAccess::authorizeCreateEvents();
        $this->form->reset();
        $this->form->setCategory($eventCategory);
        $this->form->schedule_mode = 'weekdays';
        $this->form->year = (string) now()->year;
        $this->form->specific_month = (string) now()->month;

        if (! $this->form->isSuperAdmin()) {
            $this->form->business_id = auth()->user()?->business_id;
        }
    }

    public function updatedFormBusinessId(): void
    {
        $this->form->event_team_ids = [];
        $this->closeTeamDetail();
    }

    public function updatedFormScheduleMode(string $value): void
    {
        if ($value === 'weekdays') {
            $this->form->specific_dates = [];
            $this->form->resetErrorBag('specific_dates', 'specific_month', 'specific_dates.*');

            return;
        }

        $this->form->weekdays = [];
        $this->form->start_month = '';
        $this->form->end_month = '';
        $this->form->resetErrorBag('weekdays', 'weekdays.*', 'start_month', 'end_month');

        if ($this->form->specific_month === '') {
            $this->form->specific_month = (string) now()->month;
        }
    }

    public function updatedFormYear(): void
    {
        $this->form->specific_dates = [];
    }

    public function updatedFormSpecificMonth(): void
    {
        $this->form->specific_dates = [];
    }

    public function toggleSpecificDate(string $date): void
    {
        abort_unless($this->form->isPeriodicCategory(), 403);
        abort_unless($this->form->usesSpecificDatesSchedule(), 403);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return;
        }

        $carbon = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();

        if (
            (int) $carbon->year !== (int) $this->form->year
            || (int) $carbon->month !== (int) $this->form->specific_month
            || $carbon->lt(now()->startOfDay())
        ) {
            return;
        }

        if (in_array($date, $this->form->specific_dates, true)) {
            $this->form->specific_dates = array_values(array_filter(
                $this->form->specific_dates,
                fn (string $selected) => $selected !== $date
            ));

            return;
        }

        $this->form->specific_dates[] = $date;
        $dates = $this->form->specific_dates;
        sort($dates);
        $this->form->specific_dates = $dates;
    }

    public function updatedFormDateStart(): void
    {
        $this->form->syncDaySchedules();
    }

    public function updatedFormDateEnd(): void
    {
        $this->form->syncDaySchedules();
    }

    public function openTeamDetail(int $team_id): void
    {
        $business_id = $this->form->resolvedBusinessId();

        abort_unless(
            EventTeam::query()
                ->forAuthUser()
                ->when($business_id > 0, fn ($query) => $query->where('business_id', $business_id))
                ->whereKey($team_id)
                ->exists(),
            404
        );

        $this->preview_team_id = $team_id;
        $this->show_team_modal = true;
    }

    public function closeTeamDetail(): void
    {
        $this->show_team_modal = false;
        $this->preview_team_id = null;
    }

    private function previewTeam(): ?EventTeam
    {
        if (! $this->show_team_modal || ! $this->preview_team_id) {
            return null;
        }

        return EventTeam::query()
            ->forAuthUser()
            ->with([
                'roles' => fn ($query) => $query->orderBy('name')->select('event_team_roles.id', 'event_team_roles.name', 'event_team_roles.functions'),
                'members' => fn ($query) => $query->with([
                    'user:id,first_name,last_name',
                    'role:id,name',
                ]),
            ])
            ->find($this->preview_team_id);
    }

    private function authorizeSave(): void
    {
        if (! $this->form->isEditing()) {
            EventsAccess::authorizeCreateEvents();

            return;
        }

        $record = Event::query()
            ->forAuthUser()
            ->where('event_category_id', $this->event_category->id)
            ->whereNull('parent_id')
            ->findOrFail($this->form->event_id);

        EventsAccess::authorizeEditEvent($record);
    }

    public function save(): void
    {
        $this->authorizeSave();

        $this->form->event_category_id = $this->event_category->id;

        $business_id = $this->form->resolvedBusinessId();
        $data = $this->form->validated();

        try {
            if (! $this->form->isEditing() && $this->form->isPeriodicCategory()) {
                $events = CreatePeriodicEventsAction::run($business_id, $data);

                $this->dispatch('swal', [
                    'title' => 'Se crearon '.$events->count().' eventos correctamente.',
                    'icon' => 'success',
                ]);

                $this->redirectRoute('admin.events.manage.category.index', $this->event_category, navigate: true);

                return;
            }

            if (
                ! $this->form->isPeriodicCategory()
                && ($data['date_start'] ?? '') < ($data['date_end'] ?? '')
            ) {
                CreateMultiDayEventAction::run(
                    $business_id,
                    $this->form->event_id,
                    $data
                );

                $this->dispatch('swal', [
                    'title' => $this->form->isEditing()
                        ? 'Evento multi-día actualizado correctamente.'
                        : 'Evento multi-día creado correctamente.',
                    'icon' => 'success',
                ]);

                $this->redirectRoute('admin.events.manage.category.index', $this->event_category, navigate: true);

                return;
            }

            CreateOrUpdateEventAction::run(
                $business_id,
                $this->form->event_id,
                $data
            );

            $this->dispatch('swal', [
                'title' => $this->form->isEditing()
                    ? 'Evento actualizado correctamente.'
                    : 'Evento creado correctamente.',
                'icon' => 'success',
            ]);

            $this->redirectRoute('admin.events.manage.category.index', $this->event_category, navigate: true);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first()
                ?? 'No se pudo guardar el evento. Revisa los datos.';

            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            $this->dispatch('swal', [
                'title' => $message,
                'icon' => 'warning',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.events.manage.form', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'is_periodic' => $this->form->isPeriodicCategory(),
            'is_multi_day' => $this->form->isMultiDayOccasional(),
            'businesses' => $this->form->isSuperAdmin() ? $this->form->getBusinesses() : collect(),
            'teams' => $this->form->getTeams(),
            'preview_team' => $this->previewTeam(),
            'month_options' => $this->form->monthOptions(),
            'year_options' => $this->form->yearOptions(),
            'weekday_options' => Weekday::options(),
            'calendar_weeks' => $this->form->isPeriodicCategory() && $this->form->usesSpecificDatesSchedule()
                ? $this->form->specificDatesCalendarWeeks()
                : [],
            'calendar_weekday_headers' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
        ])->layoutData([
            'title' => $this->form->isEditing()
                ? 'Gestión de eventos — Editar evento'
                : 'Gestión de eventos — Nuevo evento',
        ]);
    }
}
