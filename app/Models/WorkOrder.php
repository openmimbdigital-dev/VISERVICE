<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'client_id', 'equipment_id', 'quotation_id',
        'reference', 'status', 'km_entry', 'km_exit',
        'diagnosis', 'work_description', 'observations', 'notes',
        'estimated_delivery', 'subtotal', 'tax_percentage',
        'tax_amount', 'total', 'created_by', 'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_delivery' => 'date',
            'finalized_at'       => 'datetime',
            'subtotal'           => 'decimal:2',
            'tax_percentage'     => 'decimal:2',
            'tax_amount'         => 'decimal:2',
            'total'              => 'decimal:2',
            'km_entry'           => 'integer',
            'km_exit'            => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function remissions(): HasMany
    {
        return $this->hasMany(Remission::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(WorkOrderInvoice::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function latestInvoice(): HasOne
    {
        return $this->hasOne(WorkOrderInvoice::class)->latestOfMany();
    }

    public function scopeForAuthUser($query)
    {
        $user = auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

        $business_ids = $user->businessIds();

        if ($business_ids === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn($query->getModel()->getTable() . '.business_id', $business_ids);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['abierta', 'en_proceso']);
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', 'finalizada');
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->whereNotIn('status', ['cancelado'])->sum('subtotal');
        $tax      = round($subtotal * ($this->tax_percentage / 100), 2);
        $this->update([
            'subtotal'   => $subtotal,
            'tax_amount' => $tax,
            'total'      => $subtotal + $tax,
        ]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'abierta'    => 'Abierta',
            'en_proceso' => 'En proceso',
            'finalizada' => 'Finalizada',
            'cancelada'  => 'Cancelada',
            default      => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'abierta'    => 'blue',
            'en_proceso' => 'yellow',
            'finalizada' => 'green',
            'cancelada'  => 'red',
            default      => 'gray',
        };
    }

    public static function generateReference(int $businessId): string
    {
        $prefix = 'OT-' . now()->format('Ym') . '-';
        $last = static::withTrashed()
            ->where('business_id', $businessId)
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
