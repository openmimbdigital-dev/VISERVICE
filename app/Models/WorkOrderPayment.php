<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'work_order_id',
        'amount',
        'percentage',
        'payment_method',
        'business_payment_method_id',
        'business_bank_account_id',
        'payment_reference',
        'paid_at',
        'notes',
        'status',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'percentage' => 'decimal:2',
            'paid_at'    => 'datetime',
            'cancelled_at' => 'datetime',
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

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(BusinessPaymentMethod::class, 'business_payment_method_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BusinessBankAccount::class, 'business_bank_account_id');
    }

    public function statusDefinition(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status', 'name');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function scopeForAuthUser($query)
    {
        $user = auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

        $business_ids = $user?->businessIds() ?? [];

        if ($business_ids === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn($query->getModel()->getTable().'.business_id', $business_ids);
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    /** Registro de anticipo definido (no es un abono cobrado). */
    public function isCommitment(): bool
    {
        return $this->isPending();
    }

    /** Abono real de dinero (confirmado o anulado). */
    public function isAbono(): bool
    {
        return in_array($this->status, ['confirmed', 'voided'], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->statusDefinition?->label
            ?? (string) $this->status;
    }
}
