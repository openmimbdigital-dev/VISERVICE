<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'equipment_id', 'product_id', 'product_type_id', 'product_category_id',
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

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function calculateSubtotal(): float
    {
        $base     = (float) $this->quantity * (float) $this->unit_price;
        $discount = $base * ((float) $this->discount_percentage / 100);

        return round($base - $discount, 2);
    }
}
