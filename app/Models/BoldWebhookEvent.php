<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bitácora de las notificaciones que envía Bold.
 *
 * Existe sobre todo por la idempotencia: Bold reintenta cada evento hasta cinco
 * veces si no recibe un 200, así que el mismo pago puede llegar varias veces y
 * «event_id» es lo que permite procesarlo una sola. De paso queda el rastro de
 * qué llegó y qué se hizo, que es lo primero que uno quiere ver cuando un cobro
 * no cuadra.
 */
class BoldWebhookEvent extends Model
{
    public const TYPE_SALE_APPROVED = 'SALE_APPROVED';

    public const TYPE_SALE_REJECTED = 'SALE_REJECTED';

    public const TYPE_VOID_APPROVED = 'VOID_APPROVED';

    protected $fillable = [
        'event_id',
        'type',
        'reference',
        'payment_id',
        'subscription_invoice_id',
        'signature_valid',
        'processed',
        'result',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'signature_valid' => 'boolean',
            'processed'       => 'boolean',
            'payload'         => 'array',
        ];
    }

    public function subscriptionInvoice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionInvoice::class);
    }

    public function isApprovedSale(): bool
    {
        return $this->type === self::TYPE_SALE_APPROVED;
    }
}
