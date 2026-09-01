<?php

namespace App\Models;

use App\Models\Concerns\InteractsWithSharedCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductType extends Model
{
    use InteractsWithSharedCatalog;
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

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function hasDependencies(): bool
    {
        return $this->products()->exists();
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $this->isEditableBy($user)
            && $user?->can('settings.product_types.delete')
            && ! $this->hasDependencies();
    }
}
