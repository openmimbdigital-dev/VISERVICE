<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'client_id', 'equipment_id', 'reference',
        'status', 'diagnosis', 'km_entry', 'valid_until',
        'subtotal', 'tax_percentage', 'tax_amount', 'total',
        'notes', 'observations', 'created_by',
        'sent_at', 'accepted_at', 'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'valid_until'     => 'date',
            'sent_at'         => 'datetime',
            'accepted_at'     => 'datetime',
            'rejected_at'     => 'datetime',
            'subtotal'        => 'decimal:2',
            'tax_percentage'  => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total'           => 'decimal:2',
            'km_entry'        => 'integer',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['rechazada', 'vencida']);
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

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
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
            'borrador'  => 'Borrador',
            'enviada'   => 'Enviada',
            'aceptada'  => 'Aceptada',
            'rechazada' => 'Rechazada',
            'vencida'   => 'Vencida',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'borrador'  => 'gray',
            'enviada'   => 'blue',
            'aceptada'  => 'green',
            'rechazada' => 'red',
            'vencida'   => 'orange',
            default     => 'gray',
        };
    }

    public static function generateReference(int $businessId): string
    {
        $prefix = 'COT-' . now()->format('Ym') . '-';
        $last = static::withTrashed()
            ->where('business_id', $businessId)
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
