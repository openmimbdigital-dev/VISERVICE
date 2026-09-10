<?php

namespace App\Models;

use App\Models\Concerns\HasWizardProgress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasWizardProgress;
    use SoftDeletes;

    public const DEFAULT_FINAL_STEP = 4;

    protected $fillable = [
        'business_id',
        'step',
        'final_step',
        'product_type_id',
        'product_category_id',
        'unit_id',
        'brand_id',
        'sku',
        'barcode',
        'name',
        'description',
        'cost_price',
        'profit_percentage',
        'sale_price',
        'discount_type',
        'discount_value',
        'track_inventory',
        'status',
        'is_subscription_plan',
    ];

    /** Formas admitidas de expresar el descuento. */
    public const DISCOUNT_PERCENTAGE = 'percentage';

    public const DISCOUNT_AMOUNT = 'amount';

    protected function casts(): array
    {
        return [
            'is_subscription_plan' => 'boolean',
            'step'             => 'integer',
            'final_step'       => 'integer',
            'cost_price'         => 'decimal:2',
            'profit_percentage'  => 'decimal:2',
            'sale_price'         => 'decimal:2',
            'discount_value'     => 'decimal:2',
            'track_inventory'  => 'boolean',
            'status'           => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function product_type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function product_category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function primaryImage(): ?ProductImage
    {
        return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
    }

    public function primaryImageUrl(): ?string
    {
        return $this->primaryImage()?->url;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where($query->getModel()->getTable() . '.status', true);
    }

    /**
     * Los productos que respaldan planes de suscripción no son parte del
     * catálogo: se ocultan siempre, para que nadie los agregue por error a una
     * orden de trabajo o los edite por fuera del plan. Quien necesite verlos
     * —el módulo de suscripciones— usa scopeWithSubscriptionPlans().
     */
    protected static function booted(): void
    {
        static::addGlobalScope('withoutSubscriptionPlans', function (Builder $query) {
            $query->where($query->getModel()->getTable().'.is_subscription_plan', false);
        });
    }

    /** Levanta el ocultamiento de los productos de plan. */
    public function scopeWithSubscriptionPlans(Builder $query): Builder
    {
        return $query->withoutGlobalScope('withoutSubscriptionPlans');
    }

    /** Solo los productos de plan. */
    public function scopeOnlySubscriptionPlans(Builder $query): Builder
    {
        return $query->withoutGlobalScope('withoutSubscriptionPlans')
            ->where($query->getModel()->getTable().'.is_subscription_plan', true);
    }

    public function scopeComplete(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        return $query
            ->whereNotNull("{$table}.product_type_id")
            ->whereNotNull("{$table}.product_category_id")
            ->whereNotNull("{$table}.unit_id")
            ->whereNotNull("{$table}.sale_price");
    }

    public function isComplete(): bool
    {
        return $this->product_type_id !== null
            && $this->product_category_id !== null
            && $this->unit_id !== null
            && $this->sale_price !== null;
    }

    public function hasDiscount(): bool
    {
        return $this->discount_type !== null
            && $this->discount_value !== null
            && (float) $this->discount_value > 0
            && (float) $this->sale_price > 0;
    }

    /**
     * Descuento expresado en porcentaje.
     *
     * Los ítems de cotizaciones y órdenes de trabajo descuentan por porcentaje, así
     * que un descuento en valor se convierte aquí: sobre un precio P, un descuento
     * de $V por unidad equivale a V/P × 100, y la línea da exactamente lo mismo.
     */
    public function discountPercentage(): float
    {
        if (! $this->hasDiscount()) {
            return 0.0;
        }

        if ($this->discount_type === self::DISCOUNT_PERCENTAGE) {
            return min(100.0, round((float) $this->discount_value, 2));
        }

        $percentage = (float) $this->discount_value / (float) $this->sale_price * 100;

        return min(100.0, round($percentage, 2));
    }

    /** Valor descontado por unidad. */
    public function discountAmount(): float
    {
        if (! $this->hasDiscount()) {
            return 0.0;
        }

        if ($this->discount_type === self::DISCOUNT_AMOUNT) {
            return min((float) $this->sale_price, round((float) $this->discount_value, 2));
        }

        return round((float) $this->sale_price * (float) $this->discount_value / 100, 2);
    }

    /** Precio unitario ya con el descuento aplicado. */
    public function finalPrice(): float
    {
        return round(max(0, (float) $this->sale_price - $this->discountAmount()), 2);
    }

    /** Etiqueta corta del descuento para mostrar en listados. */
    public function discountLabel(): ?string
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        return $this->discount_type === self::DISCOUNT_PERCENTAGE
            ? rtrim(rtrim(number_format((float) $this->discount_value, 2, '.', ''), '0'), '.').'%'
            : col_money($this->discountAmount());
    }

    public function profitMarginAmount(): ?float
    {
        if ($this->cost_price === null) {
            return null;
        }

        if ($this->sale_price !== null) {
            return round((float) $this->sale_price - (float) $this->cost_price, 2);
        }

        if ($this->profit_percentage === null) {
            return null;
        }

        return round((float) $this->cost_price * ((float) $this->profit_percentage / 100), 2);
    }

    public function scopeForAuthUser(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();

        // El superAdmin no es un comodín sobre los datos de los comercios: ve los
        // de su propio negocio, igual que cualquiera. Para mirar los de otro entra
        // como un usuario suyo (ver App\Support\Impersonation), y así lo hace con
        // sus permisos y su alcance en vez de con reglas especiales.
        $business_ids = $user->businessIds();

        if ($business_ids === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn($query->getModel()->getTable() . '.business_id', $business_ids);
    }

    public function isEditableBy(?User $user = null): bool
    {
        $user ??= auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return $user->can('catalog.products.edit');
        }

        return $user->belongsToBusiness($this->business_id)
            && $user->can('catalog.products.edit');
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $this->isEditableBy($user) && $user?->can('catalog.products.delete');
    }
}
