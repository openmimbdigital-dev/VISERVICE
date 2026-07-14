<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'business_id', 'name', 'label', 'active', 'general',
    ];

    protected function casts(): array
    {
        return [
            'active'  => 'boolean',
            'general' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'brand_id');
    }

    public function equipmentModels(): HasMany
    {
        return $this->hasMany(EquipmentModel::class, 'brand_id');
    }

    public function equipmentTypes(): BelongsToMany
    {
        return $this->belongsToMany(EquipmentType::class, 'brand_equipment_type')
            ->withTimestamps();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    public function brandUsages(): HasMany
    {
        return $this->hasMany(BrandUsage::class);
    }

    public function productCategories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'brand_product_category')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        $table = $query->getModel()->getTable();

        return $query->where("{$table}.active", true);
    }

    public function scopeVisibleToUser(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();
        $table = $query->getModel()->getTable();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user, $table) {
            $business_ids = $user->businessIds();

            $q->where("{$table}.general", true);

            if ($business_ids !== []) {
                $q->orWhereIn("{$table}.business_id", $business_ids);
            }
        });
    }

    public function isEditableBy(?User $user = null): bool
    {
        $user ??= auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return $user->can('settings.brands.edit');
        }

        return ! $this->general
            && $user->belongsToBusiness($this->business_id)
            && $user->can('settings.brands.edit');
    }

    public function isGeneralReadonly(?User $user = null): bool
    {
        return $this->general && ! $this->isEditableBy($user);
    }

    public function hasDependencies(): bool
    {
        return $this->equipment()->exists() || $this->products()->exists();
    }

    public function hasEquipmentUsage(): bool
    {
        return $this->brandUsages()
            ->where('type', \App\Enums\BrandUsageType::Equipment)
            ->exists();
    }

    public function hasProductsUsage(): bool
    {
        return $this->brandUsages()
            ->where('type', \App\Enums\BrandUsageType::Products)
            ->exists();
    }

    public function scopeForProductsCatalog(Builder $query): Builder
    {
        return $query->whereHas('brandUsages', function (Builder $usage_query) {
            $usage_query->where('type', \App\Enums\BrandUsageType::Products);
        });
    }

    public function canDelete(?User $user = null): bool
    {
        return $this->isEditableBy($user)
            && $user?->can('settings.brands.delete')
            && ! $this->hasDependencies();
    }
}
