<?php

namespace App\Livewire\Forms\Admin\Events;

use App\Enums\EventCategoryType;
use App\Enums\Weekday;
use App\Models\Business;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventTeam;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EventForm extends Form
{
    public ?int $event_id = null;

    public ?int $business_id = null;

    public ?int $event_category_id = null;

    public string $name = '';

    public string $description = '';

    public string $date_start = '';

    public string $date_end = '';

    public string $start_time = '';

    public string $end_time = '';

    public bool $attendance_enabled = true;

    public bool $participation_enabled = true;

    /** weekdays | specific_dates */
    public string $schedule_mode = 'weekdays';

    public string $year = '';

    public string $start_month = '';

    public string $end_month = '';

    /** Mes del calendario para fechas específicas. */
    public string $specific_month = '';

    /** @var list<int|string> */
    public array $weekdays = [];

    /** @var list<string> fechas Y-m-d */
    public array $specific_dates = [];

    /** @var list<int|string> */
    public array $event_team_ids = [];

    public function setEvent(Event $event): void
    {
        $this->event_id = $event->id;
        $this->business_id = $event->business_id;
        $this->event_category_id = $event->event_category_id;
        $this->name = $event->name;
        $this->description = $event->description ?? '';
        $this->date_start = $event->date_start?->format('Y-m-d') ?? '';
        $this->date_end = $event->date_end?->format('Y-m-d') ?? '';
        $this->start_time = substr((string) $event->start_time, 0, 5);
        $this->end_time = substr((string) $event->end_time, 0, 5);
        $this->attendance_enabled = (bool) $event->attendance_enabled;
        $this->participation_enabled = (bool) $event->participation_enabled;
        $this->event_team_ids = $event->teams()
            ->pluck('event_teams.id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function setCategory(EventCategory $category): void
    {
        $this->event_category_id = $category->id;
    }

    public function isEditing(): bool
    {
        return $this->event_id !== null;
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function resolvedBusinessId(): int
    {
        if ($this->isSuperAdmin()) {
            return (int) $this->business_id;
        }

        return (int) auth()->user()->business_id;
    }

    public function isPeriodicCategory(): bool
    {
        if ($this->isEditing() || ! $this->event_category_id) {
            return false;
        }

        $category = EventCategory::query()
            ->visibleToUser()
            ->find($this->event_category_id);

        return $category?->type === EventCategoryType::Periodic;
    }

    public function usesWeekdaySchedule(): bool
    {
        return $this->schedule_mode === 'weekdays';
    }

    public function usesSpecificDatesSchedule(): bool
    {
        return $this->schedule_mode === 'specific_dates';
    }

    /**
     * @return list<list<array{date: ?string, day: ?int, in_month: bool, selected: bool, disabled: bool}>>
     */
    public function specificDatesCalendarWeeks(): array
    {
        if ($this->year === '' || $this->specific_month === '') {
            return [];
        }

        $year = (int) $this->year;
        $month = (int) $this->specific_month;

        if ($year < 2000 || $month < 1 || $month > 12) {
            return [];
        }

        $selected = array_flip($this->specific_dates);
        $today = now()->startOfDay();
        $month_start = Carbon::create($year, $month, 1)->startOfDay();
        $cursor = $month_start->copy()->startOfWeek(Carbon::MONDAY);
        $month_end = $month_start->copy()->endOfMonth()->startOfDay();
        $grid_end = $month_end->copy()->endOfWeek(Carbon::SUNDAY);

        $weeks = [];
        $week = [];

        while ($cursor->lte($grid_end)) {
            $in_month = $cursor->month === $month && $cursor->year === $year;
            $date = $cursor->toDateString();
            $disabled = ! $in_month || $cursor->lt($today);

            $week[] = [
                'date' => $in_month ? $date : null,
                'day' => $in_month ? $cursor->day : null,
                'in_month' => $in_month,
                'selected' => $in_month && isset($selected[$date]),
                'disabled' => $disabled,
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }

            $cursor->addDay();
        }

        return $weeks;
    }

    /** @return Collection<int, Business> */
    public function getBusinesses(): Collection
    {
        return Business::query()
            ->whereNull('deleted_at')
            ->whereHas('organization_type', fn ($query) => $query->where('label', 'iglesia'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /** @return Collection<int, EventTeam> */
    public function getTeams(): Collection
    {
        if ($this->isSuperAdmin() && ! $this->business_id) {
            return collect();
        }

        $business_id = $this->resolvedBusinessId();

        if ($business_id <= 0) {
            return collect();
        }

        return EventTeam::query()
            ->forAuthUser()
            ->where('business_id', $business_id)
            ->where(function ($query) {
                $query->where('active', true);

                if ($this->event_team_ids !== []) {
                    $query->orWhereIn('id', array_map('intval', $this->event_team_ids));
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'active']);
    }

    /** @return list<int> */
    public function allowedTeamIds(): array
    {
        return $this->getTeams()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /** @return array<int, string> */
    public function yearOptions(): array
    {
        $current_year = (int) now()->year;
        $options = [];

        for ($year = $current_year; $year <= $current_year + 5; $year++) {
            $options[$year] = (string) $year;
        }

        return $options;
    }

    /** @return array<int, string> */
    public function monthOptions(): array
    {
        return [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
    }

    public function rules(): array
    {
        $business_id = $this->resolvedBusinessId();
        $is_periodic = $this->isPeriodicCategory();
        $team_ids = $this->allowedTeamIds();

        $category_ids = EventCategory::query()
            ->visibleToUser()
            ->pluck('id')
            ->all();

        $rules = [
            'business_id' => [
                Rule::requiredIf(fn () => $this->isSuperAdmin()),
                'nullable',
                'integer',
                Rule::exists('businesses', 'id')->whereNull('deleted_at'),
            ],
            'event_category_id' => [
                'required',
                'integer',
                Rule::in($category_ids),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'attendance_enabled' => ['required', 'boolean'],
            'participation_enabled' => ['required', 'boolean'],
            'event_team_ids' => ['nullable', 'array'],
            'event_team_ids.*' => ['integer', Rule::in($team_ids)],
        ];

        if ($is_periodic) {
            $rules['end_time'][] = 'after:start_time';
            $rules['schedule_mode'] = ['required', Rule::in(['weekdays', 'specific_dates'])];
            $rules['year'] = ['required', 'integer', Rule::in(array_keys($this->yearOptions()))];

            if ($this->usesWeekdaySchedule()) {
                $rules['start_month'] = ['required', 'integer', 'min:1', 'max:12'];
                $rules['end_month'] = ['required', 'integer', 'min:1', 'max:12', 'gte:start_month'];
                $rules['weekdays'] = ['required', 'array', 'min:1'];
                $rules['weekdays.*'] = ['integer', Rule::in(array_column(Weekday::cases(), 'value'))];
            }

            if ($this->usesSpecificDatesSchedule()) {
                $rules['specific_month'] = ['required', 'integer', 'min:1', 'max:12'];
                $rules['specific_dates'] = ['required', 'array', 'min:1'];
                $rules['specific_dates.*'] = [
                    'required',
                    'date_format:Y-m-d',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        if (! is_string($value) || $this->year === '' || $this->specific_month === '') {
                            $fail('La fecha seleccionada no es válida.');

                            return;
                        }

                        $date = Carbon::createFromFormat('Y-m-d', $value);

                        if (
                            (int) $date->year !== (int) $this->year
                            || (int) $date->month !== (int) $this->specific_month
                        ) {
                            $fail('Las fechas deben pertenecer al mes y año seleccionados.');
                        }

                        if ($date->lt(now()->startOfDay())) {
                            $fail('No puedes seleccionar fechas anteriores a hoy.');
                        }
                    },
                ];
            }
        } else {
            $rules['date_start'] = ['required', 'date'];
            $rules['date_end'] = ['required', 'date', 'after_or_equal:date_start'];

            if (! $this->isEditing()) {
                $rules['date_start'][] = 'after_or_equal:today';
            }

            if ($this->date_end === '' || $this->date_start === $this->date_end) {
                $rules['end_time'][] = 'after:start_time';
            }

            $rules['name'][] = Rule::unique('events', 'name')
                ->where(fn ($query) => $query
                    ->where('business_id', $business_id)
                    ->where('date_start', $this->date_start !== '' ? $this->date_start : null)
                    ->whereNull('deleted_at'))
                ->ignore($this->event_id);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'business_id.required' => 'Selecciona una iglesia.',
            'business_id.exists' => 'La iglesia seleccionada no es válida.',
            'event_category_id.required' => 'La categoría es obligatoria.',
            'event_category_id.in' => 'La categoría seleccionada no es válida.',
            'name.required' => 'El nombre es obligatorio.',
            'name.unique' => 'Ya existe un evento con este nombre en esa fecha de inicio.',
            'date_start.required' => 'La fecha de inicio es obligatoria.',
            'date_start.after_or_equal' => 'La fecha de inicio no puede ser anterior al día de hoy.',
            'date_end.required' => 'La fecha de fin es obligatoria.',
            'date_end.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio.',
            'start_time.required' => 'La hora de inicio es obligatoria.',
            'start_time.date_format' => 'La hora de inicio no es válida.',
            'end_time.required' => 'La hora de fin es obligatoria.',
            'end_time.date_format' => 'La hora de fin no es válida.',
            'end_time.after' => 'La hora de fin debe ser posterior a la de inicio.',
            'schedule_mode.required' => 'Selecciona cómo definir las fechas.',
            'schedule_mode.in' => 'La opción de fechas seleccionada no es válida.',
            'year.required' => 'El año es obligatorio.',
            'year.in' => 'Selecciona un año válido.',
            'start_month.required' => 'El mes de inicio es obligatorio.',
            'end_month.required' => 'El mes de fin es obligatorio.',
            'end_month.gte' => 'El mes de fin debe ser igual o posterior al de inicio.',
            'weekdays.required' => 'Selecciona al menos un día de la semana.',
            'weekdays.min' => 'Selecciona al menos un día de la semana.',
            'specific_month.required' => 'El mes es obligatorio.',
            'specific_dates.required' => 'Selecciona al menos una fecha en el calendario.',
            'specific_dates.min' => 'Selecciona al menos una fecha en el calendario.',
            'event_team_ids.*.in' => 'Uno de los equipos seleccionados no es válido.',
        ];
    }

    /**
     * @return array{
     *     event_category_id: int,
     *     name: string,
     *     description: ?string,
     *     date_start?: string,
     *     date_end?: string,
     *     start_time: string,
     *     end_time: string,
     *     attendance_enabled: bool,
     *     participation_enabled: bool,
     *     schedule_mode?: string,
     *     year?: int,
     *     start_month?: int,
     *     end_month?: int,
     *     weekdays?: list<int>,
     *     specific_month?: int,
     *     specific_dates?: list<string>,
     *     event_team_ids: list<int>
     * }
     */
    public function validated(): array
    {
        $data = $this->validate();
        $is_periodic = $this->isPeriodicCategory();

        $payload = [
            'event_category_id' => (int) $data['event_category_id'],
            'name' => $data['name'],
            'description' => ($data['description'] ?? '') !== '' ? $data['description'] : null,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'attendance_enabled' => (bool) $data['attendance_enabled'],
            'participation_enabled' => (bool) $data['participation_enabled'],
            'event_team_ids' => array_values(array_map('intval', $data['event_team_ids'] ?? [])),
        ];

        if ($is_periodic) {
            $payload['schedule_mode'] = $data['schedule_mode'];
            $payload['year'] = (int) $data['year'];

            if ($this->usesWeekdaySchedule()) {
                $payload['start_month'] = (int) $data['start_month'];
                $payload['end_month'] = (int) $data['end_month'];
                $payload['weekdays'] = array_values(array_map('intval', $data['weekdays']));
            } else {
                $payload['specific_month'] = (int) $data['specific_month'];
                $payload['specific_dates'] = array_values(array_unique($data['specific_dates']));
                sort($payload['specific_dates']);
            }
        } else {
            $payload['date_start'] = $data['date_start'];
            $payload['date_end'] = $data['date_end'];
        }

        return $payload;
    }
}
