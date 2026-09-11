<?php

namespace App\Actions\Subscriptions;

use App\Models\Business;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Suscribe un negocio a un plan y le deja su primer cobro pendiente.
 *
 * La suscripción nace en «pending» y el cobro en «pending»: nada se da por
 * pagado aquí. Quien confirme el pago —el comercio pagando en línea, Bold
 * avisando, o alguien confirmando una transferencia— es el que activa la
 * suscripción y genera la orden con su factura.
 *
 * Vive aparte porque a esto se llega por dos caminos —el registro público y el
 * panel del superAdmin— y tienen que producir exactamente lo mismo.
 */
class CreateSubscriptionWithInvoiceAction
{
    use AsAction;

    public function handle(
        Business $business,
        SubscriptionPlan $plan,
        string $billing_cycle,
        string $payment_method,
        ?string $payment_proof_path = null,
        ?string $payment_reference = null,
        ?string $notes = null,
    ): SubscriptionInvoice {
        $price = $plan->getPriceForCycle($billing_cycle);

        $started_at = now()->toDateString();
        $ends_at = Carbon::now()->addMonths($price['months'])->toDateString();

        $subscription = Subscription::create([
            'business_id'          => $business->id,
            'subscription_plan_id' => $plan->id,
            'status'               => 'pending',
            'billing_cycle'        => $billing_cycle,
            'monthly_price'        => $plan->monthly_price,
            'total_price'          => $price['total'],
            'discount_percentage'  => $price['discount'],
            'started_at'           => $started_at,
            'ends_at'              => $ends_at,
            'auto_renew'           => true,
            'notes'                => $notes,
        ]);

        return SubscriptionInvoice::create([
            'subscription_id'      => $subscription->id,
            'business_id'          => $business->id,
            'invoice_number'       => SubscriptionInvoice::generateInvoiceNumber(),
            'amount'               => $subscription->total_price,
            'status'               => 'pending',
            'billing_period_start' => $started_at,
            'billing_period_end'   => $ends_at,
            'due_date'             => $started_at,
            'payment_method'       => $payment_method,
            'payment_proof'        => $payment_proof_path,
            'payment_reference'    => $payment_reference ?: null,
            'created_by'           => auth()->id(),
        ]);
    }
}
