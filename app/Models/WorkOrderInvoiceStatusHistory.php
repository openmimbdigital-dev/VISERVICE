<?php

namespace App\Models;

use App\Enums\ElectronicInvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un paso de la línea de tiempo de una factura de OT.
 *
 * Cada fila pertenece a uno de dos ejes independientes: el cobro y la emisión
 * electrónica. Se guardan juntos para poder mostrarlos en orden, pero nunca se
 * mezclan: una factura enviada a la DIAN sigue estando pendiente de pago.
 */
class WorkOrderInvoiceStatusHistory extends Model
{
    public const KIND_BILLING = 'billing';

    public const KIND_EMISSION = 'emission';

    public $timestamps = false;

    protected $fillable = [
        'business_id',
        'work_order_invoice_id',
        'kind',
        'from_status',
        'to_status',
        'comment',
        'user_id',
        'user_name',
        'duration_seconds',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'metadata'         => 'array',
            'created_at'       => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(WorkOrderInvoice::class, 'work_order_invoice_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isEmission(): bool
    {
        return $this->kind === self::KIND_EMISSION;
    }

    /** Apertura del eje: no viene de ningún estado anterior. */
    public function isOpening(): bool
    {
        return $this->from_status === null;
    }

    /** Fila reconstruida por la migración, no capturada en vivo. */
    public function isBackfilled(): bool
    {
        return (bool) ($this->metadata['backfilled'] ?? false);
    }

    public function fromLabel(): ?string
    {
        return $this->from_status === null ? null : $this->labelFor($this->from_status);
    }

    public function toLabel(): string
    {
        return $this->labelFor($this->to_status);
    }

    /** Tiempo que la factura permaneció en el estado anterior de su eje. */
    public function durationLabel(): ?string
    {
        $seconds = $this->duration_seconds;

        if ($seconds === null) {
            return null;
        }

        if ($seconds < 60) {
            return 'menos de 1 min';
        }

        $minutes = intdiv($seconds, 60);

        if ($minutes < 60) {
            return $minutes.' min';
        }

        $hours = intdiv($minutes, 60);

        if ($hours < 24) {
            $rest = $minutes % 60;

            return $rest > 0 ? "{$hours} h {$rest} min" : "{$hours} h";
        }

        $days = intdiv($hours, 24);
        $rest_hours = $hours % 24;

        return $rest_hours > 0 ? "{$days} d {$rest_hours} h" : "{$days} d";
    }

    public function badgeClass(): string
    {
        if ($this->isEmission()) {
            return ElectronicInvoiceStatus::tryFrom($this->to_status)?->badgeClass()
                ?? 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';
        }

        return match ($this->to_status) {
            'pendiente' => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20',
            'pagada'    => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
            'vencida'   => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
            'anulada'   => 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20',
            default     => 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20',
        };
    }

    public function dotClass(): string
    {
        if ($this->isEmission()) {
            return match (ElectronicInvoiceStatus::tryFrom($this->to_status)) {
                ElectronicInvoiceStatus::Pending  => 'bg-slate-400',
                ElectronicInvoiceStatus::Sent     => 'bg-blue-500',
                ElectronicInvoiceStatus::Accepted => 'bg-emerald-500',
                ElectronicInvoiceStatus::Rejected => 'bg-rose-500',
                ElectronicInvoiceStatus::Error    => 'bg-amber-500',
                default                           => 'bg-slate-300',
            };
        }

        return match ($this->to_status) {
            'pendiente' => 'bg-yellow-400',
            'pagada'    => 'bg-emerald-500',
            'vencida'   => 'bg-rose-500',
            'anulada'   => 'bg-slate-400',
            default     => 'bg-slate-300',
        };
    }

    private function labelFor(string $status): string
    {
        if ($this->isEmission()) {
            return ElectronicInvoiceStatus::tryFrom($status)?->label() ?? $status;
        }

        return match ($status) {
            'pendiente' => 'Pendiente de pago',
            'pagada'    => 'Pagada',
            'vencida'   => 'Vencida',
            'anulada'   => 'Anulada',
            default     => $status,
        };
    }
}
