<?php

use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Database\Migrations\Migration;

/**
 * Recalcula las líneas con descuento con la regla nueva: el descuento se aplica
 * al precio unitario y el total de la línea es cantidad × ese precio.
 *
 * Antes el descuento se restaba del total de la línea, lo que producía totales
 * que no son divisibles por la cantidad. La facturación electrónica exige que
 * total = cantidad × precio y rechazaba el documento con [FAV06].
 */
return new class extends Migration
{
    public function up(): void
    {
        $work_order_ids = [];

        WorkOrderItem::query()
            ->where('discount_percentage', '>', 0)
            ->chunkById(200, function ($items) use (&$work_order_ids) {
                foreach ($items as $item) {
                    $subtotal = $item->calculateSubtotal();

                    if (abs((float) $item->subtotal - $subtotal) < 0.005) {
                        continue;
                    }

                    $item->forceFill(['subtotal' => $subtotal])->save();
                    $work_order_ids[$item->work_order_id] = true;
                }
            });

        WorkOrder::query()
            ->whereIn('id', array_keys($work_order_ids))
            ->each(fn (WorkOrder $work_order) => $work_order->recalculateTotals());
    }

    public function down(): void
    {
        // Los importes anteriores no se pueden reconstruir sin volver a introducir
        // el defecto que esta migración corrige.
    }
};
