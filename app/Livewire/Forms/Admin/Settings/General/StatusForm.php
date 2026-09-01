<?php

namespace App\Livewire\Forms\Admin\Settings\General;

use App\Models\Status;
use Illuminate\Validation\Rule;
use Livewire\Form;

class StatusForm extends Form
{
    public ?int $status_id = null;

    public string $name = '';

    public string $label = '';

    public bool $active = true;

    /** @var list<string> */
    public array $type = [];

    /** @return array<string, string> */
    public static function moduleOptions(): array
    {
        return [
            'quotations' => 'Cotizaciones',
            'work_orders' => 'Órdenes de trabajo',
            'remissions' => 'Remisiones',
            'work_order_payments' => 'Pagos de OT',
        ];
    }

    public function setStatus(Status $status): void
    {
        $this->status_id = $status->id;
        $this->name = $status->name;
        $this->label = $status->label;
        $this->active = (bool) $status->active;
        $this->type = array_values(array_filter(
            (array) ($status->type ?? []),
            fn ($module) => is_string($module) && $module !== ''
        ));
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->status_id = null;
        $this->name = '';
        $this->label = '';
        $this->active = true;
        $this->type = [];
    }

    public function rules(): array
    {
        $name_rules = [
            'required',
            'string',
            'max:100',
            'regex:/^[a-z][a-z0-9_]*$/',
        ];

        if (! $this->isEditing()) {
            $name_rules[] = Rule::unique('statuses', 'name');
        }

        return [
            'name' => $name_rules,
            'label' => ['required', 'string', 'max:100'],
            'active' => ['boolean'],
            'type' => ['required', 'array', 'min:1'],
            'type.*' => ['string', Rule::in(array_keys(self::moduleOptions()))],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre técnico es obligatorio.',
            'name.regex' => 'Usa solo minúsculas, números y guion bajo (ej. in_progress).',
            'name.unique' => 'Ya existe un estado con ese nombre.',
            'name.max' => 'El nombre no puede superar 100 caracteres.',
            'label.required' => 'La etiqueta es obligatoria.',
            'label.max' => 'La etiqueta no puede superar 100 caracteres.',
            'type.required' => 'Selecciona al menos un módulo.',
            'type.min' => 'Selecciona al menos un módulo.',
            'type.*.in' => 'Uno de los módulos seleccionados no es válido.',
        ];
    }

    public function isEditing(): bool
    {
        return (bool) $this->status_id;
    }

    /** @return array{name: string, label: string, active: bool, type: list<string>} */
    public function validated(): array
    {
        $data = $this->validate();

        return [
            'name' => strtolower(trim($data['name'])),
            'label' => trim($data['label']),
            'active' => (bool) $data['active'],
            'type' => array_values(array_unique($data['type'])),
        ];
    }
}
