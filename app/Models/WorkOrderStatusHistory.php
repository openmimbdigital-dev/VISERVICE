<?php

namespace App\Models;

use App\Enums\WorkOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un paso de la línea de tiempo de estados de una orden de trabajo.
 *
 * La tabla es de solo escritura: cada cambio agrega una fila y ninguna se
 * modifica después, por eso no lleva «updated_at».
 */
class WorkOrderStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'business_id',
        'work_order_id',
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
            'from_status' => WorkOrderStatus::class,
            'to_status' => WorkOrderStatus::class,
            'duration_seconds' => 'integer',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Registro de apertura de la OT: no viene de ningún estado anterior. */
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
        return $this->from_status?->label();
    }

    public function toLabel(): string
    {
        return $this->to_status->label();
    }

    /** Tiempo que la OT permaneció en el estado anterior, en texto legible. */
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
}
