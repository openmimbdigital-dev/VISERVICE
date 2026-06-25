<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentModel extends Model
{
    protected $table = 'equipment_models';

    protected $fillable = [
        'business_id', 'brand_id', 'name', 'label', 'active', 'general',
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

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'model_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeVisibleToUser(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('general', true)
                ->orWhere('business_id', $user?->business_id);
        });
    }

    public function isEditableBy(?User $user = null): bool
    {
        $user ??= auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return true;
        }

        return ! $this->general && $this->business_id === $user?->business_id;
    }

    public function hasDependencies(): bool
    {
        return $this->equipment()->exists();
    }

    public function canDelete(?User $user = null): bool
    {
        return $this->isEditableBy($user) && ! $this->hasDependencies();
    }
}
