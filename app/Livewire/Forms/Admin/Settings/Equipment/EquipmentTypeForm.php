<?php

namespace App\Livewire\Forms\Admin\Settings\Equipment;

use App\Actions\Settings\Equipment\CreateOrUpdateEquipmentTypeAction;
use App\Models\EquipmentType;
use App\Rules\NotConflictingWithGeneralCatalogName;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EquipmentTypeForm extends Form
{
    public ?int $equipment_type_id = null;

    public string $name   = '';
    public bool   $active = true;

    public function setEquipmentType(EquipmentType $equipment_type): void
    {
        $this->equipment_type_id = $equipment_type->id;
        $this->name              = $equipment_type->name;
        $this->active            = $equipment_type->active;
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function resolvedBusinessId(): ?int
    {
        return $this->isSuperAdmin() ? null : auth()->user()?->business_id;
    }

    public function resolvedGeneral(): bool
    {
        return $this->isSuperAdmin();
    }

    public function rules(): array
    {
        $business_id = $this->resolvedBusinessId();
        $general     = $this->resolvedGeneral();

        $scope = function ($query) use ($business_id, $general) {
            if ($general) {
                $query->whereNull('business_id')->where('general', true);
            } else {
                $query->where('business_id', $business_id)->where('general', false);
            }
        };

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                new NotConflictingWithGeneralCatalogName(EquipmentType::class, $this->equipment_type_id),
                Rule::unique('equipment_types', 'name')->where($scope)->ignore($this->equipment_type_id),
                function (string $attribute, mixed $value, \Closure $fail) use ($scope) {
                    $name = mb_strtolower(trim((string) $value));

                    if ($name === '') {
                        $fail('El nombre es obligatorio.');
                        return;
                    }

                    $query = EquipmentType::query()->whereRaw('LOWER(TRIM(name)) = ?', [$name]);
                    $scope($query);

                    if ($this->equipment_type_id) {
                        $query->where('id', '!=', $this->equipment_type_id);
                    }

                    if ($query->exists()) {
                        $fail('Ya existe un tipo con este nombre.');
                        return;
                    }

                    $label = CreateOrUpdateEquipmentTypeAction::normalizeLabel((string) $value);

                    if ($label === '') {
                        $fail('El nombre debe contener al menos una letra o número.');
                        return;
                    }

                    $query = EquipmentType::query()->where('label', $label);
                    $scope($query);

                    if ($this->equipment_type_id) {
                        $query->where('id', '!=', $this->equipment_type_id);
                    }

                    if ($query->exists()) {
                        $fail('Ya existe un tipo con un nombre equivalente.');
                    }
                },
            ],
            'active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max'      => 'El nombre no puede superar 100 caracteres.',
            'name.unique'   => 'Ya existe un tipo con este nombre.',
        ];
    }

    public function isEditing(): bool
    {
        return (bool) $this->equipment_type_id;
    }

    public function validated(): array
    {
        $this->validate();

        return [
            'name'   => trim($this->name),
            'active' => $this->active,
        ];
    }
}
