<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BusinessPaymentMethod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'label',
        'general',
        'active',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'general'      => 'boolean',
            'active'       => 'boolean',
            'is_default'   => 'boolean',
            'sort_order'   => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (BusinessPaymentMethod $method) {
            if ($method->isDirty('name') || blank($method->label)) {
                $method->label = static::normalizeLabel($method->name);
            }
        });
    }

    public static function normalizeLabel(string $name): string
    {
        $ascii = Str::ascii($name);
        $label = strtolower($ascii);
        $label = preg_replace('/[^a-z0-9\s_]/', '', $label) ?? '';
        $label = preg_replace('/\s+/', '_', trim($label)) ?? '';
        $label = preg_replace('/_+/', '_', $label) ?? '';

        return trim($label, '_');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function scopeVisibleToUser(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();
        $table  = $query->getModel()->getTable();

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

    public function isEditableBy(?User $user = null, string $edit_permission = 'business_payment_methods.edit'): bool
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

    public function canDelete(?User $user = null): bool
    {
        return $this->isEditableBy($user, 'business_payment_methods.delete');
    }
}
