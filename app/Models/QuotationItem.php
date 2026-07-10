<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'item_id', 'item_type_id', 'item_category_id',
        'description', 'quantity', 'unit_price', 'discount_percentage', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity'            => 'decimal:2',
            'unit_price'          => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'subtotal'            => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function itemType(): BelongsTo
    {
        return $this->belongsTo(ItemType::class);
    }

    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function calculateSubtotal(): float
    {
        $base     = (float) $this->quantity * (float) $this->unit_price;
        $discount = $base * ((float) $this->discount_percentage / 100);

        return round($base - $discount, 2);
    }

    public function legacyWorkOrderItemType(): string
    {
        $category = $this->itemCategory?->name ?? '';

        return match (true) {
            $category === 'Mano de Obra' => 'servicio',
            $category === 'Repuestos'   => 'repuesto',
            default                     => 'otro',
        };
    }
}
