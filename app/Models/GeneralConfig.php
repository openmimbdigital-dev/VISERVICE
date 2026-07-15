<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeneralConfig extends Model
{
    protected $fillable = [
        'business_id',
        'configurable_type',
        'configurable_id',
        'key',
        'label',
        'value',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function configurable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForAuthUser($query)
    {
        $user = auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

        $business_ids = $user->businessIds();

        if ($business_ids === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn($query->getModel()->getTable() . '.business_id', $business_ids);
    }

    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }
}
