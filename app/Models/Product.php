<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
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
        'track_inventory',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'step'             => 'integer',
            'final_step'       => 'integer',
            'cost_price'         => 'decimal:2',
            'profit_percentage'  => 'decimal:2',
            'sale_price'         => 'decimal:2',
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

    public function scopeComplete(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        return $query
            ->whereNotNull("{$table}.product_type_id")
            ->whereNotNull("{$table}.product_category_id")
            ->whereNotNull("{$table}.unit_id")
            ->whereNotNull("{$table}.cost_price")
            ->whereNotNull("{$table}.profit_percentage")
            ->whereNotNull("{$table}.sale_price");
    }

    public function isComplete(): bool
    {
        return $this->product_type_id !== null
            && $this->product_category_id !== null
            && $this->unit_id !== null
            && $this->cost_price !== null
            && $this->profit_percentage !== null
            && $this->sale_price !== null;
    }

    public function profitMarginAmount(): ?float
    {
        if ($this->cost_price === null || $this->profit_percentage === null) {
            return null;
        }

        return round((float) $this->cost_price * ((float) $this->profit_percentage / 100), 2);
    }

    public function progressPercent(): int
    {
        $final = max(1, (int) $this->final_step);

        return (int) min(100, round(((int) $this->step / $final) * 100));
    }

    public function scopeForAuthUser(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

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
