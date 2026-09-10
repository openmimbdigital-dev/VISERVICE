<?php

namespace App\Actions\Subscriptions;

use App\Models\Product;
use App\Models\ProductType;
use App\Models\SubscriptionPlan;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Mantiene al día el producto que respalda a un plan de suscripción.
 *
 * El plan es el dueño: su nombre, descripción, precio mensual y estado mandan
 * sobre el producto, que existe solo para poder facturarle la suscripción al
 * comercio como cualquier otra venta. Por eso el producto queda oculto del
 * catálogo y no se edita desde allí.
 */
class SyncSubscriptionPlanProductAction
{
    use AsAction;

    public function handle(SubscriptionPlan $plan): ?Product
    {
        $business_id = (int) config('subscriptions.owner_business_id');

        if ($business_id <= 0) {
            return null;
        }

        // Se busca incluyendo los eliminados: al restaurar un plan, Laravel dispara
        // «saved» antes que «restored», así que en este punto su producto todavía
        // está borrado. Sin esto se crearía uno duplicado y el original quedaría
        // huérfano.
        $product = $plan->product_id
            ? Product::query()->withSubscriptionPlans()->withTrashed()->find($plan->product_id)
            : null;

        $attributes = [
            'name'        => $plan->name,
            'description' => $plan->description,
            'sale_price'  => $plan->monthly_price,
            'status'      => (bool) $plan->is_active,
        ];

        if ($product) {
            if ($product->trashed() && ! $plan->trashed()) {
                $product->restore();
            }

            $product->forceFill($attributes)->save();

            return $product;
        }

        $product = Product::query()->create([
            ...$attributes,
            'business_id'          => $business_id,
            'product_type_id'      => $this->planProductTypeId(),
            'sku'                  => $this->availableSku($business_id, $plan),
            'cost_price'           => 0,
            'profit_percentage'    => 0,
            'track_inventory'      => false,
            'step'                 => Product::DEFAULT_FINAL_STEP,
            'final_step'           => Product::DEFAULT_FINAL_STEP,
            'is_subscription_plan' => true,
        ]);

        // saveQuietly: escribir el enlace no debe volver a disparar la sincronización.
        $plan->forceFill(['product_id' => $product->id])->saveQuietly();

        return $product;
    }

    private function planProductTypeId(): ?int
    {
        return ProductType::query()
            ->where('name', config('subscriptions.plan_product.type_name'))
            ->where('general', true)
            ->value('id');
    }

    /**
     * SKU libre para el plan. Normalmente es PLAN-0001; si ese estuviera ocupado
     * por un producto real del negocio, se busca el siguiente disponible en vez
     * de chocar contra el índice único.
     */
    private function availableSku(int $business_id, SubscriptionPlan $plan): string
    {
        $prefix = (string) config('subscriptions.plan_product.sku_prefix');
        $number = (int) $plan->id;

        do {
            $sku = $prefix.str_pad((string) $number, 4, '0', STR_PAD_LEFT);

            $taken = Product::query()
                ->withSubscriptionPlans()
                ->where('business_id', $business_id)
                ->where('sku', $sku)
                ->exists();

            $number++;
        } while ($taken);

        return $sku;
    }
}
