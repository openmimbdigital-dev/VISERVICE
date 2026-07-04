<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderItem extends Model
{
    protected $fillable = [
        'work_order_id', 'item_type', 'description',
        'quantity', 'unit_price', 'discount_percentage',
        'subtotal', 'status', 'technician_notes',
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

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function calculateSubtotal(): float
    {
        $base     = (float) $this->quantity * (float) $this->unit_price;
        $discount = $base * ((float) $this->discount_percentage / 100);
        return round($base - $discount, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pendiente'   => 'Pendiente',
            'en_proceso'  => 'En proceso',
            'completado'  => 'Completado',
            'cancelado'   => 'Cancelado',
            default       => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pendiente'  => 'gray',
            'en_proceso' => 'yellow',
            'completado' => 'green',
            'cancelado'  => 'red',
            default      => 'gray',
        };
    }

    public function getItemTypeLabelAttribute(): string
    {
        return match ($this->item_type) {
            'servicio' => 'Servicio',
            'repuesto' => 'Repuesto',
            default    => 'Otro',
        ];
    }
}
