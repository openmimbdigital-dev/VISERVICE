<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Business;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BusinessSubscriptionsSeeder extends Seeder
{
    private const BILLING_CYCLE = 'annual';

    public function run(): void
    {
        $plan = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();

        if (! $plan) {
            $this->command?->warn('BusinessSubscriptionsSeeder: no hay planes activos. Ejecuta SubscriptionPlansSeeder primero.');

            return;
        }

        $price = $plan->getPriceForCycle(self::BILLING_CYCLE);
        $started_at = Carbon::now()->startOfDay();
        $ends_at = $started_at->copy()->addMonths($price['months']);
        $created_by = User::query()->where('username', 'superadmin')->value('id');
        $bank_account_id = BankAccount::query()->where('is_active', true)->value('id');

        $businesses = Business::query()
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get(['id', 'name', 'slug']);

        if ($businesses->isEmpty()) {
            $this->command?->warn('BusinessSubscriptionsSeeder: no hay negocios para suscribir.');

            return;
        }

        $created = 0;
        $updated = 0;

        foreach ($businesses as $index => $business) {
            $subscription = Subscription::withTrashed()
                ->where('business_id', $business->id)
                ->where('notes', 'like', 'Seeder:%')
                ->first();

            $attributes = [
                'subscription_plan_id' => $plan->id,
                'status' => 'active',
                'billing_cycle' => self::BILLING_CYCLE,
                'monthly_price' => $plan->monthly_price,
                'total_price' => $price['total'],
                'discount_percentage' => $price['discount'],
                'started_at' => $started_at->toDateString(),
                'ends_at' => $ends_at->toDateString(),
                'trial_ends_at' => null,
                'cancelled_at' => null,
                'cancellation_reason' => null,
                'auto_renew' => true,
                'notes' => 'Seeder: suscripción demo con pago confirmado',
                'created_by' => $created_by,
                'deleted_at' => null,
            ];

            if ($subscription) {
                $subscription->restore();
                $subscription->update($attributes);
                $updated++;
            } else {
                $subscription = Subscription::query()->create([
                    'business_id' => $business->id,
                    ...$attributes,
                ]);
                $created++;
            }

            $invoice_number = 'SEED-INV-'.str_pad((string) $business->id, 4, '0', STR_PAD_LEFT);

            SubscriptionInvoice::query()->updateOrCreate(
                [
                    'subscription_id' => $subscription->id,
                    'invoice_number' => $invoice_number,
                ],
                [
                    'business_id' => $business->id,
                    'amount' => $subscription->total_price,
                    'status' => 'paid',
                    'billing_period_start' => $started_at->toDateString(),
                    'billing_period_end' => $ends_at->toDateString(),
                    'due_date' => $started_at->toDateString(),
                    'paid_at' => $started_at->copy()->addHours(2 + ($index % 10)),
                    'payment_method' => 'transfer',
                    'payment_reference' => 'SEED-PAY-'.$business->id,
                    'bank_account_id' => $bank_account_id,
                    'payment_proof' => null,
                    'notes' => 'Seeder: pago confirmado',
                    'created_by' => $created_by,
                ]
            );
        }

        $this->command?->info(
            "Suscripciones demo: {$created} creadas, {$updated} actualizadas ({$businesses->count()} negocios). Plan: {$plan->name} / ".self::BILLING_CYCLE.'.'
        );
    }
}
