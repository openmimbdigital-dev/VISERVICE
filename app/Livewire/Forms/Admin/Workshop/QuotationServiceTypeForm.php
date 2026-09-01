<?php

namespace App\Livewire\Forms\Admin\Workshop;

use App\Models\QuotationServiceType;
use App\Rules\NotConflictingWithGeneralCatalogName;
use App\Support\CatalogLabelNormalizer;
use Illuminate\Validation\Rule;
use Livewire\Form;

class QuotationServiceTypeForm extends Form
{
    public ?int $service_type_id = null;

    public string $name = '';

    public bool $active = true;

    public function setServiceType(QuotationServiceType $service_type): void
    {
        $this->service_type_id = $service_type->id;
        $this->name            = $service_type->name;
        $this->active          = $service_type->active;
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->service_type_id = null;
        $this->name            = '';
        $this->active          = true;
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function resolvedBusinessId(): ?int
    {
        return $this->isSuperAdmin() ? null : (auth()->user()->businessIds()[0] ?? null);
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

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                new NotConflictingWithGeneralCatalogName(QuotationServiceType::class, $this->service_type_id),
                Rule::unique('quotation_service_types', 'name')->where($scope)->ignore($this->service_type_id),
            ],
            'active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un tipo de servicio con este nombre.',
        ];
    }

    public function isEditing(): bool
    {
        return (bool) $this->service_type_id;
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
