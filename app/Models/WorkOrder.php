<?php

namespace App\Models;

use App\Enums\WorkOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'client_id', 'quotation_id',
        'reference', 'status', 'status_comments',
        'diagnosis', 'work_description', 'observations', 'notes',
        'estimated_delivery', 'subtotal', 'tax_percentage',
        'tax_amount', 'total', 'advance_percentage', 'advance_amount',
        'document_client', 'created_by', 'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'status'             => WorkOrderStatus::class,
            'status_comments'    => 'array',
            'estimated_delivery' => 'date',
            'finalized_at'       => 'datetime',
            'subtotal'           => 'decimal:2',
            'tax_percentage'     => 'decimal:2',
            'tax_amount'         => 'decimal:2',
            'total'              => 'decimal:2',
            'advance_percentage' => 'decimal:2',
            'advance_amount'     => 'decimal:2',
            'document_client'    => 'array',
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

    public function equipments(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'equipment_work_order')
            ->withTimestamps();
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusDefinition(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status', 'name');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(WorkOrderPayment::class);
    }

    public function confirmedPayments(): HasMany
    {
        return $this->payments()->where('status', 'confirmed');
    }

    public function advancePaidAmount(): float
    {
        if (array_key_exists('confirmed_paid_sum', $this->attributes)) {
            return round((float) ($this->attributes['confirmed_paid_sum'] ?? 0), 2);
        }

        if ($this->relationLoaded('payments')) {
            return round((float) $this->payments
                ->where('status', 'confirmed')
                ->sum('amount'), 2);
        }

        return round((float) $this->confirmedPayments()->sum('amount'), 2);
    }

    public function advanceRemainingAmount(): float
    {
        return max(0, round((float) $this->advance_amount - $this->advancePaidAmount(), 2));
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

    public function equipment_historicals(): MorphMany
    {
        return $this->morphMany(EquipmentHistorical::class, 'subject')->orderByDesc('created_at');
    }

    public function generalConfigs(): MorphMany
    {
        return $this->morphMany(GeneralConfig::class, 'configurable');
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
        return $query->whereIn('status', WorkOrderStatus::openValues());
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', WorkOrderStatus::Completed);
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
        if ($this->relationLoaded('statusDefinition') && $this->statusDefinition) {
            return $this->statusDefinition->label;
        }

        return $this->status instanceof WorkOrderStatus
            ? $this->status->label()
            : (string) $this->status;
    }

    public function isEditable(): bool
    {
        return $this->status instanceof WorkOrderStatus
            ? ! $this->status->isTerminal()
            : true;
    }

    public function canChangeStatus(): bool
    {
        return $this->isEditable();
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            WorkOrderStatus::Created => 'blue',
            WorkOrderStatus::InProgress => 'yellow',
            WorkOrderStatus::Completed => 'green',
            WorkOrderStatus::Cancelled => 'red',
            default => 'gray',
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
