<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentType extends Model
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

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'equipment_type_business')
            ->withTimestamps();
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'equipment_type_id');
    }

    public function attributeProductTypes(): MorphMany
    {
        return $this->morphMany(AttributeProductType::class, 'model');
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

        $business_id = $user?->business_id;

        if (! $business_id) {
            return $query->where("{$table}.general", true)
                ->whereDoesntHave('businesses');
        }

        return $query->where(function (Builder $q) use ($business_id, $table) {
            $q->where(function (Builder $q2) use ($business_id, $table) {
                $q2->where("{$table}.general", false)
                    ->where("{$table}.business_id", $business_id);
            })
                ->orWhere(function (Builder $q2) use ($business_id, $table) {
                    $q2->where("{$table}.general", true)
                        ->whereHas('businesses', fn (Builder $bq) => $bq->where('businesses.id', $business_id));
                })
                ->orWhere(function (Builder $q2) use ($table) {
                    $q2->where("{$table}.general", true)
                        ->whereDoesntHave('businesses');
                });
        });
    }

    public function isEditableBy(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user?->hasRole('superAdmin')
            && $user->can('settings.equipment_types.edit');
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
        return $this->isEditableBy($user)
            && $user?->can('settings.equipment_types.delete')
            && ! $this->hasDependencies();
    }
}
