<?php

namespace App\Livewire\Forms\Admin\Events;

use App\Enums\EventCategoryType;
use App\Enums\Weekday;
use App\Models\Business;
use App\Models\Event;
use App\Models\EventCategory;
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

    public string $date = '';

    public string $start_time = '';

    public string $end_time = '';

    public string $year = '';

    public string $start_month = '';

    public string $end_month = '';

    /** @var list<int|string> */
    public array $weekdays = [];

    public function setEvent(Event $event): void
    {
        $this->event_id = $event->id;
        $this->business_id = $event->business_id;
        $this->event_category_id = $event->event_category_id;
        $this->name = $event->name;
        $this->description = $event->description ?? '';
        $this->date = $event->date?->format('Y-m-d') ?? '';
        $this->start_time = substr((string) $event->start_time, 0, 5);
        $this->end_time = substr((string) $event->end_time, 0, 5);
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

    /** @return Collection<int, Business> */
    public function getBusinesses(): Collection
    {
        return Business::query()
            ->whereNull('deleted_at')
            ->whereHas('organization_type', fn ($query) => $query->where('label', 'iglesia'))
            ->orderBy('name')
            ->get(['id', 'name']);
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
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];

        if ($is_periodic) {
            $rules['year'] = ['required', 'integer', 'min:2000', 'max:2100'];
            $rules['start_month'] = ['required', 'integer', 'min:1', 'max:12'];
            $rules['end_month'] = ['required', 'integer', 'min:1', 'max:12', 'gte:start_month'];
            $rules['weekdays'] = ['required', 'array', 'min:1'];
            $rules['weekdays.*'] = ['integer', Rule::in(array_column(Weekday::cases(), 'value'))];
        } else {
            $rules['date'] = ['required', 'date'];

            if (! $this->isEditing()) {
                $rules['date'][] = 'after_or_equal:today';
            }

            $rules['name'][] = Rule::unique('events', 'name')
                ->where(fn ($query) => $query
                    ->where('business_id', $business_id)
                    ->where('date', $this->date !== '' ? $this->date : null)
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
            'name.unique' => 'Ya existe un evento con este nombre en esa fecha.',
            'date.required' => 'La fecha es obligatoria.',
            'date.after_or_equal' => 'La fecha no puede ser anterior al día de hoy.',
            'start_time.required' => 'La hora de inicio es obligatoria.',
            'start_time.date_format' => 'La hora de inicio no es válida.',
            'end_time.required' => 'La hora de fin es obligatoria.',
            'end_time.date_format' => 'La hora de fin no es válida.',
            'end_time.after' => 'La hora de fin debe ser posterior a la de inicio.',
            'year.required' => 'El año es obligatorio.',
            'start_month.required' => 'El mes de inicio es obligatorio.',
            'end_month.required' => 'El mes de fin es obligatorio.',
            'end_month.gte' => 'El mes de fin debe ser igual o posterior al de inicio.',
            'weekdays.required' => 'Selecciona al menos un día de la semana.',
            'weekdays.min' => 'Selecciona al menos un día de la semana.',
        ];
    }

    /**
     * @return array{
     *     event_category_id: int,
     *     name: string,
     *     description: ?string,
     *     date?: string,
     *     start_time: string,
     *     end_time: string,
     *     year?: int,
     *     start_month?: int,
     *     end_month?: int,
     *     weekdays?: list<int>
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
        ];

        if ($is_periodic) {
            $payload['year'] = (int) $data['year'];
            $payload['start_month'] = (int) $data['start_month'];
            $payload['end_month'] = (int) $data['end_month'];
            $payload['weekdays'] = array_values(array_map('intval', $data['weekdays']));
        } else {
            $payload['date'] = $data['date'];
        }

        return $payload;
    }
}
