<?php

namespace App\Livewire\Forms\Admin\Settings\Equipment;

use App\Actions\Settings\Equipment\CreateOrUpdateEquipmentModelAction;
use App\Models\Brand;
use App\Models\EquipmentModel;
use App\Rules\NotConflictingWithGeneralCatalogName;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EquipmentModelForm extends Form
{
    public ?int $equipment_model_id = null;

    public ?int $brand_id = null;
    public string $name   = '';
    public bool   $active = true;

    public function setEquipmentModel(EquipmentModel $equipment_model): void
    {
        $this->equipment_model_id = $equipment_model->id;
        $this->brand_id           = $equipment_model->brand_id;
        $this->name               = $equipment_model->name;
        $this->active             = $equipment_model->active;
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
            $query->whereNull('deleted_at');

            if ($general) {
                $query->whereNull('business_id')->where('general', true);
            } else {
                $query->where('business_id', $business_id)->where('general', false);
            }
        };

        $visible_brand_ids = Brand::query()
            ->visibleToUser()
            ->pluck('id')
            ->all();

        return [
            'brand_id' => [
                'required',
                'integer',
                Rule::in($visible_brand_ids),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                new NotConflictingWithGeneralCatalogName(EquipmentModel::class, $this->equipment_model_id),
                Rule::unique('equipment_models', 'name')
                    ->where(function ($query) use ($scope) {
                        $scope($query);

                        if ($this->brand_id) {
                            $query->where('brand_id', $this->brand_id);
                        }
                    })
                    ->ignore($this->equipment_model_id),
                function (string $attribute, mixed $value, \Closure $fail) use ($scope) {
                    if (! $this->brand_id) {
                        return;
                    }

                    $name = mb_strtolower(trim((string) $value));

                    if ($name === '') {
                        $fail('El nombre es obligatorio.');
                        return;
                    }

                    $query = EquipmentModel::query()
                        ->where('brand_id', $this->brand_id)
                        ->whereRaw('LOWER(TRIM(name)) = ?', [$name]);
                    $scope($query);

                    if ($this->equipment_model_id) {
                        $query->where('id', '!=', $this->equipment_model_id);
                    }

                    if ($query->exists()) {
                        $fail('Ya existe un modelo con este nombre para la marca seleccionada.');
                        return;
                    }

                    $label = CreateOrUpdateEquipmentModelAction::normalizeLabel((string) $value);

                    if ($label === '') {
                        $fail('El nombre debe contener al menos una letra o número.');
                        return;
                    }

                    $query = EquipmentModel::query()
                        ->where('brand_id', $this->brand_id)
                        ->where('label', $label);
                    $scope($query);

                    if ($this->equipment_model_id) {
                        $query->where('id', '!=', $this->equipment_model_id);
                    }

                    if ($query->exists()) {
                        $fail('Ya existe un modelo con un nombre equivalente para esta marca.');
                    }
                },
            ],
            'active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'brand_id.required' => 'La marca es obligatoria.',
            'brand_id.in'       => 'La marca seleccionada no es válida.',
            'name.required'     => 'El nombre es obligatorio.',
            'name.max'          => 'El nombre no puede superar 100 caracteres.',
            'name.unique'       => 'Ya existe un modelo con este nombre para la marca seleccionada.',
        ];
    }

    public function isEditing(): bool
    {
        return (bool) $this->equipment_model_id;
    }

    public function validated(): array
    {
        $this->validate();

        return [
            'brand_id' => $this->brand_id,
            'name'     => trim($this->name),
            'active'   => $this->active,
        ];
    }
}
