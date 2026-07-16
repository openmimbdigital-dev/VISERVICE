<?php

namespace App\Livewire\Forms\Admin\Workshop;

use App\Models\GeneralConfig;
use App\Support\CurrentBusiness;
use Illuminate\Validation\Rule;
use Livewire\Form;

class AssociatedDocumentOtForm extends Form
{
    public ?int $config_id = null;

    public ?int $business_id = null;

    public string $value = '';

    public function setConfig(GeneralConfig $config): void
    {
        $this->config_id   = $config->id;
        $this->business_id = (int) $config->business_id;
        $this->value       = (string) $config->value;
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->config_id   = null;
        $this->business_id = null;
        $this->value       = '';
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function isEditing(): bool
    {
        return (bool) $this->config_id;
    }

    public function resolvedBusinessId(): int
    {
        if ($this->isSuperAdmin()) {
            return (int) ($this->business_id
                ?: CurrentBusiness::id()
                ?: auth()->user()?->business_id);
        }

        return (int) (CurrentBusiness::id() ?? auth()->user()?->business_id);
    }

    public function rules(): array
    {
        $business_id = $this->resolvedBusinessId();

        return [
            'business_id' => $this->isSuperAdmin()
                ? ['required', 'integer', Rule::exists('businesses', 'id')->whereNull('deleted_at')]
                : ['nullable'],
            'value' => [
                'required',
                'string',
                'max:150',
                function (string $attribute, mixed $value, \Closure $fail) use ($business_id) {
                    $label = GeneralConfig::makeLabelFromValue((string) $value);

                    $exists = GeneralConfig::query()
                        ->where('business_id', $business_id)
                        ->where('key', GeneralConfig::KEY_ASSOCIATE_DOCUMENT_OT)
                        ->where('label', $label)
                        ->when($this->config_id, fn ($q) => $q->where('id', '!=', $this->config_id))
                        ->exists();

                    if ($exists) {
                        $fail('Ya existe un documento asociado con este nombre.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'business_id.required' => 'El negocio es obligatorio.',
            'value.required'       => 'El nombre del documento es obligatorio.',
            'value.unique'         => 'Ya existe un documento asociado con este nombre.',
            'value.max'            => 'El nombre no puede superar 150 caracteres.',
        ];
    }

    /** @return array{value: string, label: string, business_id: int} */
    public function validated(): array
    {
        if ($this->isSuperAdmin() && ! $this->business_id) {
            $this->business_id = CurrentBusiness::id() ?? auth()->user()?->business_id;
        }

        $this->validate();

        $value = trim($this->value);

        return [
            'value'       => $value,
            'label'       => GeneralConfig::makeLabelFromValue($value),
            'business_id' => $this->resolvedBusinessId(),
        ];
    }
}
