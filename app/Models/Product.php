<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'product_type_id',
        'product_category_id',
        'unit_id',
        'brand_id',
        'code',
        'name',
        'description',
        'cost_price',
        'sale_price',
        'tax_id',
        'track_inventory',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'cost_price'       => 'decimal:2',
            'sale_price'       => 'decimal:2',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where($query->getModel()->getTable() . '.status', true);
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
