<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cada plan de suscripción pasa a tener un producto que lo respalda.
 *
 * VISERVICE le factura a los comercios que se suscriben, y para emitir esa
 * factura el plan tiene que existir como algo vendible. En vez de inventar un
 * camino aparte, cada plan se refleja como un producto del negocio dueño de la
 * plataforma y se factura como cualquier otra venta.
 *
 * Son productos ocultos: «products.is_subscription_plan» los marca para que no
 * asomen en el catálogo ni en los selectores de ítems de OT y cotizaciones,
 * donde no tendrían ningún sentido.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_subscription_plan')->default(false)->after('status')
                ->comment('Respalda a un plan de suscripción: no se muestra en el catálogo');

            $table->index(['business_id', 'is_subscription_plan'], 'products_business_plan_idx');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('id')
                ->constrained('products')->nullOnDelete()
                ->comment('Producto con el que se factura este plan');
        });

        $this->backfillPlanProducts();
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_business_plan_idx');
            $table->dropColumn('is_subscription_plan');
        });
    }

    /** Le crea su producto a los planes que ya existían. */
    private function backfillPlanProducts(): void
    {
        $business_id = (int) config('subscriptions.owner_business_id');

        if (! DB::table('businesses')->where('id', $business_id)->exists()) {
            return;
        }

        $type_id = DB::table('product_types')
            ->where('name', config('subscriptions.plan_product.type_name'))
            ->where('general', true)
            ->value('id');

        $prefix = (string) config('subscriptions.plan_product.sku_prefix');
        $now = now();

        $plans = DB::table('subscription_plans')
            ->whereNull('product_id')
            ->orderBy('id')
            ->get();

        foreach ($plans as $plan) {
            $sku = $prefix.str_pad((string) $plan->id, 4, '0', STR_PAD_LEFT);

            // Si el SKU ya estuviera ocupado se deja que el plan quede sin
            // producto: es preferible a pisar un producto real del negocio.
            $taken = DB::table('products')
                ->where('business_id', $business_id)
                ->where('sku', $sku)
                ->whereNull('deleted_at')
                ->exists();

            if ($taken) {
                continue;
            }

            $product_id = DB::table('products')->insertGetId([
                'business_id'          => $business_id,
                'step'                 => 4,
                'final_step'           => 4,
                'product_type_id'      => $type_id,
                'sku'                  => $sku,
                'name'                 => $plan->name,
                'description'          => $plan->description,
                'cost_price'           => 0,
                'profit_percentage'    => 0,
                'sale_price'           => $plan->monthly_price,
                'track_inventory'      => false,
                'status'               => $plan->is_active,
                'is_subscription_plan' => true,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

            DB::table('subscription_plans')
                ->where('id', $plan->id)
                ->update(['product_id' => $product_id, 'updated_at' => $now]);
        }
    }
};
