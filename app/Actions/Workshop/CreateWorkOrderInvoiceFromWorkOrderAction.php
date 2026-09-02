<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
use App\Enums\WorkOrderStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderInvoice;
use App\Models\WorkOrderInvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateWorkOrderInvoiceFromWorkOrderAction
{
    use AsAction;

    public function handle(WorkOrder $work_order): WorkOrderInvoice
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);

        $work_order = WorkOrder::query()
            ->forAuthUser()
            ->with('items')
            ->findOrFail($work_order->id);

        if ($work_order->status !== WorkOrderStatus::Completed) {
            throw ValidationException::withMessages([
                'invoice' => 'Solo se puede facturar una OT en estado Finalizada.',
            ]);
        }

        $has_active_invoice = $work_order->invoices()
            ->whereIn('status', ['pendiente', 'pagada'])
            ->exists();

        if ($has_active_invoice) {
            throw ValidationException::withMessages([
                'invoice' => 'Esta OT ya tiene una factura activa.',
            ]);
        }

        return DB::transaction(function () use ($work_order) {
            $work_order->recalculateTotals();
            $work_order->refresh();

            $invoice = WorkOrderInvoice::query()->create([
                'business_id'    => $work_order->business_id,
                'work_order_id'  => $work_order->id,
                'reference'      => WorkOrderInvoice::generateReference($work_order->business_id),
                'subtotal'       => $work_order->subtotal,
                'tax_percentage' => $work_order->tax_percentage,
                'tax_amount'     => $work_order->tax_amount,
                'total'          => $work_order->total,
                'status'         => 'pendiente',
                'due_date'       => now()->addDays(15)->toDateString(),
                'created_by'     => auth()->id(),
            ]);

            foreach ($work_order->items as $item) {
                WorkOrderInvoiceItem::query()->create([
                    'work_order_invoice_id' => $invoice->id,
                    'work_order_item_id'      => $item->id,
                    'quantity'                => $item->quantity,
                    'quantity_complete'       => $item->quantity_complete,
                    'quantity_canceled'       => $item->quantity_canceled,
                ]);
            }

            $invoice = $invoice->fresh(['items.workOrderItem']);

            LogUserHistoricalAction::run(
                action: 'created',
                module: 'workshop.work-orders',
                description: "Generó la factura {$invoice->reference} desde la OT {$work_order->reference}",
                subject: $work_order,
                subject_label: $work_order->reference,
                properties: [
                    'invoice_id'        => $invoice->id,
                    'invoice_reference' => $invoice->reference,
                    'total'             => $invoice->total,
                    'items_count'       => $invoice->items->count(),
                ],
                business_id: (int) $work_order->business_id,
            );

            return $invoice;
        });
    }
}
