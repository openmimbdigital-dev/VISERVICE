<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrderItem extends Model
{
    protected $fillable = [
        'work_order_id',
        'equipment_id',
        'product_id',
        'product_type_id',
        'description',
        'quantity',
        'quantity_complete',
        'quantity_canceled',
        'unit_price',
        'discount_percentage',
        'subtotal',
        'technician_notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'            => 'decimal:2',
            'quantity_complete'   => 'decimal:2',
            'quantity_canceled'   => 'decimal:2',
            'unit_price'          => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'subtotal'            => 'decimal:2',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function catalogProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function remissionItems(): HasMany
    {
        return $this->hasMany(RemissionItem::class);
    }

    public function calculateSubtotal(): float
    {
        $base     = (float) $this->quantity * (float) $this->unit_price;
        $discount = $base * ((float) $this->discount_percentage / 100);

        return round($base - $discount, 2);
    }
}
