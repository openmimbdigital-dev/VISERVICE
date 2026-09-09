<?php

namespace App\Models;

use App\Enums\WorkOrderStatus;
use App\Models\Concerns\HasAppliedTaxes;
use App\Models\Concerns\HasWizardProgress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasAppliedTaxes;
    use HasWizardProgress;
    use SoftDeletes;

    public const DEFAULT_FINAL_STEP = 3;

    protected $fillable = [
        'business_id', 'client_id', 'bill_to_final_consumer', 'quotation_id', 'step', 'final_step',
        'reference', 'status', 'status_comments',
        'diagnosis', 'work_description', 'observations', 'notes',
        'estimated_delivery', 'subtotal', 'coupon_id', 'coupon_code',
        'discount_amount', 'tax_amount', 'total', 'advance_percentage', 'advance_amount',
        'created_by', 'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'status'             => WorkOrderStatus::class,
            'bill_to_final_consumer' => 'boolean',
            'step'               => 'integer',
            'final_step'         => 'integer',
            'status_comments'    => 'array',
            'estimated_delivery' => 'date',
            'finalized_at'       => 'datetime',
            'subtotal'           => 'decimal:2',
            'discount_amount'    => 'decimal:2',
            'tax_amount'         => 'decimal:2',
            'total'              => 'decimal:2',
            'advance_percentage' => 'decimal:2',
            'advance_amount'     => 'decimal:2',
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

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
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

    public function associatedDocuments(): HasMany
    {
        return $this->hasMany(WorkOrderAssociatedDocument::class)->orderBy('name');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(WorkOrderInvoice::class);
    }

    /** Línea de tiempo de estados, del más antiguo al más reciente. */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(WorkOrderStatusHistory::class)
            ->orderBy('created_at')
            ->orderBy('id');
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

    public function scopeComplete(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        return $query->whereColumn("{$table}.step", '>=', "{$table}.final_step")
            ->whereExists(function ($sub) use ($table) {
                $sub->selectRaw('1')
                    ->from('work_order_items')
                    ->whereColumn('work_order_items.work_order_id', "{$table}.id");
            });
    }

    public function isComplete(): bool
    {
        $reached_end = (int) $this->step >= max(1, (int) $this->final_step);

        if (! $reached_end) {
            return false;
        }

        if ($this->relationLoaded('items')) {
            return $this->items->isNotEmpty();
        }

        return $this->items()->exists();
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', WorkOrderStatus::openValues());
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', WorkOrderStatus::Completed);
    }

    /**
     * Subtotal ya descontado el cupón: es la base sobre la que se calculan el
     * IVA, el anticipo y el total.
     */
    public function taxableBase(): float
    {
        return max(0, round((float) $this->subtotal - (float) $this->discount_amount, 2));
    }

    public function appliedTaxableBase(): float
    {
        return $this->taxableBase();
    }

    /** Suma de lo que se ahorró el cliente por descuentos de producto. */
    public function itemsDiscountAmount(): float
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();

        return round($items->sum(fn (WorkOrderItem $item) => $item->discountAmount()), 2);
    }

    public function recalculateTotals(): void
    {
        $subtotal = round((float) $this->items()->sum('subtotal'), 2);

        // El cupón se recalcula contra el subtotal actual: si se agregan o quitan
        // ítems, un descuento porcentual tiene que moverse con ellos.
        // Se relee el cupón en vez de usar la relación cargada: el coupon_id pudo
        // acabar de cambiar en este mismo guardado. Si el cupón se eliminó, la OT
        // conserva el descuento con el que se acordó.
        $coupon = $this->coupon_id ? Coupon::find($this->coupon_id) : null;

        $discount = match (true) {
            (bool) $coupon      => $coupon->discountOn($subtotal),
            // Sin registro pero con código: el cupón se eliminó después de
            // aplicarlo, así que se respeta el descuento ya pactado.
            (bool) $this->coupon_code => min($subtotal, round((float) $this->discount_amount, 2)),
            default             => 0.0,
        };

        $base = max(0, round($subtotal - $discount, 2));
        $tax  = $this->refreshAppliedTaxAmounts($base);

        $this->update([
            'subtotal'        => $subtotal,
            'discount_amount' => $discount,
            'tax_amount'      => $tax,
            'total'           => $base + $tax,
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

    public function isDraft(): bool
    {
        return $this->status === WorkOrderStatus::Draft;
    }

    public function isEditable(): bool
    {
        return $this->status instanceof WorkOrderStatus
            ? ! $this->status->isTerminal()
            : true;
    }

    public function canManageAssociatedDocuments(): bool
    {
        return $this->status instanceof WorkOrderStatus
            ? $this->status->allowsAssociatedDocuments()
            : true;
    }

    public function canReceiveRemission(): bool
    {
        return $this->status instanceof WorkOrderStatus
            ? $this->status->canReceiveRemission()
            : true;
    }

    public function canChangeStatus(): bool
    {
        return $this->isEditable() && ! $this->isDraft();
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            WorkOrderStatus::Draft => 'amber',
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
