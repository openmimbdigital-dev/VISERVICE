<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Remission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'work_order_id', 'reference',
        'status', 'notes', 'items', 'issued_at', 'delivered_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'items'        => 'array',
            'issued_at'    => 'datetime',
            'delivered_at' => 'datetime',
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

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'borrador'   => 'Borrador',
            'emitida'    => 'Emitida',
            'entregada'  => 'Entregada',
            default      => $this->status,
        };
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
