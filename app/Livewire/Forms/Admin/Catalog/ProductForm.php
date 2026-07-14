<?php

namespace App\Livewire\Forms\Admin\Catalog;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ProductForm extends Form
{
    public ?int $product_id = null;

    public ?int $business_id = null;

    public ?int $product_type_id = null;

    public ?int $product_category_id = null;

    public ?int $unit_id = null;

    public ?int $brand_id = null;

    public string $code = '';

    public string $name = '';

    public string $description = '';

    public string $cost_price = '0';

    public string $sale_price = '0';

    public ?int $tax_id = null;

    public bool $status = true;

    public function setProduct(Product $product): void
    {
        $this->product_id          = $product->id;
        $this->business_id         = $product->business_id;
        $this->product_type_id     = $product->product_type_id;
        $this->product_category_id = $product->product_category_id;
        $this->unit_id             = $product->unit_id;
        $this->brand_id            = $product->brand_id;
        $this->code                = $product->code;
        $this->name                = $product->name;
        $this->description         = $product->description ?? '';
        $this->cost_price          = (string) $product->cost_price;
        $this->sale_price          = (string) $product->sale_price;
        $this->tax_id              = $product->tax_id;
        $this->status              = $product->status;
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->product_id          = null;
        $this->business_id         = null;
        $this->product_type_id     = null;
        $this->product_category_id = null;
        $this->unit_id             = null;
        $this->brand_id            = null;
        $this->code                = '';
        $this->name                = '';
        $this->description         = '';
        $this->cost_price          = '0';
        $this->sale_price          = '0';
        $this->tax_id              = null;
        $this->status              = true;
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

        return (int) auth()->user()->business_id;
    }

    public function getProductTypes(): Collection
    {
        return ProductType::query()
            ->visibleToUser()
            ->orderBy('name')
            ->get(['id', 'name', 'active']);
    }

    public function getProductCategories(): Collection
    {
        return ProductCategory::query()
            ->visibleToUser()
            ->orderBy('name')
            ->get(['id', 'name', 'inventory', 'active']);
    }

    public function getUnits(): Collection
    {
        return Unit::query()
            ->visibleToUser()
            ->orderBy('name')
            ->get(['id', 'name', 'symbol', 'active']);
    }

    public function getBrands(): Collection
    {
        $query = Brand::query()
            ->visibleToUser()
            ->forProductsCatalog()
            ->orderBy('name');

        if ($this->product_category_id) {
            $query->whereHas('productCategories', fn ($category_query) => $category_query->whereKey($this->product_category_id));
        }

        return $query->get(['id', 'name', 'active']);
    }

    public function rules(): array
    {
        $business_id = $this->isSuperAdmin()
            ? $this->business_id
            : auth()->user()?->business_id;

        $product_type_ids     = $this->getProductTypes()->pluck('id')->all();
        $product_category_ids = $this->getProductCategories()->pluck('id')->all();
        $unit_ids             = $this->getUnits()->pluck('id')->all();
        $brand_ids            = $this->getBrands()->pluck('id')->all();

        $rules = [
            'product_type_id' => ['required', 'integer', Rule::in($product_type_ids)],
            'product_category_id' => ['required', 'integer', Rule::in($product_category_ids)],
            'unit_id' => ['required', 'integer', Rule::in($unit_ids)],
            'brand_id' => ['nullable', 'integer', Rule::in($brand_ids)],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'code')
                    ->where(fn ($query) => $query->where('business_id', $business_id)->whereNull('deleted_at'))
                    ->ignore($this->product_id),
            ],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'tax_id' => ['nullable', 'integer'],
            'status' => ['boolean'],
        ];

        if ($this->isSuperAdmin()) {
            $rules['business_id'] = ['required', 'integer', 'exists:businesses,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'business_id.required'         => 'Debe seleccionar un comercio.',
            'product_type_id.required'     => 'Debe seleccionar un tipo de producto.',
            'product_type_id.in'           => 'El tipo de producto seleccionado no es válido.',
            'product_category_id.required' => 'Debe seleccionar una categoría.',
            'product_category_id.in'       => 'La categoría seleccionada no es válida.',
            'unit_id.required'             => 'Debe seleccionar una unidad de medida.',
            'unit_id.in'                   => 'La unidad seleccionada no es válida.',
            'brand_id.in'                  => 'La marca seleccionada no es válida.',
            'code.required'                => 'El código es obligatorio.',
            'code.max'                     => 'El código no puede superar 50 caracteres.',
            'code.unique'                  => 'Ya existe un producto con este código en el comercio.',
            'name.required'                => 'El nombre es obligatorio.',
            'name.max'                     => 'El nombre no puede superar 200 caracteres.',
            'cost_price.required'          => 'El precio de costo es obligatorio.',
            'cost_price.numeric'           => 'El precio de costo debe ser numérico.',
            'cost_price.min'               => 'El precio de costo no puede ser negativo.',
            'sale_price.required'          => 'El precio de venta es obligatorio.',
            'sale_price.numeric'           => 'El precio de venta debe ser numérico.',
            'sale_price.min'               => 'El precio de venta no puede ser negativo.',
        ];
    }

    public function isEditing(): bool
    {
        return (bool) $this->product_id;
    }

    public function validated(): array
    {
        $this->validate();

        $data = [
            'product_type_id'     => (int) $this->product_type_id,
            'product_category_id' => (int) $this->product_category_id,
            'unit_id'             => (int) $this->unit_id,
            'brand_id'            => $this->brand_id ? (int) $this->brand_id : null,
            'code'                => trim($this->code),
            'name'                => trim($this->name),
            'description'         => trim($this->description) !== '' ? trim($this->description) : null,
            'cost_price'          => (float) $this->cost_price,
            'sale_price'          => (float) $this->sale_price,
            'tax_id'              => $this->tax_id,
            'status'              => $this->status,
        ];

        if ($this->isSuperAdmin()) {
            $data['business_id'] = (int) $this->business_id;
        }

        return $data;
    }
}
