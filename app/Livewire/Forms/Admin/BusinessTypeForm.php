<?php

namespace App\Livewire\Forms\Admin;

use App\Models\BusinessType;
use Illuminate\Validation\Rule;
use Livewire\Form;

class BusinessTypeForm extends Form
{
    public ?int $business_type_id = null;

    public string $name = '';

    public bool $status = true;

    public function rules(): array
    {
        return [
            'name'   => [
                'required',
                'string',
                'max:150',
                Rule::unique('business_types', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($this->business_type_id),
            ],
            'status' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max'      => 'El nombre no debe superar 150 caracteres.',
            'name.unique'   => 'Ya existe un tipo de negocio con ese nombre.',
        ];
    }

    public function setBusinessType(BusinessType $business_type): void
    {
        $this->business_type_id = $business_type->id;
        $this->name             = $business_type->name;
        $this->status           = (bool) $business_type->status;
    }

    public function isEditing(): bool
    {
        return $this->business_type_id !== null;
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
