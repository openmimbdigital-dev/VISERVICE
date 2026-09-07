<?php

namespace App\Livewire\Forms\Admin\Workshop;

use App\Models\Brand;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\EquipmentModel;
use App\Models\EquipmentType;
use App\Support\DynamicAttributeValidator;
use App\Support\EquipmentTypeAttributeResolver;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EquipmentForm extends Form
{
    public const STEP_GENERAL = 1;

    public const STEP_IDENTIFICATION = 2;

    public const STEP_DETAILS = 3;

    public const TOTAL_STEPS = 3;

    public ?int $equipment_id = null;

    public ?int $business_id = null;

    public ?int $client_id = null;

    public ?int $brand_id = null;

    public ?int $model_id = null;

    public ?int $equipment_type_id = null;

    public string $name = '';

    public string $plate = '';

    public string $year = '';

    public bool $status = true;

    public string $notes = '';

    public int $persisted_step = 1;

    /** @var array<int, mixed> */
    public array $attribute_values = [];

    public function setEquipmentType(EquipmentType $equipment_type): void
    {
        $this->equipment_type_id = $equipment_type->id;
        $this->attribute_values  = [];
        $this->hydrateAttributeDefaults();
    }

    public function setEquipment(Equipment $equipment): void
    {
        $this->equipment_id      = $equipment->id;
        $this->business_id       = $equipment->business_id;
        $this->client_id         = $equipment->client_id;
        $this->brand_id          = $equipment->brand_id;
        $this->model_id          = $equipment->model_id;
        $this->equipment_type_id = $equipment->equipment_type_id;
        $this->name              = $equipment->name ?? '';
        $this->plate             = $equipment->plate;
        $this->year              = $equipment->year ? (string) $equipment->year : '';
        $this->status            = $equipment->status;
        $this->notes             = $equipment->notes ?? '';
        $this->persisted_step    = max(1, (int) $equipment->step);
        $this->attribute_values  = EquipmentTypeAttributeResolver::valuesForEquipment($equipment);
        $this->hydrateAttributeDefaults();
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function resolvedBusinessId(): ?int
    {
        if ($this->isSuperAdmin()) {
            return $this->business_id ? (int) $this->business_id : null;
        }

        return auth()->user()?->business_id ? (int) auth()->user()->business_id : null;
    }

    /** @return Collection<int, \App\Models\AttributeEquipmentType> */
    public function getAttributeLinks(): Collection
    {
        $business_id = $this->resolvedBusinessId();

        if (! $business_id || ! $this->equipment_type_id) {
            return collect();
        }

        return EquipmentTypeAttributeResolver::linksFor($this->equipment_type_id, $business_id);
    }

    public function hydrateAttributeDefaults(): void
    {
        foreach ($this->getAttributeLinks() as $link) {
            $attribute = $link->attribute;
            $id        = $attribute->id;

            if (array_key_exists($id, $this->attribute_values)) {
                continue;
            }

            $this->attribute_values[$id] = match ($attribute->type->value) {
                'checkbox' => [],
                'color'    => (string) ($attribute->options['default'] ?? '#6366f1'),
                default    => '',
            };
        }
    }

    public function rules(): array
    {
        return array_merge(
            $this->rulesForStep(self::STEP_GENERAL),
            $this->rulesForStep(self::STEP_IDENTIFICATION),
            $this->rulesForStep(self::STEP_DETAILS),
        );
    }

    /** @return array<string, mixed> */
    public function rulesForStep(int $step): array
    {
        return match ($step) {
            self::STEP_GENERAL => $this->generalRules(),
            self::STEP_IDENTIFICATION => $this->identificationRules(),
            self::STEP_DETAILS => $this->detailsRules(),
            default => [],
        };
    }

    public function firstStepWithErrors(array $errors): int
    {
        $step_fields = [
            self::STEP_GENERAL => ['business_id', 'client_id', 'name', 'plate', 'equipment_type_id'],
            self::STEP_IDENTIFICATION => ['brand_id', 'model_id', 'year'],
            self::STEP_DETAILS => ['status', 'notes', 'attribute_values'],
        ];

        $error_keys = collect(array_keys($errors))
            ->map(fn (string $key) => str_replace('form.', '', $key))
            ->all();

        foreach ($step_fields as $step => $fields) {
            foreach ($error_keys as $key) {
                foreach ($fields as $field) {
                    if ($key === $field || str_starts_with($key, $field . '.')) {
                        return $step;
                    }
                }
            }
        }

        return self::STEP_GENERAL;
    }

    public function messages(): array
    {
        $messages = [
            'business_id.required'       => 'Debes seleccionar un negocio.',
            'business_id.exists'         => 'El negocio seleccionado no es válido.',
            'client_id.required'         => 'Debes seleccionar un cliente.',
            'client_id.exists'           => 'El cliente seleccionado no es válido para este negocio.',
            'brand_id.required'          => 'Debes seleccionar una marca.',
            'brand_id.in'                => 'La marca seleccionada no es válida.',
            'model_id.required'          => 'Debes seleccionar un modelo.',
            'model_id.in'                => 'El modelo seleccionado no es válido.',
            'equipment_type_id.required' => 'El tipo de equipo es obligatorio.',
            'name.required'              => 'El nombre del equipo es obligatorio.',
            'name.max'                   => 'El nombre no puede superar 120 caracteres.',
            'plate.required'             => 'La placa es obligatoria.',
            'plate.max'                  => 'La placa no puede superar 20 caracteres.',
            'plate.unique'               => 'Ya existe un equipo con esta placa en el negocio.',
            'year.required'              => 'El año es obligatorio.',
            'year.integer'               => 'El año debe ser un número válido.',
            'year.min'                   => 'El año no es válido.',
            'year.max'                   => 'El año no puede ser futuro.',
        ];

        $links = $this->getAttributeLinks();

        if ($links->isNotEmpty()) {
            $attribute_validator = new DynamicAttributeValidator($links, ! $this->isEditing(), 'attribute_values');
            $messages            = array_merge($messages, $attribute_validator->messages());
        }

        return $messages;
    }

    public function isEditing(): bool
    {
        return (bool) $this->equipment_id;
    }

    public function isFlowComplete(): bool
    {
        return $this->equipment_id
            && $this->brand_id
            && $this->model_id
            && $this->year !== ''
            && $this->persisted_step >= self::TOTAL_STEPS;
    }

    public function getClients(): Collection
    {
        $business_id = $this->resolvedBusinessId();

        if (! $business_id) {
            return collect();
        }

        return Client::query()
            ->where('business_id', $business_id)
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getBrands(): Collection
    {
        $business_id = $this->resolvedBusinessId();

        if (! $business_id || ! $this->equipment_type_id) {
            return collect();
        }

        return Brand::query()
            ->active()
            ->where(function ($query) use ($business_id) {
                $query->where('general', true)
                    ->orWhere('business_id', $business_id);
            })
            ->where(function ($query) {
                $query->whereIn('id', function ($sub) {
                    $sub->select('brand_id')
                        ->from('brand_equipment_type')
                        ->where('equipment_type_id', $this->equipment_type_id);
                });

                if ($this->brand_id) {
                    $query->orWhere('id', $this->brand_id);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getModels(): Collection
    {
        if (! $this->brand_id) {
            return collect();
        }

        $business_id = $this->resolvedBusinessId();

        if (! $business_id) {
            return collect();
        }

        return EquipmentModel::query()
            ->active()
            ->where('brand_id', $this->brand_id)
            ->where(function ($query) use ($business_id) {
                $query->where('general', true)
                    ->orWhere('business_id', $business_id);
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function validated(): array
    {
        $this->validate();

        return $this->payload(self::TOTAL_STEPS);
    }

    /** @return array<string, mixed> */
    public function payload(int $step): array
    {
        $links            = $this->getAttributeLinks();
        $attribute_values = $links->isNotEmpty()
            ? (new DynamicAttributeValidator($links, ! $this->isEditing(), 'attribute_values'))
                ->normalize($this->attribute_values)
            : [];

        return [
            'client_id'         => (int) $this->client_id,
            'brand_id'          => $this->brand_id ? (int) $this->brand_id : null,
            'model_id'          => $this->model_id ? (int) $this->model_id : null,
            'equipment_type_id' => (int) $this->equipment_type_id,
            'name'              => trim($this->name),
            'plate'             => strtoupper(trim($this->plate)),
            'year'              => $this->year === '' ? null : (int) $this->year,
            'status'            => $this->status,
            'notes'             => trim($this->notes) ?: null,
            'attribute_values'  => $attribute_values,
            'step'              => $step,
            'final_step'        => self::TOTAL_STEPS,
        ];
    }

    /** @return array<string, mixed> */
    private function generalRules(): array
    {
        $business_id = $this->resolvedBusinessId();

        $client_rule = Rule::exists('clients', 'id')->where(
            fn ($query) => $query->where('business_id', $business_id)->whereNull('deleted_at')
        );

        $rules = [
            'client_id'         => ['required', 'integer', $client_rule],
            'equipment_type_id' => ['required', 'integer', 'exists:equipment_types,id'],
            'name'              => ['required', 'string', 'max:120'],
            'plate'             => [
                'required',
                'string',
                'max:20',
                Rule::unique('equipment', 'plate')
                    ->where(fn ($query) => $query->where('business_id', $business_id)->whereNull('deleted_at'))
                    ->ignore($this->equipment_id),
            ],
        ];

        if ($this->isSuperAdmin()) {
            $rules['business_id'] = ['required', 'integer', 'exists:businesses,id'];
        }

        return $rules;
    }

    /** @return array<string, mixed> */
    private function identificationRules(): array
    {
        return [
            'brand_id' => ['required', 'integer', Rule::in($this->getBrands()->pluck('id')->all())],
            'model_id' => ['required', 'integer', Rule::in($this->getModels()->pluck('id')->all())],
            'year'     => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
        ];
    }

    /** @return array<string, mixed> */
    private function detailsRules(): array
    {
        $rules = [
            'status' => ['boolean'],
            'notes'  => ['nullable', 'string'],
        ];

        $links = $this->getAttributeLinks();

        if ($links->isNotEmpty()) {
            $attribute_validator = new DynamicAttributeValidator($links, ! $this->isEditing(), 'attribute_values');
            $rules               = array_merge($rules, $attribute_validator->rules());
        }

        return $rules;
    }
}
