<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un pago de suscripción, con el mismo detalle venga del cobro manual o de la
 * pasarela. Es el registro que responde «¿cómo pagó este comercio?».
 */
class SubscriptionPayment extends Model
{
    public const CHANNEL_MANUAL = 'manual';

    public const CHANNEL_ONLINE = 'online';

    protected $fillable = [
        'business_id',
        'subscription_id',
        'subscription_invoice_id',
        'channel',
        'gateway',
        'method',
        'method_label',
        'amount',
        'currency',
        'tip',
        'taxes',
        'gateway_payment_id',
        'gateway_reference',
        'gateway_code',
        'gateway_source',
        'payer_email',
        'payment_reference',
        'paid_at',
        'metadata',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'   => 'decimal:2',
            'tip'      => 'decimal:2',
            'taxes'    => 'array',
            'metadata' => 'array',
            'paid_at'  => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionInvoice::class, 'subscription_invoice_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOnline(): bool
    {
        return $this->channel === self::CHANNEL_ONLINE;
    }

    /** Fila reconstruida por la migración, sin el detalle de la pasarela. */
    public function isBackfilled(): bool
    {
        return (bool) ($this->metadata['backfilled'] ?? false);
    }

    /** Nombre del medio de pago como se le muestra a una persona. */
    public function methodLabel(): string
    {
        if (filled($this->method_label)) {
            return $this->method_label;
        }

        return match ($this->method) {
            'PSE'               => 'PSE',
            'NEQUI'             => 'Nequi',
            'BOTON_BANCOLOMBIA' => 'Botón Bancolombia',
            'CARD', 'CARD_WEB'  => 'Tarjeta',
            'QR'                => 'Código QR',
            'transfer'          => 'Transferencia bancaria',
            'cash'              => 'Efectivo',
            'online'            => 'Pago en línea',
            default             => (string) ($this->method ?: 'Sin especificar'),
        };
    }

    public function channelLabel(): string
    {
        return $this->isOnline()
            ? 'En línea'.($this->gateway ? ' · '.ucfirst($this->gateway) : '')
            : 'Manual';
    }
}
