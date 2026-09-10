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
        'work_order_id',
        'work_order_invoice_id',
        'invoice_number',
        'amount',
        'status',
        'billing_period_start',
        'billing_period_end',
        'due_date',
        'paid_at',
        'payment_method',
        'payment_reference',
        'bank_account_id',
        'payment_proof',
        'bold_payment_link',
        'bold_link_url',
        'bold_reference',
        'bold_status',
        'bold_payment_id',
        'bold_payment_method',
        'bold_link_created_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'bold_link_created_at' => 'datetime',
            'amount'               => 'decimal:2',
            'billing_period_start' => 'date',
            'billing_period_end'   => 'date',
            'due_date'             => 'date',
            'paid_at'              => 'datetime',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    /**
     * ¿Hay un link de Bold por el que todavía se pueda pagar?
     *
     * Un link pagado o vencido ya no sirve, y uno en proceso tampoco conviene
     * reemplazarlo: el comercio podría estar justo en el checkout.
     */
    public function hasUsableBoldLink(): bool
    {
        if (blank($this->bold_payment_link) || blank($this->bold_link_url)) {
            return false;
        }

        return in_array($this->bold_status, ['ACTIVE', 'PROCESSING'], true);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** OT generada al cobrar este período. */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /** Factura emitida por este cobro, la que va a la DIAN. */
    public function workOrderInvoice(): BelongsTo
    {
        return $this->belongsTo(WorkOrderInvoice::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
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
        $year  = now()->format('Y');
        $month = now()->format('m');
        $last  = self::whereYear('created_at', $year)->count() + 1;

        return "INV-{$year}{$month}-" . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}
