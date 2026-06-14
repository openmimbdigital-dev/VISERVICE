<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'work_order_id', 'reference',
        'supplier_name', 'supplier_nit', 'supplier_phone',
        'status', 'expected_delivery', 'total', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expected_delivery' => 'date',
            'total'             => 'decimal:2',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function recalculateTotal(): void
    {
        $this->update(['total' => $this->items()->sum('subtotal')]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'borrador'  => 'Borrador',
            'enviada'   => 'Enviada',
            'recibida'  => 'Recibida',
            'cancelada' => 'Cancelada',
            default     => $this->status,
        };
    }

    public static function generateReference(int $businessId): string
    {
        $prefix = 'OC-' . now()->format('Ym') . '-';
        $last = static::withTrashed()
            ->where('business_id', $businessId)
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
