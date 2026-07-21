<?php

namespace App\Livewire\Forms\Admin\Settings\Events;

use App\Models\AttendeeType;
use Illuminate\Validation\Rule;
use Livewire\Form;

class AttendeeTypeForm extends Form
{
    public ?int $attendee_type_id = null;

    public string $name = '';

    public string $description = '';

    public ?int $minimum_range = null;

    public ?int $maximum_range = null;

    public function setAttendeeType(AttendeeType $attendee_type): void
    {
        $this->attendee_type_id = $attendee_type->id;
        $this->name             = $attendee_type->name;
        $this->description      = $attendee_type->description ?? '';
        $this->minimum_range    = $attendee_type->minimum_range;
        $this->maximum_range    = $attendee_type->maximum_range;
    }

    public function isEditing(): bool
    {
        return $this->attendee_type_id !== null;
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function resolvedBusinessId(): ?int
    {
        return $this->isSuperAdmin() ? null : auth()->user()?->business_id;
    }

    public function rules(): array
    {
        $business_id = $this->resolvedBusinessId();
        $general     = $this->isSuperAdmin();

        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('attendee_types', 'name')
                    ->where(fn ($query) => $query
                        ->where('general', $general)
                        ->when(
                            $general,
                            fn ($q) => $q->whereNull('business_id'),
                            fn ($q) => $q->where('business_id', $business_id)
                        )
                        ->whereNull('deleted_at'))
                    ->ignore($this->attendee_type_id),
            ],
            'description'   => ['nullable', 'string', 'max:2000'],
            'minimum_range' => ['required', 'integer', 'min:0', 'max:1000000'],
            'maximum_range' => ['required', 'integer', 'gte:minimum_range', 'max:1000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'El nombre es obligatorio.',
            'name.max'               => 'El nombre no puede superar 150 caracteres.',
            'name.unique'            => 'Ya existe un tipo de asistente con este nombre.',
            'description.max'        => 'La descripción no puede superar 2000 caracteres.',
            'minimum_range.required' => 'El rango mínimo es obligatorio.',
            'minimum_range.integer'  => 'El rango mínimo debe ser un número entero.',
            'minimum_range.min'      => 'El rango mínimo no puede ser negativo.',
            'maximum_range.required' => 'El rango máximo es obligatorio.',
            'maximum_range.integer'  => 'El rango máximo debe ser un número entero.',
            'maximum_range.gte'      => 'El rango máximo debe ser mayor o igual al rango mínimo.',
        ];
    }

    /** @return array{name: string, description: ?string, minimum_range: int, maximum_range: int} */
    public function validated(): array
    {
        $data = $this->validate();

        return [
            'name'          => trim($data['name']),
            'description'   => filled($data['description']) ? trim($data['description']) : null,
            'minimum_range' => (int) $data['minimum_range'],
            'maximum_range' => (int) $data['maximum_range'],
        ];
    }
}
