<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SparePartCatalog extends Model
{
    use SoftDeletes;

    protected $table = 'spare_parts_catalog';

    protected $fillable = [
        'business_id', 'code', 'name', 'description',
        'category', 'brand', 'unit', 'unit_price',
        'stock', 'min_stock', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'unit_price' => 'decimal:2',
            'stock'      => 'integer',
            'min_stock'  => 'integer',
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

    public function getLowStockAttribute(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}
