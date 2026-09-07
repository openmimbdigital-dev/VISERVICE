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
    public const STEP_GENERAL = 1;

    public const STEP_CLASSIFICATION = 2;

    public const STEP_PRICING = 3;

    public const TOTAL_STEPS = 3;

    public ?int $product_id = null;

    public ?int $business_id = null;

    public ?int $product_type_id = null;

    public ?int $product_category_id = null;

    public ?int $unit_id = null;

    public ?int $brand_id = null;

    public string $sku = '';

    public string $barcode = '';

    public string $name = '';

    public string $description = '';

    public string $cost_price = '';

    public string $profit_percentage = '';

    public string $sale_price = '';

    public bool $status = true;

    public function setProduct(Product $product): void
    {
        $this->product_id          = $product->id;
        $this->business_id         = $product->business_id;
        $this->product_type_id     = $product->product_type_id;
        $this->product_category_id = $product->product_category_id;
        $this->unit_id             = $product->unit_id;
        $this->brand_id            = $product->brand_id;
        $this->sku                 = $product->sku;
        $this->barcode             = $product->barcode ?? '';
        $this->name                = $product->name;
        $this->description         = $product->description ?? '';
        $this->cost_price          = $product->cost_price !== null ? (string) $product->cost_price : '';
        $this->profit_percentage   = $product->profit_percentage !== null ? (string) $product->profit_percentage : '';
        $this->sale_price          = $product->sale_price !== null ? (string) $product->sale_price : '';
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
        $this->sku                 = '';
        $this->barcode             = '';
        $this->name                = '';
        $this->description         = '';
        $this->cost_price          = '';
        $this->profit_percentage   = '';
        $this->sale_price          = '';
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
        return array_merge(
            $this->rulesForStep(self::STEP_GENERAL),
            $this->rulesForStep(self::STEP_CLASSIFICATION),
            $this->rulesForStep(self::STEP_PRICING),
        );
    }

    public function rulesForStep(int $step): array
    {
        $business_id = $this->isSuperAdmin()
            ? $this->business_id
            : auth()->user()?->business_id;

        return match ($step) {
            self::STEP_GENERAL => $this->generalRules($business_id),
            self::STEP_CLASSIFICATION => $this->classificationRules(),
            self::STEP_PRICING => [
                'cost_price'         => ['required', 'numeric', 'min:0'],
                'profit_percentage'  => ['required', 'numeric', 'min:0'],
                'sale_price'         => ['required', 'numeric', 'min:0'],
                'status'             => ['boolean'],
            ],
            default => [],
        };
    }

    public function firstStepWithErrors(array $errors): int
    {
        $step_fields = [
            self::STEP_GENERAL         => ['business_id', 'sku', 'barcode', 'name', 'description'],
            self::STEP_CLASSIFICATION  => ['product_type_id', 'product_category_id', 'unit_id', 'brand_id'],
            self::STEP_PRICING         => ['cost_price', 'profit_percentage', 'sale_price', 'status'],
        ];

        $error_keys = collect(array_keys($errors))
            ->map(fn (string $key) => str_replace('form.', '', $key))
            ->all();

        foreach ($step_fields as $step => $fields) {
            if (array_intersect($error_keys, $fields) !== []) {
                return $step;
            }
        }

        return self::STEP_GENERAL;
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
            'sku.required'                 => 'El SKU es obligatorio.',
            'sku.max'                      => 'El SKU no puede superar 50 caracteres.',
            'sku.unique'                   => 'Ya existe un producto con este SKU o código de barras en el comercio.',
            'barcode.required'             => 'El código de barras es obligatorio.',
            'barcode.max'                  => 'El código de barras no puede superar 64 caracteres.',
            'barcode.unique'               => 'Ya existe un producto con este SKU o código de barras en el comercio.',
            'name.required'                => 'El nombre es obligatorio.',
            'name.max'                     => 'El nombre no puede superar 200 caracteres.',
            'cost_price.required'          => 'El precio de costo es obligatorio.',
            'cost_price.numeric'           => 'El precio de costo debe ser numérico.',
            'cost_price.min'               => 'El precio de costo no puede ser negativo.',
            'profit_percentage.required'   => 'El porcentaje de ganancia es obligatorio.',
            'profit_percentage.numeric'    => 'El porcentaje de ganancia debe ser numérico.',
            'profit_percentage.min'        => 'El porcentaje de ganancia no puede ser negativo.',
            'sale_price.required'          => 'El precio de venta es obligatorio.',
            'sale_price.numeric'           => 'El precio de venta debe ser numérico.',
            'sale_price.min'               => 'El precio de venta no puede ser negativo.',
        ];
    }

    public function isEditing(): bool
    {
        return (bool) $this->product_id;
    }

    public function isFlowComplete(): bool
    {
        return $this->product_id
            && $this->product_type_id
            && $this->product_category_id
            && $this->unit_id
            && $this->cost_price !== ''
            && $this->profit_percentage !== ''
            && $this->sale_price !== '';
    }

    public function validated(): array
    {
        $this->normalizeIdentifiers();
        $this->recalculateSalePrice();
        $this->validate();

        return $this->payload(self::TOTAL_STEPS);
    }

    public function recalculateSalePrice(): void
    {
        if ($this->cost_price === '' || $this->profit_percentage === '' || ! is_numeric($this->cost_price) || ! is_numeric($this->profit_percentage)) {
            return;
        }

        $cost    = (float) $this->cost_price;
        $percent = (float) $this->profit_percentage;
        $sale    = round($cost * (1 + ($percent / 100)), 2);

        $this->sale_price = number_format($sale, 2, '.', '');
    }

    public function profitMarginAmount(): ?float
    {
        if ($this->cost_price === '' || $this->profit_percentage === '' || ! is_numeric($this->cost_price) || ! is_numeric($this->profit_percentage)) {
            return null;
        }

        return round((float) $this->cost_price * ((float) $this->profit_percentage / 100), 2);
    }

    /** @return array<string, mixed> */
    public function payload(int $step): array
    {
        $this->normalizeIdentifiers();

        $data = [
            'product_type_id'     => $this->product_type_id ? (int) $this->product_type_id : null,
            'product_category_id' => $this->product_category_id ? (int) $this->product_category_id : null,
            'unit_id'             => $this->unit_id ? (int) $this->unit_id : null,
            'brand_id'            => $this->brand_id ? (int) $this->brand_id : null,
            'sku'                 => trim($this->sku),
            'barcode'             => trim($this->barcode) !== '' ? trim($this->barcode) : null,
            'name'                => trim($this->name),
            'description'         => trim($this->description) !== '' ? trim($this->description) : null,
            'cost_price'          => $this->cost_price === '' ? null : (float) $this->cost_price,
            'profit_percentage'   => $this->profit_percentage === '' ? null : (float) $this->profit_percentage,
            'sale_price'          => $this->sale_price === '' ? null : (float) $this->sale_price,
            'status'              => $this->status,
            'step'                => $step,
            'final_step'          => self::TOTAL_STEPS,
        ];

        if ($this->isSuperAdmin()) {
            $data['business_id'] = (int) $this->business_id;
        }

        return $data;
    }

    public function normalizeIdentifiers(): void
    {
        $this->sku     = trim($this->sku);
        $this->barcode = trim($this->barcode);
    }

    /** @return array<string, mixed> */
    private function generalRules(mixed $business_id): array
    {
        $rules = [
            'sku' => [
                'required',
                'string',
                'max:50',
                $this->uniqueIdentifierRule('sku', $business_id),
                $this->uniqueIdentifierRule('barcode', $business_id),
            ],
            'barcode' => [
                'required',
                'string',
                'max:64',
                $this->uniqueIdentifierRule('barcode', $business_id),
                $this->uniqueIdentifierRule('sku', $business_id),
            ],
            'name'        => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];

        if ($this->isSuperAdmin()) {
            $rules['business_id'] = ['required', 'integer', 'exists:businesses,id'];
        }

        return $rules;
    }

    private function uniqueIdentifierRule(string $column, mixed $business_id): \Illuminate\Validation\Rules\Unique
    {
        return Rule::unique('products', $column)
            ->where(fn ($query) => $query->where('business_id', $business_id)->whereNull('deleted_at'))
            ->ignore($this->product_id);
    }

    /** @return array<string, mixed> */
    private function classificationRules(): array
    {
        $product_type_ids     = $this->getProductTypes()->pluck('id')->all();
        $product_category_ids = $this->getProductCategories()->pluck('id')->all();
        $unit_ids             = $this->getUnits()->pluck('id')->all();
        $brand_ids            = $this->getBrands()->pluck('id')->all();

        return [
            'product_type_id'     => ['required', 'integer', Rule::in($product_type_ids)],
            'product_category_id' => ['required', 'integer', Rule::in($product_category_ids)],
            'unit_id'             => ['required', 'integer', Rule::in($unit_ids)],
            'brand_id'            => ['nullable', 'integer', Rule::in($brand_ids)],
        ];
    }
}
