<?php

namespace App\Livewire\Forms\Admin\Settings\Catalog;

use App\Models\Brand;
use App\Models\ProductCategory;
use App\Rules\NotConflictingWithGeneralCatalogName;
use App\Support\CatalogLabelNormalizer;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class CatalogBrandForm extends Form
{
    public ?int $brand_id = null;

    public string $name = '';

    public bool $active = true;

    /** @var array<int> */
    public array $product_category_ids = [];

    public function setBrand(Brand $brand): void
    {
        $brand->load('productCategories');

        $this->brand_id             = $brand->id;
        $this->name                 = $brand->name;
        $this->active               = $brand->active;
        $this->product_category_ids = $brand->productCategories
            ->where('inventory', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->brand_id             = null;
        $this->name                 = '';
        $this->active               = true;
        $this->product_category_ids = [];
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

    public function getAvailableProductCategories(): Collection
    {
        $query = ProductCategory::query()
            ->where('inventory', true)
            ->orderBy('name');

        if (! $this->isSuperAdmin()) {
            $query->visibleToUser();
        }

        return $query->get(['id', 'name', 'active', 'inventory']);
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

        $available_product_category_ids = $this->getAvailableProductCategories()->pluck('id')->all();

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

                    $label = CatalogLabelNormalizer::fromName((string) $value);

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
            'active'                     => ['boolean'],
            'product_category_ids'       => ['required', 'array', 'min:1'],
            'product_category_ids.*'     => ['integer', Rule::in($available_product_category_ids)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                      => 'El nombre es obligatorio.',
            'name.max'                           => 'El nombre no puede superar 100 caracteres.',
            'name.unique'                        => 'Ya existe una marca con este nombre.',
            'product_category_ids.required'      => 'Debe seleccionar al menos una categoría.',
            'product_category_ids.min'           => 'Debe seleccionar al menos una categoría.',
            'product_category_ids.*.in'          => 'Una de las categorías seleccionadas no es válida.',
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
            'name'                => trim($this->name),
            'active'              => $this->active,
            'product_category_ids' => array_map('intval', $this->product_category_ids),
        ];
    }
}
