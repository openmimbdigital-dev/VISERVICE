<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cada consulta que le hacemos a Bold sobre el estado de un cobro.
 *
 * Es la contraparte de la bitácora del webhook: si un cobro se dio por pagado
 * sin que Bold nos avisara, fue por una de estas consultas, y aquí está la
 * respuesta en la que nos basamos. Las consultas que decidieron no confirmar
 * también quedan: explican por qué un pago sigue pendiente.
 */
class BoldStatusCheck extends Model
{
    public const ORIGIN_CALLBACK = 'callback';

    public const ORIGIN_SCHEDULED = 'tarea';

    public const ORIGIN_MANUAL = 'manual';

    protected $fillable = [
        'subscription_invoice_id',
        'payment_link',
        'reference',
        'origin',
        'http_status',
        'reported_status',
        'reported_amount',
        'reported_currency',
        'transaction_id',
        'payment_method',
        'is_sandbox',
        'raw_response',
        'error',
        'confirmed',
        'decision',
        'duration_ms',
        'user_id',
        'ip',
    ];

    protected function casts(): array
    {
        return [
            'is_sandbox'      => 'boolean',
            'confirmed'       => 'boolean',
            'reported_amount' => 'decimal:2',
        ];
    }

    public function subscriptionInvoice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionInvoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
