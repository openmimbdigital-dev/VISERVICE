<?php

namespace App\Livewire\Forms\Admin\Businesses;

use App\Models\Business;
use App\Models\CustomTax;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class CustomTaxForm extends Form
{
    public ?int $custom_tax_id = null;

    public ?int $business_id = null;

    public string $name = '';

    public string $description = '';

    public string $percentage = '0';

    public bool $active = true;

    public function setCustomTax(CustomTax $tax): void
    {
        $this->custom_tax_id = $tax->id;
        $this->business_id   = $tax->business_id;
        $this->name          = $tax->name;
        $this->description   = $tax->description ?? '';
        $this->percentage    = (string) $tax->percentage;
        $this->active        = $tax->active;
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->custom_tax_id = null;
        $this->business_id   = null;
        $this->name          = '';
        $this->description   = '';
        $this->percentage    = '0';
        $this->active        = true;
    }

    public function isEditing(): bool
    {
        return (bool) $this->custom_tax_id;
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function resolvedBusinessId(): int
    {
        if ($this->isSuperAdmin()) {
            return (int) $this->business_id;
        }

        return (int) (auth()->user()->businessIds()[0] ?? 0);
    }

    public function getBusinesses(): Collection
    {
        if (! $this->isSuperAdmin()) {
            return collect();
        }

        return Business::query()
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function rules(): array
    {
        $business_id = $this->resolvedBusinessId();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('custom_taxes', 'name')
                    ->where(fn ($query) => $query
                        ->where('business_id', $business_id)
                        ->whereNull('deleted_at'))
                    ->ignore($this->custom_tax_id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'percentage'  => ['required', 'numeric', 'min:0', 'max:100'],
            'active'      => ['boolean'],
        ];

        if ($this->isSuperAdmin()) {
            $rules['business_id'] = ['required', 'integer', 'exists:businesses,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'business_id.required' => 'Debe seleccionar un negocio.',
            'name.required'        => 'El nombre es obligatorio.',
            'name.unique'          => 'Ya existe un impuesto con este nombre en el negocio.',
            'percentage.required'  => 'El porcentaje es obligatorio.',
            'percentage.numeric'   => 'El porcentaje debe ser un número.',
            'percentage.min'       => 'El porcentaje no puede ser negativo.',
            'percentage.max'       => 'El porcentaje no puede ser mayor a 100.',
        ];
    }

    public function validated(): array
    {
        $this->validate();

        return [
            'business_id' => $this->resolvedBusinessId(),
            'name'        => trim($this->name),
            'description' => trim($this->description) !== '' ? trim($this->description) : null,
            'percentage'  => round((float) $this->percentage, 2),
            'active'      => $this->active,
        ];
    }
}
