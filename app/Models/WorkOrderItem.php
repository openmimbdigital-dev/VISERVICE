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

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(WorkOrderInvoiceItem::class);
    }

    public function calculateSubtotal(): float
    {
        return round($this->lineBase() - $this->discountAmount(), 2);
    }

    /** Lo que costaría la línea sin el descuento del producto. */
    public function lineBase(): float
    {
        return round((float) $this->quantity * (float) $this->unit_price, 2);
    }

    public function hasDiscount(): bool
    {
        return (float) $this->discount_percentage > 0;
    }

    /** Valor descontado en la línea por el descuento del producto. */
    public function discountAmount(): float
    {
        return round($this->lineBase() * ((float) $this->discount_percentage / 100), 2);
    }

    /** Etiqueta corta del descuento, para mostrarlo junto a la línea. */
    public function discountLabel(): ?string
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        return rtrim(rtrim(number_format((float) $this->discount_percentage, 2, '.', ''), '0'), '.').'%';
    }
}
