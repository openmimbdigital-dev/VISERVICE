<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bitácora de las notificaciones que envía Bold.
 *
 * Guarda cada entrega completa: lo que llegó —cuerpo crudo, cabeceras, IP,
 * firma— y lo que contestamos, con cuánto tardamos. Aquí se mueve dinero, así
 * que el criterio es que la fila baste por sí sola para reconstruir qué pasó, sin
 * depender de logs que rotan ni de volver a preguntarle a nadie.
 *
 * Los reintentos también quedan: Bold reenvía hasta cinco veces si no recibe un
 * 200, y cada intento es una fila. «event_id» ya no es único por eso; lo que
 * evita procesar dos veces es que exista otra entrega del mismo evento ya
 * marcada como procesada.
 */
class BoldWebhookEvent extends Model
{
    public const TYPE_SALE_APPROVED = 'SALE_APPROVED';

    public const TYPE_SALE_REJECTED = 'SALE_REJECTED';

    public const TYPE_VOID_APPROVED = 'VOID_APPROVED';

    public const TYPE_VOID_REJECTED = 'VOID_REJECTED';

    protected $fillable = [
        'event_id',
        'type',
        'reference',
        'payment_id',
        'amount',
        'currency',
        'payment_method',
        'event_time',
        'subscription_invoice_id',
        'work_order_invoice_id',
        'signature_valid',
        'signature',
        'signed_with',
        'processed',
        'result',
        'payload',
        'received_at',
        'ip',
        'user_agent',
        'http_method',
        'url',
        'headers',
        'raw_body',
        'body_bytes',
        'response_status',
        'response_body',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'signature_valid' => 'boolean',
            'processed'       => 'boolean',
            'payload'         => 'array',
            'headers'         => 'array',
            'received_at'     => 'datetime',
            'amount'          => 'decimal:2',
        ];
    }

    public function subscriptionInvoice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionInvoice::class);
    }

    /** Factura de taller, cuando el aviso es de un cobro a un cliente del negocio. */
    public function workOrderInvoice(): BelongsTo
    {
        return $this->belongsTo(WorkOrderInvoice::class);
    }

    public function isApprovedSale(): bool
    {
        return $this->type === self::TYPE_SALE_APPROVED;
    }

    /** ¿El cuerpo venía en un JSON que se pudiera interpretar? */
    public function isReadable(): bool
    {
        return is_array($this->payload);
    }

    /**
     * ¿Respondimos dentro de los dos segundos que Bold exige antes de dar la
     * entrega por fallida y reintentar?
     */
    public function respondedInTime(): bool
    {
        return $this->duration_ms !== null && $this->duration_ms < 2000;
    }
}
