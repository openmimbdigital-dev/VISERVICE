<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'subscription_plan_id',
        'status',
        'billing_cycle',
        'monthly_price',
        'total_price',
        'discount_percentage',
        'started_at',
        'ends_at',
        'trial_ends_at',
        'cancelled_at',
        'cancellation_reason',
        'auto_renew',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price'       => 'decimal:2',
            'total_price'         => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'started_at'          => 'date',
            'ends_at'             => 'date',
            'trial_ends_at'       => 'date',
            'cancelled_at'        => 'datetime',
            'auto_renew'          => 'boolean',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['trial', 'active']);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return in_array($this->status, ['trial', 'active']);
    }

    /**
     * Vigencia real: valida estado Y fecha (prioriza trial_ends_at durante el trial).
     */
    public function isCurrentlyValid(): bool
    {
        if (! in_array($this->status, ['trial', 'active'], true)) {
            return false;
        }

        if ($this->status === 'trial' && $this->trial_ends_at) {
            return ! $this->trial_ends_at->copy()->endOfDay()->isPast();
        }

        return $this->ends_at !== null && ! $this->ends_at->copy()->endOfDay()->isPast();
    }

    public function isExpired(): bool
    {
        return ! $this->isCurrentlyValid();
    }

    public function daysUntilExpiry(): int
    {
        return max(0, now()->diffInDays($this->ends_at, false));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Pago pendiente',
            'trial'     => 'Prueba',
            'active'    => 'Activa',
            'past_due'  => 'Vencida',
            'cancelled' => 'Cancelada',
            'expired'   => 'Expirada',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'orange',
            'trial'     => 'blue',
            'active'    => 'green',
            'past_due'  => 'yellow',
            'cancelled' => 'gray',
            'expired'   => 'red',
            default     => 'gray',
        };
    }

    public function getBillingCycleLabelAttribute(): string
    {
        return match ($this->billing_cycle) {
            'monthly'    => 'Mensual',
            'quarterly'  => 'Trimestral',
            'semiannual' => 'Semestral',
            'annual'     => 'Anual',
            default      => $this->billing_cycle,
        };
    }
}
