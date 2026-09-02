<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'work_order_id', 'reference',
        'subtotal', 'tax_percentage', 'tax_amount', 'total',
        'status', 'due_date', 'paid_at',
        'payment_method', 'payment_reference', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date'       => 'date',
            'paid_at'        => 'datetime',
            'subtotal'       => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'tax_amount'     => 'decimal:2',
            'total'          => 'decimal:2',
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

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderInvoiceItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pendiente' => 'Pendiente',
            'pagada'    => 'Pagada',
            'vencida'   => 'Vencida',
            'anulada'   => 'Anulada',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pendiente' => 'yellow',
            'pagada'    => 'green',
            'vencida'   => 'red',
            'anulada'   => 'gray',
            default     => 'gray',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pendiente' => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20',
            'pagada'    => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
            'vencida'   => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
            'anulada'   => 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20',
            default     => 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20',
        };
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

    public static function generateReference(int $businessId): string
    {
        $prefix = 'FAC-' . now()->format('Ym') . '-';
        $last = static::withTrashed()
            ->where('business_id', $businessId)
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
