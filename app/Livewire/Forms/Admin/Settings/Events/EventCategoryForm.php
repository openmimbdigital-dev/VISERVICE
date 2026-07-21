<?php

namespace App\Livewire\Forms\Admin\Settings\Events;

use App\Enums\EventCategoryType;
use App\Models\EventCategory;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EventCategoryForm extends Form
{
    public ?int $event_category_id = null;

    public string $name = '';

    public string $description = '';

    public string $type = '';

    public function setEventCategory(EventCategory $event_category): void
    {
        $this->event_category_id = $event_category->id;
        $this->name              = $event_category->name;
        $this->description       = $event_category->description ?? '';
        $this->type              = $event_category->type->value;
    }

    public function isEditing(): bool
    {
        return $this->event_category_id !== null;
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
                Rule::unique('event_categories', 'name')
                    ->where(fn ($query) => $query
                        ->where('general', $general)
                        ->when(
                            $general,
                            fn ($q) => $q->whereNull('business_id'),
                            fn ($q) => $q->where('business_id', $business_id)
                        )
                        ->whereNull('deleted_at'))
                    ->ignore($this->event_category_id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'type'        => ['required', Rule::enum(EventCategoryType::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max'      => 'El nombre no puede superar 150 caracteres.',
            'name.unique'   => 'Ya existe una categoría de evento con este nombre.',
            'description.max' => 'La descripción no puede superar 2000 caracteres.',
            'type.required' => 'Selecciona el tipo de categoría.',
            'type.enum'     => 'El tipo de categoría seleccionado no es válido.',
        ];
    }

    /** @return array{name: string, description: ?string, type: string} */
    public function validated(): array
    {
        $data = $this->validate();

        return [
            'name'        => trim($data['name']),
            'description' => filled($data['description']) ? trim($data['description']) : null,
            'type'        => $data['type'],
        ];
    }
}
