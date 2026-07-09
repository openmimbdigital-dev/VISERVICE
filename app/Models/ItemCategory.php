<?php

namespace App\Models;

use App\Models\Concerns\InteractsWithSharedCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemCategory extends Model
{
    use InteractsWithSharedCatalog;
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'name', 'label', 'inventory', 'active', 'general',
    ];

    protected function casts(): array
    {
        return [
            'inventory' => 'boolean',
            'active'    => 'boolean',
            'general'   => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'brand_item_category')
            ->withTimestamps();
    }

    public function hasDependencies(): bool
    {
        return $this->items()->exists();
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $this->isEditableBy($user)
            && $user?->can('settings.item_categories.delete')
            && ! $this->hasDependencies();
    }
}
