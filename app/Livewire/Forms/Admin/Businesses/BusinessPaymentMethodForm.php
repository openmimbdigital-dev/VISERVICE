<?php

namespace App\Livewire\Forms\Admin\Businesses;

use App\Models\Business;
use App\Models\BusinessPaymentMethod;
use App\Rules\NotConflictingWithGeneralCatalogName;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class BusinessPaymentMethodForm extends Form
{
    public ?int $payment_method_id = null;

    public ?int $business_id = null;

    public bool $general = false;

    public string $name = '';

    public bool $active = true;

    public bool $is_default = false;

    public int $sort_order = 0;

    public function setPaymentMethod(BusinessPaymentMethod $method): void
    {
        $this->payment_method_id = $method->id;
        $this->business_id       = $method->business_id;
        $this->general           = $method->general;
        $this->name              = $method->name;
        $this->active            = $method->active;
        $this->is_default        = $method->is_default;
        $this->sort_order        = $method->sort_order;
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->payment_method_id = null;
        $this->business_id       = null;
        $this->general           = false;
        $this->name              = '';
        $this->active            = true;
        $this->is_default        = false;
        $this->sort_order        = 0;
    }

    public function isEditing(): bool
    {
        return (bool) $this->payment_method_id;
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function resolvedBusinessId(): ?int
    {
        if ($this->isSuperAdmin()) {
            return $this->resolvedGeneral() ? null : ($this->business_id ? (int) $this->business_id : null);
        }

        return auth()->user()->businessIds()[0] ?? null;
    }

    public function resolvedGeneral(): bool
    {
        return $this->isSuperAdmin() && $this->general;
    }

    public function updatedGeneral(bool $value): void
    {
        if ($value) {
            $this->business_id = null;
        }
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
        $general     = $this->resolvedGeneral();

        $scope = function ($query) use ($business_id, $general) {
            $query->whereNull('deleted_at');

            if ($general) {
                $query->whereNull('business_id')->where('general', true);
            } else {
                $query->where('business_id', $business_id)->where('general', false);
            }
        };

        $rules = [
            'name' => [
                'required',
                'string',
                'max:100',
                new NotConflictingWithGeneralCatalogName(BusinessPaymentMethod::class, $this->payment_method_id),
                Rule::unique('business_payment_methods', 'name')->where($scope)->ignore($this->payment_method_id),
            ],
            'active'     => ['boolean'],
            'is_default' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:9999'],
        ];

        if ($this->isSuperAdmin()) {
            $rules['general'] = ['boolean'];

            if (! $general) {
                $rules['business_id'] = ['required', 'integer', 'exists:businesses,id'];
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'business_id.required' => 'Debe seleccionar un negocio.',
            'name.required'        => 'El nombre es obligatorio.',
            'name.unique'          => 'Ya existe un método de pago con este nombre.',
            'sort_order.min'       => 'El orden no puede ser negativo.',
        ];
    }

    public function validated(): array
    {
        $this->validate();

        return [
            'business_id'  => $this->resolvedBusinessId(),
            'general'      => $this->resolvedGeneral(),
            'name'         => trim($this->name),
            'active'       => $this->active,
            'is_default'   => $this->is_default,
            'sort_order'   => $this->sort_order,
        ];
    }
}
