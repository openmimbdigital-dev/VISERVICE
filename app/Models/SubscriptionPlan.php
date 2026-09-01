<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
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
