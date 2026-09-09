<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
use App\Enums\WorkOrderStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderInvoice;
use App\Models\WorkOrderInvoiceItem;
use App\Models\WorkOrderInvoiceStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateWorkOrderInvoiceFromWorkOrderAction
{
    use AsAction;

    /**
     * @param  bool|null  $bill_to_final_consumer  Si se omite, se hereda de la OT.
     */
    public function handle(WorkOrder $work_order, ?bool $bill_to_final_consumer = null): WorkOrderInvoice
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

        $to_final_consumer = $bill_to_final_consumer ?? (bool) $work_order->bill_to_final_consumer;

        return DB::transaction(function () use ($work_order, $to_final_consumer) {
            $work_order->recalculateTotals();
            $work_order->refresh();

            $invoice = WorkOrderInvoice::query()->create([
                'business_id'    => $work_order->business_id,
                'work_order_id'  => $work_order->id,
                'bill_to_final_consumer' => $to_final_consumer,
                'reference'      => WorkOrderInvoice::generateReference($work_order->business_id),
                'subtotal'       => $work_order->subtotal,
                'discount_amount' => $work_order->discount_amount,
                'coupon_code'    => $work_order->coupon_code,
                'tax_percentage' => $work_order->effectiveTaxPercentage(),
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

            RecordInvoiceStatusHistoryAction::run(
                invoice: $invoice,
                kind: WorkOrderInvoiceStatusHistory::KIND_BILLING,
                to_status: 'pendiente',
                metadata: ['origin' => 'work_order', 'work_order_reference' => $work_order->reference],
            );

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
