<?php

namespace App\Models;

use App\Enums\QuotationStatus;
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

class Quotation extends Model
{
    use HasAppliedTaxes;
    use HasWizardProgress;
    use SoftDeletes;

    public const DEFAULT_FINAL_STEP = 3;

    public const PDF_CATEGORY_GROUPS = [
        'mano_obra'    => ['Mano de Obra'],
        'repuestos'    => ['Repuestos'],
        'lubricantes'  => ['Lubricantes y fluidos'],
        'otros'        => [],
    ];

    protected $fillable = [
        'business_id', 'step', 'final_step', 'client_id', 'quotation_service_type_id',
        'business_payment_method_id', 'business_bank_account_id',
        'reference', 'status', 'diagnosis', 'hours_entry',
        'validity_days', 'valid_until', 'execution_time',
        'coupon_id', 'coupon_code', 'subtotal', 'discount_amount',
        'tax_amount', 'total',
        'advance_percentage', 'advance_amount',
        'notes', 'observations', 'reject_reason',
        'approved_by_name', 'approved_by_position', 'approved_signature',
        'created_by', 'issued_at', 'sent_at', 'accepted_at', 'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'step'            => 'integer',
            'final_step'      => 'integer',
            'status'          => QuotationStatus::class,
            'valid_until'     => 'date',
            'issued_at'       => 'datetime',
            'sent_at'         => 'datetime',
            'accepted_at'     => 'datetime',
            'rejected_at'     => 'datetime',
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total'           => 'decimal:2',
            'advance_percentage' => 'decimal:2',
            'advance_amount'  => 'decimal:2',
            'validity_days'   => 'integer',
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
        return $this->belongsToMany(Equipment::class, 'equipment_quotation')
            ->withTimestamps();
    }

    public function quotationServiceType(): BelongsTo
    {
        return $this->belongsTo(QuotationServiceType::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(BusinessPaymentMethod::class, 'business_payment_method_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BusinessBankAccount::class, 'business_bank_account_id');
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
        return $this->hasMany(QuotationItem::class);
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function equipment_historicals(): MorphMany
    {
        return $this->morphMany(EquipmentHistorical::class, 'subject')->orderByDesc('created_at');
    }

    public function scopeForAuthUser($query)
    {
        $user = auth()->user();

        // El superAdmin no es un comodín sobre los datos de los comercios: ve los
        // de su propio negocio, igual que cualquiera. Para mirar los de otro entra
        // como un usuario suyo (ver App\Support\Impersonation), y así lo hace con
        // sus permisos y su alcance en vez de con reglas especiales.
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
                    ->from('quotation_items')
                    ->whereColumn('quotation_items.quotation_id', "{$table}.id");
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

    public function isDraft(): bool
    {
        return $this->status === QuotationStatus::Draft;
    }

    public function getHoursEntryFormattedAttribute(): ?string
    {
        if ($this->hours_entry === null || $this->hours_entry === '') {
            return null;
        }

        $value = (string) $this->hours_entry;

        return strlen($value) >= 5 ? substr($value, 0, 5) : $value;
    }

    public function itemsDiscountAmount(): float
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();

        return round($items->sum(fn (QuotationItem $item) => $item->discountAmount()), 2);
    }

    public function couponDiscountAmount(): float
    {
        $stored = round((float) $this->discount_amount, 2);

        if ($stored > 0) {
            return $stored;
        }

        if (! $this->coupon_id) {
            return 0.0;
        }

        $coupon = $this->relationLoaded('coupon') ? $this->coupon : Coupon::find($this->coupon_id);

        return $coupon ? $coupon->discountOn((float) $this->subtotal) : 0.0;
    }

    public function taxableBase(): float
    {
        return max(0, round((float) $this->subtotal - (float) $this->discount_amount, 2));
    }

    public function appliedTaxableBase(): float
    {
        return $this->taxableBase();
    }

    public function recalculateTotals(): void
    {
        $subtotal = round((float) $this->items()->sum('subtotal'), 2);

        $coupon = $this->coupon_id ? Coupon::find($this->coupon_id) : null;

        $discount = match (true) {
            (bool) $coupon            => $coupon->discountOn($subtotal),
            (bool) $this->coupon_code => min($subtotal, round((float) $this->discount_amount, 2)),
            default                   => 0.0,
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

    /** @return array<string, float> */
    public function subtotalsByPdfCategory(): array
    {
        $items = $this->relationLoaded('items')
            ? $this->items->loadMissing('productCategory')
            : $this->items()->with('productCategory')->get();

        $groups = [
            'mano_obra'   => 0.0,
            'repuestos'   => 0.0,
            'lubricantes' => 0.0,
            'otros'       => 0.0,
        ];

        foreach ($items as $item) {
            $category_name = $item->productCategory?->name ?? '';
            $amount        = (float) $item->subtotal;

            if (in_array($category_name, self::PDF_CATEGORY_GROUPS['mano_obra'], true)) {
                $groups['mano_obra'] += $amount;
            } elseif (in_array($category_name, self::PDF_CATEGORY_GROUPS['repuestos'], true)) {
                $groups['repuestos'] += $amount;
            } elseif (in_array($category_name, self::PDF_CATEGORY_GROUPS['lubricantes'], true)) {
                $groups['lubricantes'] += $amount;
            } else {
                $groups['otros'] += $amount;
            }
        }

        return array_map(fn ($v) => round($v, 2), $groups);
    }

    public function syncValidUntil(): void
    {
        if ($this->validity_days > 0) {
            $base = $this->issued_at ?? $this->created_at ?? now();
            $this->valid_until = $base->copy()->addDays($this->validity_days)->toDateString();
        }
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->relationLoaded('statusDefinition') && $this->statusDefinition) {
            return $this->statusDefinition->label;
        }

        return $this->status instanceof QuotationStatus
            ? $this->status->label()
            : (string) $this->status;
    }

    public function isRejected(): bool
    {
        return $this->status === QuotationStatus::Rejected;
    }

    public function isAccepted(): bool
    {
        return $this->status === QuotationStatus::Accepted;
    }

    public function isEditable(): bool
    {
        return ! $this->isRejected() && ! $this->isAccepted();
    }

    public function canBeDeleted(): bool
    {
        return ! $this->isRejected() && ! $this->isAccepted();
    }

    public function canChangeStatus(): bool
    {
        return ! $this->isRejected() && ! $this->isDraft();
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            QuotationStatus::Draft => 'amber',
            QuotationStatus::Created => 'gray',
            QuotationStatus::Sent => 'blue',
            QuotationStatus::Accepted => 'green',
            QuotationStatus::Rejected => 'red',
            QuotationStatus::Expired => 'orange',
            default => 'gray',
        };
    }

    public static function generateReference(int $business_id): string
    {
        $prefix = 'COT-' . now()->format('Ym') . '-';
        $last = static::withTrashed()
            ->where('business_id', $business_id)
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
