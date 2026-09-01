<?php

namespace App\Livewire\Forms\Admin\Settings\Equipment;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\EquipmentType;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class AttributeForm extends Form
{
    public ?int $attribute_id = null;

    public bool $general = false;

    /** @var array<int> */
    public array $business_ids = [];

    public string $name = '';

    public string $type = '';

    /** @var array<int, array{label: string, value: string}> */
    public array $options = [];

    /** @var array<int> */
    public array $equipment_types = [];

    public bool $required = false;

    public bool $nullable_creation = false;

    public ?float $min_value = null;

    public ?float $max_value = null;

    public string $default_color = '#6366f1';

    public function setAttribute(Attribute $attribute): void
    {
        $attribute->load(['attributeProductTypes', 'businesses']);

        $this->attribute_id      = $attribute->id;
        $this->general             = $attribute->general;
        $this->business_ids        = $attribute->businesses->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->name                = $attribute->name;
        $this->type                = $attribute->type->value;
        $this->required            = $attribute->required;
        $this->nullable_creation   = $attribute->nullable_creation;
        $this->min_value           = $attribute->min_value;
        $this->max_value           = $attribute->max_value;
        $this->options             = $attribute->options ?? [];
        $this->default_color       = $attribute->type === AttributeType::COLOR
            ? (string) ($attribute->options['default'] ?? '#6366f1')
            : '#6366f1';
        $this->equipment_types     = $attribute->attributeProductTypes
            ->where('model_type', EquipmentType::class)
            ->pluck('model_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->attribute_id        = null;
        $this->general             = false;
        $this->business_ids        = [];
        $this->name                = '';
        $this->type                = '';
        $this->options             = [];
        $this->equipment_types     = [];
        $this->required            = false;
        $this->nullable_creation   = false;
        $this->min_value           = null;
        $this->max_value           = null;
        $this->default_color       = '#6366f1';
    }

    public function isEditing(): bool
    {
        return (bool) $this->attribute_id;
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function rules(): array
    {
        $visible_type_ids = $this->getEquipmentTypes()->pluck('id')->all();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attributes', 'name')
                    ->where(fn ($query) => $query->where('type', $this->type)->whereNull('deleted_at'))
                    ->ignore($this->attribute_id),
            ],
            'type' => ['required', Rule::enum(AttributeType::class)],
            'options' => ['nullable', 'array'],
            'equipment_types' => ['required', 'array', 'min:1'],
            'equipment_types.*' => ['integer', Rule::in($visible_type_ids)],
            'required' => ['boolean'],
            'nullable_creation' => ['boolean'],
            'min_value' => ['nullable', 'numeric', 'required_with:max_value'],
            'max_value' => ['nullable', 'numeric', 'gte:min_value', 'required_with:min_value'],
            'default_color' => [
                'required_if:type,color',
                'nullable',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
        ];

        if ($this->isSuperAdmin()) {
            $rules['general'] = ['boolean'];

            if (! $this->general) {
                $rules['business_ids']   = ['required', 'array', 'min:1'];
                $rules['business_ids.*'] = ['integer', 'exists:businesses,id'];
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required'              => 'El nombre es obligatorio.',
            'name.max'                   => 'El nombre no puede superar 255 caracteres.',
            'name.unique'                => 'Ya existe un atributo con este nombre y tipo.',
            'type.required'              => 'El tipo es obligatorio.',
            'type.enum'                  => 'El tipo seleccionado no es válido.',
            'equipment_types.required'   => 'Debe seleccionar al menos un tipo de equipo.',
            'equipment_types.min'        => 'Debe seleccionar al menos un tipo de equipo.',
            'equipment_types.*.in'       => 'Uno de los tipos de equipo seleccionados no es válido.',
            'min_value.required_with'    => 'Si ingresa un valor máximo, también debe ingresar un valor mínimo.',
            'max_value.gte'              => 'El valor máximo debe ser mayor o igual al valor mínimo.',
            'max_value.required_with'    => 'Si ingresa un valor mínimo, también debe ingresar un valor máximo.',
            'business_ids.required'      => 'Debe seleccionar al menos un comercio.',
            'business_ids.min'           => 'Debe seleccionar al menos un comercio.',
            'business_ids.*.exists'        => 'Uno de los comercios seleccionados no es válido.',
            'default_color.required_if'    => 'Debe seleccionar un color predeterminado.',
            'default_color.regex'          => 'El color debe ser un código hexadecimal válido (ej. #6366f1).',
        ];
    }

    public function validated(): array
    {
        $this->validate();

        return [
            'name'              => trim($this->name),
            'type'              => $this->type,
            'options'           => $this->type === AttributeType::COLOR->value
                ? ['default' => $this->default_color]
                : $this->options,
            'equipment_types'   => array_map('intval', $this->equipment_types),
            'required'          => $this->required,
            'nullable_creation' => $this->nullable_creation,
            'min_value'         => $this->type === AttributeType::NUMBER->value ? $this->min_value : null,
            'max_value'         => $this->type === AttributeType::NUMBER->value ? $this->max_value : null,
            'general'           => $this->isSuperAdmin() ? $this->general : false,
            'business_ids'      => $this->isSuperAdmin() && ! $this->general
                ? array_map('intval', $this->business_ids)
                : [],
        ];
    }

    public function getEquipmentTypes(): Collection
    {
        $query = EquipmentType::query()
            ->where('active', true)
            ->orderBy('name');

        if ($this->isSuperAdmin()) {
            if ($this->general) {
                return $query->where('general', true)
                    ->where(function ($q) {
                        $q->whereDoesntHave('businesses')
                            ->orWhereHas('businesses');
                    })
                    ->get(['id', 'name', 'general']);
            }

            if (empty($this->business_ids)) {
                return collect();
            }

            $business_ids = array_map('intval', $this->business_ids);

            return $query->where(function ($q) use ($business_ids) {
                $q->where(function ($q2) {
                    $q2->where('general', true)->whereDoesntHave('businesses');
                })
                    ->orWhereHas('businesses', fn ($bq) => $bq->whereIn('businesses.id', $business_ids))
                    ->orWhereIn('business_id', $business_ids);
            })->get(['id', 'name', 'general']);
        }

        return $query->visibleToUser()->get(['id', 'name', 'general']);
    }

    public function addOption(): void
    {
        $this->options[] = ['label' => '', 'value' => ''];
    }

    public function removeOption(int $index): void
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }
}
