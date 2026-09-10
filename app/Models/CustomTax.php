<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusinessTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomTax extends Model
{
    use BelongsToBusinessTenant;
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'percentage',
        'general',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'general'    => 'boolean',
            'active'     => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function scopeForAuthUser(Builder $query, ?User $user = null): Builder
    {
        $user  ??= auth()->user();
        $table = $query->getModel()->getTable();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

        $business_ids = $user?->businessIds() ?? [];

        if ($business_ids === []) {
            return $query->where("{$table}.general", true);
        }

        return $query->where(function (Builder $q) use ($table, $business_ids) {
            $q->where("{$table}.general", true)
                ->orWhereIn("{$table}.business_id", $business_ids);
        });
    }

    public function scopeAvailableForBusiness(Builder $query, int $business_id): Builder
    {
        $table = $query->getModel()->getTable();

        return $query->where(function (Builder $q) use ($table, $business_id) {
            $q->where("{$table}.business_id", $business_id)
                ->orWhere("{$table}.general", true);
        });
    }

    public function isEditableBy(?User $user = null, string $edit_permission = ''): bool
    {
        $user ??= auth()->user();

        if ($edit_permission !== '' && ! $user?->can($edit_permission)) {
            return false;
        }

        if ($user?->hasRole('superAdmin')) {
            return true;
        }

        return ! $this->general
            && $this->business_id !== null
            && $user->belongsToBusiness($this->business_id);
    }

    public function isGeneralReadonly(?User $user = null): bool
    {
        return $this->general && ! $this->isEditableBy($user);
    }

    protected function selectLabel(): Attribute
    {
        return Attribute::get(function (): string {
            $percentage = rtrim(rtrim(number_format((float) $this->percentage, 2, '.', ''), '0'), '.');

            return $this->name.' ('.$percentage.'%)';
        });
    }

    public function appliedTaxes(): HasMany
    {
        return $this->hasMany(AppliedTax::class);
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $this->isEditableBy($user, 'custom_taxes.delete');
    }
}
