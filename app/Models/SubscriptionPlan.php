<?php

namespace App\Models;

use App\Actions\Subscriptions\SyncSubscriptionPlanProductAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'name',
        'description',
        'monthly_price',
        'features',
        'billing_cycles',
        'max_users',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'features' => 'array',
            'billing_cycles' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Producto con el que se factura el plan.
     *
     * Se salta el ocultamiento global de Product: aquí sí queremos verlo.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withoutGlobalScope('withoutSubscriptionPlans');
    }

    /**
     * El producto del plan se mantiene solo: crear, editar o eliminar un plan es
     * también crear, editar o eliminar su producto. Va en eventos del modelo y no
     * en la pantalla para que valga igual desde un seeder o desde tinker.
     */
    protected static function booted(): void
    {
        static::saved(function (self $plan) {
            SyncSubscriptionPlanProductAction::run($plan);
        });

        static::deleted(function (self $plan) {
            $product = $plan->product()->withTrashed()->first();

            if (! $product) {
                return;
            }

            $plan->isForceDeleting() ? $product->forceDelete() : $product->delete();
        });

        static::restored(function (self $plan) {
            $plan->product()->withTrashed()->first()?->restore();
        });
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->whereIn('status', ['trial', 'active']);
    }

    /**
     * Devuelve los ciclos de facturación con sus meses y descuentos.
     * Usa los valores del campo billing_cycles o defaults estándar.
     */
    public function getCycles(): array
    {
        $defaults = [
            'monthly'    => ['label' => 'Mensual',     'months' => 1,  'discount' => 0],
            'quarterly'  => ['label' => 'Trimestral',  'months' => 3,  'discount' => 5],
            'semiannual' => ['label' => 'Semestral',   'months' => 6,  'discount' => 10],
            'annual'     => ['label' => 'Anual',       'months' => 12, 'discount' => 20],
        ];

        if (! $this->billing_cycles) {
            return $defaults;
        }

        foreach ($this->billing_cycles as $key => $data) {
            if (isset($defaults[$key])) {
                $defaults[$key] = array_merge($defaults[$key], $data);
            }
        }

        return $defaults;
    }

    /**
     * Calcula el precio total para un ciclo dado, aplicando descuento.
     */
    public function getPriceForCycle(string $cycle): array
    {
        $cycles = $this->getCycles();
        $data = $cycles[$cycle] ?? $cycles['monthly'];

        $months = $data['months'];
        $discount = $data['discount'];
        $base = $this->monthly_price * $months;
        $total = $base * (1 - $discount / 100);

        return [
            'months'   => $months,
            'discount' => $discount,
            'base'     => round($base, 2),
            'total'    => round($total, 2),
        ];
    }
}
