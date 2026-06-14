<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'business_id',
        'invoice_number',
        'amount',
        'status',
        'billing_period_start',
        'billing_period_end',
        'due_date',
        'paid_at',
        'payment_method',
        'payment_reference',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'               => 'decimal:2',
            'billing_period_start' => 'date',
            'billing_period_end'   => 'date',
            'due_date'             => 'date',
            'paid_at'              => 'datetime',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'Pendiente',
            'paid'     => 'Pagada',
            'failed'   => 'Fallida',
            'refunded' => 'Reembolsada',
            default    => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'yellow',
            'paid'     => 'green',
            'failed'   => 'red',
            'refunded' => 'blue',
            default    => 'gray',
        };
    }

    public static function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $last = self::whereYear('created_at', $year)->count() + 1;

        return "INV-{$year}{$month}-" . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}
