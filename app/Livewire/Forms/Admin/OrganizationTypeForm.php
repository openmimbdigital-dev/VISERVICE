<?php

namespace App\Livewire\Forms\Admin;

use App\Models\OrganizationType;
use Illuminate\Validation\Rule;
use Livewire\Form;

class OrganizationTypeForm extends Form
{
    public ?int $organization_type_id = null;

    public string $name = '';

    public bool $status = true;

    public function rules(): array
    {
        return [
            'name'   => [
                'required',
                'string',
                'max:150',
                Rule::unique('organization_types', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($this->organization_type_id),
            ],
            'status' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max'      => 'El nombre no debe superar 150 caracteres.',
            'name.unique'   => 'Ya existe un tipo de organización con ese nombre.',
        ];
    }

    public function setOrganizationType(OrganizationType $organization_type): void
    {
        $this->organization_type_id = $organization_type->id;
        $this->name                 = $organization_type->name;
        $this->status               = (bool) $organization_type->status;
    }

    public function isEditing(): bool
    {
        return $this->organization_type_id !== null;
    }

    /** @return array{name: string, status: bool} */
    public function validated(): array
    {
        $this->validate();

        return [
            'name'   => trim($this->name),
            'status' => $this->status,
        ];
    }
}
