<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'work_order_id', 'bill_to_final_consumer', 'reference',
        'subtotal', 'discount_amount', 'coupon_code',
        'tax_percentage', 'tax_amount', 'total',
        'status', 'due_date', 'paid_at',
        'payment_method', 'payment_reference', 'dian_payment_means_code',
        'bold_payment_link', 'bold_link_url', 'bold_reference', 'bold_status',
        'bold_payment_id', 'bold_payment_method', 'bold_account', 'bold_link_created_at',
        'notes', 'created_by',
    ];

    /**
     * ¿Hay un link de Bold por el que todavía se pueda pagar esta factura?
     */
    public function hasUsableBoldLink(): bool
    {
        if (blank($this->bold_payment_link) || blank($this->bold_link_url)) {
            return false;
        }

        return in_array($this->bold_status, ['ACTIVE', 'PROCESSING'], true);
    }

    /**
     * Factura a la que corresponde una referencia de Bold.
     *
     * Igual que en las suscripciones: primero la coincidencia exacta y, si no
     * aparece, por el prefijo, porque un link viejo lleva un sufijo distinto al
     * último que guardamos.
     */
    public static function findByBoldReference(string $reference): ?self
    {
        $reference = trim($reference);

        if ($reference === '') {
            return null;
        }

        $invoice = static::query()->where('bold_reference', $reference)->first();

        if ($invoice) {
            return $invoice;
        }

        $separator = mb_strrpos($reference, '-');

        if ($separator === false) {
            return null;
        }

        return static::query()
            ->where('reference', mb_substr($reference, 0, $separator))
            ->first();
    }

    protected function casts(): array
    {
        return [
            'bill_to_final_consumer' => 'boolean',
            'bold_link_created_at'   => 'datetime',
            'due_date'        => 'date',
            'paid_at'         => 'datetime',
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_percentage'  => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total'           => 'decimal:2',
        ];
    }

    /** Base gravable: el subtotal ya descontado el cupón de la OT. */
    public function taxableBase(): float
    {
        return max(0, round((float) $this->subtotal - (float) $this->discount_amount, 2));
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderInvoiceItem::class);
    }

    public function electronicInvoice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ElectronicInvoice::class);
    }

    /** Línea de tiempo de cobro y emisión, del paso más antiguo al más reciente. */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(WorkOrderInvoiceStatusHistory::class)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Etiqueta del estado de cobro.
     *
     * Dice «de pago» a propósito: una factura ya emitida ante la DIAN sigue
     * estando pendiente mientras no la paguen, y sin la aclaración se leía como
     * si no hubiera pasado nada. El estado ante la DIAN va aparte, en
     * ElectronicInvoiceStatus.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pendiente' => 'Pendiente de pago',
            'pagada'    => 'Pagada',
            'vencida'   => 'Vencida',
            'anulada'   => 'Anulada',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pendiente' => 'yellow',
            'pagada'    => 'green',
            'vencida'   => 'red',
            'anulada'   => 'gray',
            default     => 'gray',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pendiente' => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20',
            'pagada'    => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
            'vencida'   => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
            'anulada'   => 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20',
            default     => 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20',
        };
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

    public static function generateReference(int $businessId): string
    {
        $prefix = 'FAC-' . now()->format('Ym') . '-';
        $last = static::withTrashed()
            ->where('business_id', $businessId)
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
