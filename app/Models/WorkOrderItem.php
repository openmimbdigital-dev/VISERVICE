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

    /**
     * Precio unitario con el descuento ya aplicado, a dos decimales.
     *
     * El descuento se aplica al precio, no al total de la línea: la facturación
     * electrónica exige que el total sea exactamente cantidad × precio, y un
     * descuento aplicado al total no siempre se puede repartir en un precio de
     * dos decimales. Cobrar un precio unitario rebajado es además lo que el
     * cliente entiende que está pagando.
     */
    public static function discountedUnitPrice(float $unit_price, float $discount_percentage): float
    {
        $discount_percentage = max(0, min(100, $discount_percentage));

        return round($unit_price * (1 - $discount_percentage / 100), 2);
    }

    public static function lineSubtotal(float $quantity, float $unit_price, float $discount_percentage): float
    {
        return round($quantity * self::discountedUnitPrice($unit_price, $discount_percentage), 2);
    }

    public function calculateSubtotal(): float
    {
        return self::lineSubtotal(
            (float) $this->quantity,
            (float) $this->unit_price,
            (float) $this->discount_percentage
        );
    }

    /** Precio unitario que realmente se cobra en esta línea. */
    public function finalUnitPrice(): float
    {
        return self::discountedUnitPrice((float) $this->unit_price, (float) $this->discount_percentage);
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
        return round($this->lineBase() - $this->calculateSubtotal(), 2);
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
