<?php

namespace App\Livewire\Forms\Admin\Catalog;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\Unit;
use App\Support\ProductPricing;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ProductForm extends Form
{
    public const STEP_GENERAL = 1;

    public const STEP_CLASSIFICATION = 2;

    public const STEP_PRICING = 3;

    public const STEP_IMAGES = 4;

    public const TOTAL_STEPS = 4;

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

    public string $profit_margin = '';

    public string $sale_price = '';

    /** Last pricing field the user edited: percentage, margin or sale. */
    public string $pricing_source = '';

    public string $discount_type = '';

    public string $discount_value = '';

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
        $this->profit_margin       = $product->profitMarginAmount() !== null
            ? $this->formatDecimal($product->profitMarginAmount())
            : '';
        $this->pricing_source      = '';
        $this->discount_type       = (string) ($product->discount_type ?? '');
        $this->discount_value      = $product->discount_value !== null ? (string) $product->discount_value : '';
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
        $this->profit_margin       = '';
        $this->sale_price          = '';
        $this->pricing_source      = '';
        $this->discount_type       = '';
        $this->discount_value      = '';
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
            self::STEP_PRICING => $this->pricingRules(),
            default => [],
        };
    }

    public function firstStepWithErrors(array $errors): int
    {
        $step_fields = [
            self::STEP_GENERAL         => ['business_id', 'sku', 'barcode', 'name', 'description'],
            self::STEP_CLASSIFICATION  => ['product_type_id', 'product_category_id', 'unit_id', 'brand_id'],
            self::STEP_PRICING         => ['cost_price', 'profit_percentage', 'profit_margin', 'sale_price', 'discount_type', 'discount_value', 'status'],
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
            'cost_price.numeric'           => 'El precio de costo debe ser numérico.',
            'cost_price.min'               => 'El precio de costo no puede ser negativo.',
            'profit_percentage.numeric'    => 'El porcentaje de ganancia debe ser numérico.',
            'profit_margin.numeric'        => 'El margen de ganancia debe ser numérico.',
            'discount_type.in'             => 'La forma del descuento no es válida.',
            'discount_value.required'      => 'Indica el valor del descuento o deja la opción «Sin descuento».',
            'discount_value.numeric'       => 'El descuento debe ser numérico.',
            'discount_value.gt'            => 'El descuento debe ser mayor a cero.',
            'discount_value.max'           => 'El descuento no puede superar el 100%.',
            'discount_value.lt'            => 'El descuento debe ser menor al precio de venta.',
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
            && $this->sale_price !== '';
    }

    public function validated(): array
    {
        $this->normalizeIdentifiers();
        $this->syncPricingBeforeValidate();
        $this->validate();

        return $this->payload(self::TOTAL_STEPS);
    }

    public function applyCostChange(): void
    {
        if ($this->forgetPricingIfCleared($this->cost_price)) {
            return;
        }

        match ($this->resolvedPricingSource()) {
            'percentage' => $this->applyFromPercentage(),
            'margin'     => $this->applyFromMargin(),
            'sale'       => $this->applyFromSale(),
            default      => $this->fillMissingDerivedPricing(),
        };
    }

    public function applyFromPercentage(): void
    {
        if ($this->forgetPricingIfCleared($this->profit_percentage)) {
            return;
        }

        $this->pricing_source = 'percentage';

        $cost    = $this->parseDecimal($this->cost_price);
        $percent = $this->parseDecimal($this->profit_percentage);

        if ($cost === null || $percent === null) {
            return;
        }

        $pricing = ProductPricing::fromCostAndPercentage($cost, $percent);

        $this->profit_margin = $this->formatDecimal($pricing['profit_margin']);
        $this->sale_price    = $this->formatDecimal($pricing['sale_price']);
    }

    public function applyFromMargin(): void
    {
        if ($this->forgetPricingIfCleared($this->profit_margin)) {
            return;
        }

        $this->pricing_source = 'margin';

        $cost   = $this->parseDecimal($this->cost_price);
        $margin = $this->parseDecimal($this->profit_margin);

        if ($cost === null || $margin === null) {
            return;
        }

        $pricing = ProductPricing::fromCostAndMargin($cost, $margin);

        $this->sale_price = $this->formatDecimal($pricing['sale_price']);
        $this->profit_percentage = $pricing['profit_percentage'] === null
            ? ''
            : $this->formatDecimal($pricing['profit_percentage']);
    }

    public function applyFromSale(): void
    {
        if ($this->forgetPricingIfCleared($this->sale_price)) {
            return;
        }

        $this->pricing_source = 'sale';

        $cost = $this->parseDecimal($this->cost_price);
        $sale = $this->parseDecimal($this->sale_price);

        if ($cost === null || $sale === null) {
            return;
        }

        $pricing = ProductPricing::fromCostAndSale($cost, $sale);

        $this->profit_margin = $this->formatDecimal($pricing['profit_margin']);
        $this->profit_percentage = $pricing['profit_percentage'] === null
            ? ''
            : $this->formatDecimal($pricing['profit_percentage']);
    }

    public function syncPricingBeforeValidate(): void
    {
        if ($this->parseDecimal($this->cost_price) === null) {
            return;
        }

        match ($this->pricing_source) {
            'percentage' => $this->applyFromPercentage(),
            'margin'     => $this->applyFromMargin(),
            'sale'       => $this->applyFromSale(),
            default      => $this->fillMissingDerivedPricing(),
        };
    }

    public function profitMarginAmount(): ?float
    {
        return $this->parseDecimal($this->profit_margin);
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
            'discount_type'       => $this->discount_type !== '' ? $this->discount_type : null,
            'discount_value'      => $this->discount_type !== '' && $this->discount_value !== ''
                ? (float) $this->discount_value
                : null,
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

    /**
     * Reglas del paso de precios.
     *
     * El descuento es opcional, pero si se elige una forma hay que indicar el valor:
     * un porcentaje no puede pasar de 100 y un valor fijo no puede alcanzar el precio
     * de venta, porque dejaría el producto en cero o por debajo.
     *
     * @return array<string, mixed>
     */
    private function pricingRules(): array
    {
        $rules = [
            'cost_price'        => ['nullable', 'numeric', 'min:0'],
            'profit_percentage' => ['nullable', 'numeric'],
            'profit_margin'     => ['nullable', 'numeric'],
            'sale_price'        => ['required', 'numeric', 'min:0'],
            'discount_type'     => ['nullable', Rule::in([Product::DISCOUNT_PERCENTAGE, Product::DISCOUNT_AMOUNT])],
            'status'            => ['boolean'],
        ];

        if ($this->discount_type === Product::DISCOUNT_PERCENTAGE) {
            $rules['discount_value'] = ['required', 'numeric', 'gt:0', 'max:100'];

            return $rules;
        }

        if ($this->discount_type === Product::DISCOUNT_AMOUNT) {
            $sale_price = is_numeric($this->sale_price) ? (float) $this->sale_price : 0;

            $rules['discount_value'] = ['required', 'numeric', 'gt:0', 'lt:'.max($sale_price, 0.01)];

            return $rules;
        }

        $rules['discount_value'] = ['nullable'];

        return $rules;
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

    private function fillMissingDerivedPricing(): void
    {
        $cost    = $this->parseDecimal($this->cost_price);
        $percent = $this->parseDecimal($this->profit_percentage);
        $margin  = $this->parseDecimal($this->profit_margin);
        $sale    = $this->parseDecimal($this->sale_price);

        if ($cost === null) {
            return;
        }

        if ($percent !== null && $sale === null) {
            $this->applyFromPercentage();

            return;
        }

        if ($sale !== null && $percent === null) {
            $this->applyFromSale();

            return;
        }

        if ($margin !== null && ($sale === null || $percent === null)) {
            $this->applyFromMargin();
        }
    }

    private function resolvedPricingSource(): string
    {
        if (in_array($this->pricing_source, ['percentage', 'margin', 'sale'], true)) {
            return $this->pricing_source;
        }

        if ($this->parseDecimal($this->profit_percentage) !== null) {
            return 'percentage';
        }

        if ($this->parseDecimal($this->sale_price) !== null) {
            return 'sale';
        }

        if ($this->parseDecimal($this->profit_margin) !== null) {
            return 'margin';
        }

        return '';
    }

    private function forgetPricingIfCleared(string $value): bool
    {
        if (trim($value) !== '') {
            return false;
        }

        $this->cost_price        = '';
        $this->profit_percentage = '';
        $this->profit_margin     = '';
        $this->sale_price        = '';
        $this->pricing_source    = '';

        return true;
    }

    private function parseDecimal(string $value): ?float
    {
        $value = trim($value);

        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function formatDecimal(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
