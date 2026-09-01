<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'description', 'quantity',
        'unit_price', 'subtotal', 'received_quantity', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'          => 'decimal:2',
            'unit_price'        => 'decimal:2',
            'subtotal'          => 'decimal:2',
            'received_quantity' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function calculateSubtotal(): float
    {
        return round((float) $this->quantity * (float) $this->unit_price, 2);
    }

    public function getPendingQuantityAttribute(): float
    {
        return max(0, (float) $this->quantity - (float) $this->received_quantity);
    }
}
