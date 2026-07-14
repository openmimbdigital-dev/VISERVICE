<?php

namespace App\Livewire\Forms\Admin;

use App\Models\BusinessType;
use Illuminate\Validation\Rule;
use Livewire\Form;

class BusinessTypeForm extends Form
{
    public ?int $business_type_id = null;

    public ?int $organization_type_id = null;

    public string $name = '';

    public bool $active = true;

    public function rules(): array
    {
        return [
            'organization_type_id' => 'required|exists:organization_types,id',
            'name'                 => [
                'required',
                'string',
                'max:150',
                Rule::unique('business_types', 'name')
                    ->where('organization_type_id', $this->organization_type_id)
                    ->whereNull('deleted_at')
                    ->ignore($this->business_type_id),
            ],
            'active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'organization_type_id.required' => 'Selecciona el tipo de organización.',
            'name.required'                 => 'El nombre es obligatorio.',
            'name.max'                      => 'El nombre no debe superar 150 caracteres.',
            'name.unique'                   => 'Ya existe este tipo de negocio para la organización seleccionada.',
        ];
    }

    public function setBusinessType(BusinessType $business_type): void
    {
        $this->business_type_id     = $business_type->id;
        $this->organization_type_id = $business_type->organization_type_id;
        $this->name                 = $business_type->name;
        $this->active               = (bool) $business_type->active;
    }

    public function isEditing(): bool
    {
        return $this->business_type_id !== null;
    }

    /** @return array{organization_type_id: int, name: string, active: bool} */
    public function validated(): array
    {
        $this->validate();

        return [
            'organization_type_id' => (int) $this->organization_type_id,
            'name'                 => trim($this->name),
            'active'               => $this->active,
        ];
    }
}
