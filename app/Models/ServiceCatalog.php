<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCatalog extends Model
{
    use SoftDeletes;

    protected $table = 'services_catalog';

    protected $fillable = [
        'business_id', 'code', 'name', 'description',
        'category', 'default_price', 'duration_minutes',
        'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'        => 'boolean',
            'default_price'    => 'decimal:2',
            'duration_minutes' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDurationLabelAttribute(): string
    {
        if ($this->duration_minutes < 60) {
            return "{$this->duration_minutes} min";
        }
        $hours = floor($this->duration_minutes / 60);
        $min   = $this->duration_minutes % 60;
        return $min ? "{$hours}h {$min}min" : "{$hours}h";
    }
}
