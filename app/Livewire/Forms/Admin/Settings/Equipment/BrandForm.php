<?php

namespace App\Livewire\Forms\Admin\Settings\Equipment;

use App\Actions\Settings\Equipment\CreateOrUpdateBrandAction;
use App\Models\Brand;
use App\Models\EquipmentType;
use App\Rules\NotConflictingWithGeneralCatalogName;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class BrandForm extends Form
{
    public ?int $brand_id = null;

    public string $name = '';

    public bool $active = true;

    /** @var array<int> */
    public array $equipment_type_ids = [];

    public function setBrand(Brand $brand): void
    {
        $brand->load('equipmentTypes');

        $this->brand_id           = $brand->id;
        $this->name               = $brand->name;
        $this->active             = $brand->active;
        $this->equipment_type_ids = $brand->equipmentTypes
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->brand_id           = null;
        $this->name               = '';
        $this->active             = true;
        $this->equipment_type_ids = [];
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

    public function getAvailableEquipmentTypes(): Collection
    {
        $query = EquipmentType::query()->orderBy('name');

        if (! $this->isSuperAdmin()) {
            $query->visibleToUser();
        }

        return $query->get(['id', 'name', 'active']);
    }

    public function rules(): array
    {
        $business_id = $this->resolvedBusinessId();
        $general     = $this->resolvedGeneral();

        $scope = function ($query) use ($business_id, $general) {
            $query->whereNull('deleted_at');

            if ($general) {
                $query->whereNull('business_id')->where('general', true);
            } else {
                $query->where('business_id', $business_id)->where('general', false);
            }
        };

        $available_equipment_type_ids = $this->getAvailableEquipmentTypes()->pluck('id')->all();

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                new NotConflictingWithGeneralCatalogName(Brand::class, $this->brand_id),
                Rule::unique('brands', 'name')->where($scope)->ignore($this->brand_id),
                function (string $attribute, mixed $value, \Closure $fail) use ($scope) {
                    $name = mb_strtolower(trim((string) $value));

                    if ($name === '') {
                        $fail('El nombre es obligatorio.');
                        return;
                    }

                    $query = Brand::query()->whereRaw('LOWER(TRIM(name)) = ?', [$name]);
                    $scope($query);

                    if ($this->brand_id) {
                        $query->where('id', '!=', $this->brand_id);
                    }

                    if ($query->exists()) {
                        $fail('Ya existe una marca con este nombre.');
                        return;
                    }

                    $label = CreateOrUpdateBrandAction::normalizeLabel((string) $value);

                    if ($label === '') {
                        $fail('El nombre debe contener al menos una letra o número.');
                        return;
                    }

                    $query = Brand::query()->where('label', $label);
                    $scope($query);

                    if ($this->brand_id) {
                        $query->where('id', '!=', $this->brand_id);
                    }

                    if ($query->exists()) {
                        $fail('Ya existe una marca con un nombre equivalente.');
                    }
                },
            ],
            'active'               => ['boolean'],
            'equipment_type_ids'   => ['required', 'array', 'min:1'],
            'equipment_type_ids.*' => ['integer', Rule::in($available_equipment_type_ids)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                  => 'El nombre es obligatorio.',
            'name.max'                       => 'El nombre no puede superar 100 caracteres.',
            'name.unique'                    => 'Ya existe una marca con este nombre.',
            'equipment_type_ids.required'    => 'Debe seleccionar al menos un tipo de equipo.',
            'equipment_type_ids.min'         => 'Debe seleccionar al menos un tipo de equipo.',
            'equipment_type_ids.*.in'        => 'Uno de los tipos de equipo seleccionados no es válido.',
        ];
    }

    public function isEditing(): bool
    {
        return (bool) $this->brand_id;
    }

    public function validated(): array
    {
        $this->validate();

        return [
            'name'                 => trim($this->name),
            'active'               => $this->active,
            'equipment_type_ids'   => array_map('intval', $this->equipment_type_ids),
        ];
    }
}
