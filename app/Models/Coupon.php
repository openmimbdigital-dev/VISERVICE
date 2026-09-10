<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusinessTenant;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use BelongsToBusinessTenant;
    use SoftDeletes;

    public const DISCOUNT_PERCENTAGE = 'percentage';

    public const DISCOUNT_AMOUNT = 'amount';

    protected $fillable = [
        'business_id', 'code', 'name', 'discount_type', 'discount_value',
        'min_order_amount', 'starts_at', 'ends_at', 'max_uses', 'active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'discount_value'   => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'starts_at'        => 'date',
            'ends_at'          => 'date',
            'max_uses'         => 'integer',
            'active'           => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    /** El código siempre se guarda y se compara en mayúsculas y sin espacios. */
    public static function normalizeCode(?string $code): string
    {
        return mb_strtoupper(trim((string) $code));
    }

    public function setCodeAttribute(?string $value): void
    {
        $this->attributes['code'] = self::normalizeCode($value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Cuántas OT lo están usando. Se cuenta en vivo en lugar de llevar un
     * contador: así no se desfasa cuando una OT se elimina o cambia de cupón.
     */
    public function usesCount(): int
    {
        if (array_key_exists('work_orders_count', $this->attributes)) {
            return (int) $this->attributes['work_orders_count'];
        }

        return $this->workOrders()->count();
    }

    public function remainingUses(): ?int
    {
        return $this->max_uses === null
            ? null
            : max(0, $this->max_uses - $this->usesCount());
    }

    public function hasStarted(?CarbonInterface $moment = null): bool
    {
        return $this->starts_at === null
            || $this->starts_at->lte(($moment ?? now())->endOfDay());
    }

    public function hasExpired(?CarbonInterface $moment = null): bool
    {
        return $this->ends_at !== null
            && $this->ends_at->lt(($moment ?? now())->startOfDay());
    }

    /**
     * Motivo por el que el cupón no se puede usar, o null si sí se puede.
     *
     * Se devuelve el mensaje en vez de un booleano para poder decirle al usuario
     * exactamente qué pasa («venció», «alcanzó el límite»...).
     *
     * @param  int|null  $excluded_work_order_id  OT que ya lo tenía aplicado: no
     *                                            debe contar contra su propio límite.
     */
    public function unavailableReason(float $subtotal = 0, ?int $excluded_work_order_id = null): ?string
    {
        if (! $this->active) {
            return 'El cupón está inactivo.';
        }

        if (! $this->hasStarted()) {
            return 'El cupón todavía no está vigente (inicia el '.$this->starts_at->format('d/m/Y').').';
        }

        if ($this->hasExpired()) {
            return 'El cupón venció el '.$this->ends_at->format('d/m/Y').'.';
        }

        if ($this->max_uses !== null) {
            $uses = $this->workOrders()
                ->when($excluded_work_order_id, fn ($query) => $query->whereKeyNot($excluded_work_order_id))
                ->count();

            if ($uses >= $this->max_uses) {
                return 'El cupón alcanzó su límite de usos.';
            }
        }

        if ($this->min_order_amount !== null && $subtotal < (float) $this->min_order_amount) {
            return 'El cupón aplica desde '.col_money($this->min_order_amount).' de subtotal.';
        }

        return null;
    }

    /** Valor descontado sobre un subtotal, sin pasarse del subtotal mismo. */
    public function discountOn(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        $discount = $this->discount_type === self::DISCOUNT_PERCENTAGE
            ? $subtotal * (float) $this->discount_value / 100
            : (float) $this->discount_value;

        return round(min($subtotal, max(0, $discount)), 2);
    }

    /** Etiqueta corta del descuento para listados y chips. */
    public function discountLabel(): string
    {
        return $this->discount_type === self::DISCOUNT_PERCENTAGE
            ? rtrim(rtrim(number_format((float) $this->discount_value, 2, '.', ''), '0'), '.').'%'
            : col_money($this->discount_value);
    }
}
