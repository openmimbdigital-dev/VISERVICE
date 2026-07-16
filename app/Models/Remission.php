<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Remission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'work_order_id',
        'client_id',
        'equipment_id',
        'reference',
        'type',
        'status',
        'quotation_or_po_reference',
        'issue_date',
        'delivery_address',
        'delivery_city',
        'delivery_contact',
        'delivery_phone',
        'delivery_observations',
        'observations',
        'delivered_by_name',
        'delivered_by_position',
        'delivered_by_document',
        'delivered_at',
        'delivered_by_signature',
        'received_by_name',
        'received_by_position',
        'received_by_document',
        'received_at',
        'received_by_signature',
        'total_items',
        'issued_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'   => 'date',
            'issued_at'    => 'datetime',
            'delivered_at' => 'datetime',
            'received_at'  => 'datetime',
            'total_items'  => 'integer',
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
        return $this->hasMany(RemissionItem::class)->orderBy('sort_order');
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

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'entrega'    => 'Entrega',
            'devolucion' => 'Devolución',
            'traslado'   => 'Traslado',
            default      => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'borrador'  => 'Borrador',
            'emitida'   => 'Emitida',
            'entregada' => 'Entregada',
            default     => $this->status,
        };
    }

    public function recalculateTotalItems(): void
    {
        $this->update([
            'total_items' => (int) $this->items()->sum('quantity'),
        ]);
    }

    public static function generateReference(int $businessId): string
    {
        $prefix = 'REM-' . now()->format('Ym') . '-';
        $last = static::withTrashed()
            ->where('business_id', $businessId)
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
