<?php

namespace App\Actions\Subscriptions;

use App\Actions\Workshop\CreateWorkOrderInvoiceFromWorkOrderAction;
use App\Actions\Workshop\RecordWorkOrderStatusHistoryAction;
use App\Actions\Workshop\UpdateWorkOrderStatusAction;
use App\Enums\WorkOrderStatus;
use App\Models\SubscriptionInvoice;
use App\Models\WorkOrder;
use App\Models\WorkOrderInvoice;
use App\Models\WorkOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Convierte el cobro de una suscripción en una OT facturable del negocio dueño.
 *
 * VISERVICE le factura a sus comercios por la misma tubería que usa el taller
 * —OT, factura y emisión ante la DIAN— en vez de construir un camino aparte.
 * La OT nace ya finalizada: no hay trabajo que ejecutar, solo algo que cobrar.
 *
 * Los ítems se escriben aquí y no a través de CreateOrUpdateWorkOrderAction
 * porque esa acción valida el producto contra el catálogo visible, y los
 * productos de plan están ocultos justamente para que nadie los meta a mano en
 * una OT. Este es el único camino por el que deben entrar.
 */
class CreateSubscriptionWorkOrderAction
{
    use AsAction;

    public function handle(SubscriptionInvoice $subscription_invoice): ?WorkOrderInvoice
    {
        // Un cobro ya facturado no se vuelve a facturar.
        if ($subscription_invoice->work_order_invoice_id) {
            return $subscription_invoice->workOrderInvoice;
        }

        $subscription_invoice->loadMissing(['subscription.plan', 'business']);

        $subscription = $subscription_invoice->subscription;
        $plan = $subscription?->plan;
        $subscriber = $subscription_invoice->business;

        if (! $subscription || ! $plan || ! $subscriber) {
            return null;
        }

        $owner_business_id = (int) config('subscriptions.owner_business_id');

        // Nadie se factura a sí mismo. El negocio dueño puede tener una
        // suscripción de referencia, pero no genera documento.
        if ((int) $subscriber->id === $owner_business_id) {
            return null;
        }

        if ($owner_business_id <= 0 || ! $plan->product_id) {
            throw ValidationException::withMessages([
                'subscription' => 'El plan no tiene un producto asociado con el cual facturar.',
            ]);
        }

        $amount = round((float) $subscription_invoice->amount, 2);

        if ($amount <= 0) {
            return null;
        }

        $client = ResolveSubscriberClientAction::run($subscriber);

        $work_order = DB::transaction(function () use (
            $owner_business_id, $client, $plan, $subscription, $subscription_invoice, $amount
        ) {
            $work_order = WorkOrder::query()->create([
                'business_id'        => $owner_business_id,
                'client_id'          => $client->id,
                'reference'          => WorkOrder::generateReference($owner_business_id),
                'status'             => WorkOrderStatus::Created,
                'step'               => WorkOrder::DEFAULT_FINAL_STEP,
                'final_step'         => WorkOrder::DEFAULT_FINAL_STEP,
                'diagnosis'          => $this->periodLabel($subscription_invoice, $plan->name),
                'notes'              => "Suscripción #{$subscription->id} · cobro {$subscription_invoice->invoice_number}",
                'estimated_delivery' => now()->toDateString(),
                'created_by'         => auth()->id(),
            ]);

            WorkOrderItem::query()->create([
                'work_order_id'       => $work_order->id,
                'product_id'          => $plan->product_id,
                'product_type_id'     => $plan->product?->product_type_id,
                'description'         => $this->periodLabel($subscription_invoice, $plan->name),
                'quantity'            => 1,
                'quantity_complete'   => 1,
                'quantity_canceled'   => 0,
                'unit_price'          => $amount,
                'discount_percentage' => 0,
                'subtotal'            => WorkOrderItem::lineSubtotal(1, $amount, 0),
            ]);

            $work_order->recalculateTotals();

            RecordWorkOrderStatusHistoryAction::run(
                work_order: $work_order,
                to_status: WorkOrderStatus::Created,
                metadata: [
                    'origin'                  => 'subscription',
                    'subscription_id'         => $subscription->id,
                    'subscription_invoice_id' => $subscription_invoice->id,
                ],
            );

            return $work_order->fresh(['items', 'client']);
        });

        // Se cierra y se factura fuera de la transacción de creación para que cada
        // paso quede con su propio registro en la bitácora, igual que en el taller.
        $work_order = UpdateWorkOrderStatusAction::run(
            $work_order->id,
            WorkOrderStatus::Completed,
            'Cobro de suscripción registrado.',
        );

        $invoice = CreateWorkOrderInvoiceFromWorkOrderAction::run($work_order);

        $subscription_invoice->forceFill([
            'work_order_id'         => $work_order->id,
            'work_order_invoice_id' => $invoice->id,
        ])->save();

        return $invoice;
    }

    /** «Plan Profesional · 01/09/2026 - 30/09/2026» */
    private function periodLabel(SubscriptionInvoice $subscription_invoice, string $plan_name): string
    {
        $start = $subscription_invoice->billing_period_start;
        $end = $subscription_invoice->billing_period_end;

        if (! $start || ! $end) {
            return $plan_name;
        }

        return $plan_name.' · '.$start->format('d/m/Y').' - '.$end->format('d/m/Y');
    }
}
