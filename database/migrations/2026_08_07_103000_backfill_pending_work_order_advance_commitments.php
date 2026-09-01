<?php

use App\Models\WorkOrder;
use App\Models\WorkOrderPayment;
use Illuminate\Database\Migrations\Migration;

/**
 * Crea el registro pending de seguimiento para OTs que ya tienen anticipo
 * definido pero aún no tienen fila de compromiso en work_order_payments.
 */
return new class extends Migration
{
    public function up(): void
    {
        WorkOrder::query()
            ->where('advance_amount', '>', 0)
            ->whereDoesntHave('payments', fn ($q) => $q->where('status', 'pending'))
            ->orderBy('id')
            ->each(function (WorkOrder $work_order) {
                WorkOrderPayment::query()->create([
                    'business_id' => $work_order->business_id,
                    'work_order_id' => $work_order->id,
                    'amount' => $work_order->advance_amount,
                    'percentage' => $work_order->advance_percentage,
                    'paid_at' => null,
                    'notes' => $work_order->quotation_id
                        ? 'Anticipo definido desde cotización'
                        : 'Anticipo definido al crear la OT',
                    'status' => 'pending',
                    'created_by' => $work_order->created_by,
                ]);
            });
    }

    public function down(): void
    {
        WorkOrderPayment::query()
            ->where('status', 'pending')
            ->whereIn('notes', [
                'Anticipo definido desde cotización',
                'Anticipo definido al crear la OT',
            ])
            ->forceDelete();
    }
};
