<?php

namespace App\Livewire\Forms\Admin;

use App\Models\OrganizationType;
use Illuminate\Validation\Rule;
use Livewire\Form;

class OrganizationTypeForm extends Form
{
    public ?int $organization_type_id = null;

    public ?int $business_type_id = null;

    public string $name = '';

    public bool $active = true;

    public function rules(): array
    {
        return [
            'business_type_id' => 'required|exists:business_types,id',
            'name'             => [
                'required',
                'string',
                'max:150',
                Rule::unique('organization_types', 'name')
                    ->where('business_type_id', $this->business_type_id)
                    ->whereNull('deleted_at')
                    ->ignore($this->organization_type_id),
            ],
            'active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'business_type_id.required' => 'Selecciona el tipo de negocio.',
            'name.required'             => 'El nombre es obligatorio.',
            'name.max'                  => 'El nombre no debe superar 150 caracteres.',
            'name.unique'               => 'Ya existe este tipo de organización para el negocio seleccionado.',
        ];
    }

    public function setOrganizationType(OrganizationType $organization_type): void
    {
        $this->organization_type_id = $organization_type->id;
        $this->business_type_id     = $organization_type->business_type_id;
        $this->name                 = $organization_type->name;
        $this->active               = (bool) $organization_type->active;
    }

    public function isEditing(): bool
    {
        return $this->organization_type_id !== null;
    }

    /** @return array{business_type_id: int, name: string, active: bool} */
    public function validated(): array
    {
        $this->validate();

        return [
            'business_type_id' => (int) $this->business_type_id,
            'name'             => trim($this->name),
            'active'           => $this->active,
        ];
    }
}
