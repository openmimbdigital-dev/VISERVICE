<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
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
            $q->where("{$table}.general", true)
                ->orWhere("{$table}.business_id", $user?->business_id);
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

    public function isGeneralReadonly(?User $user = null): bool
    {
        return $this->general && ! $this->isEditableBy($user);
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
