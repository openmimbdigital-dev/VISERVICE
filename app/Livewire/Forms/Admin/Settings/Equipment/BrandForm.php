<?php

namespace App\Livewire\Forms\Admin\Settings\Equipment;

use App\Actions\Settings\Equipment\CreateOrUpdateBrandAction;
use App\Models\Brand;
use App\Rules\NotConflictingWithGeneralCatalogName;
use Illuminate\Validation\Rule;
use Livewire\Form;

class BrandForm extends Form
{
    public ?int $brand_id = null;

    public string $name   = '';
    public bool   $active = true;

    public function setBrand(Brand $brand): void
    {
        $this->brand_id = $brand->id;
        $this->name     = $brand->name;
        $this->active   = $brand->active;
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
            'active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max'      => 'El nombre no puede superar 100 caracteres.',
            'name.unique'   => 'Ya existe una marca con este nombre.',
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
            'name'   => trim($this->name),
            'active' => $this->active,
        ];
    }
}
