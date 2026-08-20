<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use SoftDeletes;

    public const PDF_CATEGORY_GROUPS = [
        'mano_obra'    => ['Mano de Obra'],
        'repuestos'    => ['Repuestos'],
        'lubricantes'  => ['Lubricantes y fluidos'],
        'otros'        => [],
    ];

    protected $fillable = [
        'business_id', 'client_id', 'quotation_service_type_id',
        'business_payment_method_id', 'business_bank_account_id',
        'reference', 'status', 'diagnosis', 'hours_entry',
        'validity_days', 'valid_until', 'execution_time',
        'subtotal', 'tax_percentage', 'tax_amount', 'total',
        'advance_percentage', 'advance_amount',
        'notes', 'observations', 'reject_reason',
        'approved_by_name', 'approved_by_position', 'approved_signature',
        'created_by', 'issued_at', 'sent_at', 'accepted_at', 'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'status'          => QuotationStatus::class,
            'valid_until'     => 'date',
            'issued_at'       => 'datetime',
            'sent_at'         => 'datetime',
            'accepted_at'     => 'datetime',
            'rejected_at'     => 'datetime',
            'subtotal'        => 'decimal:2',
            'tax_percentage'  => 'decimal:2',
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

    public function equipment_historicals(): MorphMany
    {
        return $this->morphMany(EquipmentHistorical::class, 'subject')->orderByDesc('created_at');
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

    public function getHoursEntryFormattedAttribute(): ?string
    {
        if ($this->hours_entry === null || $this->hours_entry === '') {
            return null;
        }

        $value = (string) $this->hours_entry;

        return strlen($value) >= 5 ? substr($value, 0, 5) : $value;
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
        return ! $this->isRejected();
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
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
