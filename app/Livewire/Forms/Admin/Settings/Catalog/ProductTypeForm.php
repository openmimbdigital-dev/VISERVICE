<?php

namespace App\Livewire\Forms\Admin\Settings\Catalog;

use App\Models\ProductType;
use App\Rules\NotConflictingWithGeneralCatalogName;
use App\Support\CatalogLabelNormalizer;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ProductTypeForm extends Form
{
    public ?int $product_type_id = null;

    public string $name = '';

    public bool $active = true;

    public function setProductType(ProductType $product_type): void
    {
        $this->product_type_id = $product_type->id;
        $this->name            = $product_type->name;
        $this->active          = $product_type->active;
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->product_type_id = null;
        $this->name            = '';
        $this->active          = true;
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

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                new NotConflictingWithGeneralCatalogName(ProductType::class, $this->product_type_id),
                Rule::unique('product_types', 'name')->where($scope)->ignore($this->product_type_id),
                function (string $attribute, mixed $value, \Closure $fail) use ($scope) {
                    $name = mb_strtolower(trim((string) $value));

                    if ($name === '') {
                        $fail('El nombre es obligatorio.');

                        return;
                    }

                    $query = ProductType::query()->whereRaw('LOWER(TRIM(name)) = ?', [$name]);
                    $scope($query);

                    if ($this->product_type_id) {
                        $query->where('id', '!=', $this->product_type_id);
                    }

                    if ($query->exists()) {
                        $fail('Ya existe un tipo con este nombre.');

                        return;
                    }

                    $label = CatalogLabelNormalizer::fromName((string) $value);

                    if ($label === '') {
                        $fail('El nombre debe contener al menos una letra o número.');

                        return;
                    }

                    $query = ProductType::query()->where('label', $label);
                    $scope($query);

                    if ($this->product_type_id) {
                        $query->where('id', '!=', $this->product_type_id);
                    }

                    if ($query->exists()) {
                        $fail('Ya existe un tipo con un nombre equivalente.');
                    }
                },
            ],
            'active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max'      => 'El nombre no puede superar 100 caracteres.',
            'name.unique'   => 'Ya existe un tipo con este nombre.',
        ];
    }

    public function isEditing(): bool
    {
        return (bool) $this->product_type_id;
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
