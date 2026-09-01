<?php

namespace App\Livewire\Forms\Admin\Settings\Equipment;

use App\Actions\Settings\Equipment\CreateOrUpdateEquipmentTypeAction;
use App\Models\Business;
use App\Models\EquipmentType;
use App\Rules\NotConflictingWithGeneralCatalogName;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EquipmentTypeForm extends Form
{
    public ?int $equipment_type_id = null;

    public string $name = '';

    public bool $active = true;

    /** @var array<int> */
    public array $business_ids = [];

    public function setEquipmentType(EquipmentType $equipment_type): void
    {
        $equipment_type->load('businesses');

        $this->equipment_type_id = $equipment_type->id;
        $this->name              = $equipment_type->name;
        $this->active            = $equipment_type->active;
        $this->business_ids      = $equipment_type->businesses
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->equipment_type_id = null;
        $this->name              = '';
        $this->active            = true;
        $this->business_ids      = [];
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function getActiveBusinesses(): Collection
    {
        return Business::query()
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function rules(): array
    {
        $active_business_ids = $this->getActiveBusinesses()->pluck('id')->all();

        $scope = function ($query) {
            $query->whereNull('deleted_at')
                ->whereNull('business_id')
                ->where('general', true);
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
            'active'        => ['boolean'],
            'business_ids'  => ['required', 'array', 'min:1'],
            'business_ids.*' => ['integer', Rule::in($active_business_ids)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'El nombre es obligatorio.',
            'name.max'              => 'El nombre no puede superar 100 caracteres.',
            'name.unique'           => 'Ya existe un tipo con este nombre.',
            'business_ids.required' => 'Debe seleccionar al menos un negocio.',
            'business_ids.min'      => 'Debe seleccionar al menos un negocio.',
            'business_ids.*.in'     => 'Uno de los negocios seleccionados no es válido.',
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
            'name'          => trim($this->name),
            'active'        => $this->active,
            'business_ids'  => array_map('intval', $this->business_ids),
        ];
    }
}
