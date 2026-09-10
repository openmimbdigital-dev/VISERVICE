<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Detalle de cada pago de suscripción, venga de donde venga.
 *
 * Hasta ahora del pago solo quedaban tres campos sueltos en el cobro —método,
 * referencia y fecha— y del pago en línea se perdía casi todo: con qué medio
 * pagó, a nombre de quién, qué impuestos traía, qué código le dio la pasarela.
 * Eso vivía únicamente dentro del payload crudo del webhook, que es una bitácora
 * técnica, no algo que se pueda consultar.
 *
 * Esta tabla guarda el pago como hecho de negocio, con el mismo formato para el
 * cobro manual y el de la pasarela.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->unsignedBigInteger('subscription_invoice_id');

            $table->string('channel', 20)
                ->comment('manual = confirmado por un humano; online = pasarela');
            $table->string('gateway', 20)->nullable()
                ->comment('bold, u otra pasarela en el futuro');

            $table->string('method', 60)->nullable()
                ->comment('PSE, NEQUI, CARD, Transferencia bancaria, Efectivo…');
            $table->string('method_label', 80)->nullable()
                ->comment('El método como se le muestra a una persona');

            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('COP');
            $table->decimal('tip', 14, 2)->nullable();
            $table->json('taxes')->nullable()->comment('Impuestos tal como los reportó la pasarela');

            $table->string('gateway_payment_id', 80)->nullable();
            $table->string('gateway_reference', 80)->nullable()
                ->comment('La referencia con la que viajó el cobro');
            $table->string('gateway_code', 60)->nullable()->comment('Código de aprobación de la pasarela');
            $table->string('gateway_source', 60)->nullable()->comment('Origen del pago en la pasarela');

            $table->string('payer_email')->nullable();
            $table->string('payment_reference', 120)->nullable()->comment('Referencia que dio quien pagó');
            $table->timestamp('paid_at')->nullable();

            $table->json('metadata')->nullable()->comment('Lo demás que reportó la pasarela');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('business_id', 'sub_payments_business_fk')
                ->references('id')->on('businesses')->cascadeOnDelete();
            $table->foreign('subscription_id', 'sub_payments_subscription_fk')
                ->references('id')->on('subscriptions')->nullOnDelete();
            $table->foreign('subscription_invoice_id', 'sub_payments_invoice_fk')
                ->references('id')->on('subscription_invoices')->cascadeOnDelete();
            $table->foreign('created_by', 'sub_payments_user_fk')
                ->references('id')->on('users')->nullOnDelete();

            $table->index(['business_id', 'paid_at'], 'sub_payments_business_paid_idx');
            $table->index(['channel', 'method'], 'sub_payments_channel_method_idx');

            // Un cobro en línea no puede quedar registrado dos veces por el mismo
            // pago de la pasarela, aunque el webhook llegue repetido.
            $table->unique(['gateway', 'gateway_payment_id'], 'sub_payments_gateway_payment_unique');
        });

        $this->backfillFromPaidInvoices();
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }

    /**
     * Reconstruye lo que se pueda de los cobros ya pagados. De los antiguos solo
     * hay método, referencia y fecha; se marcan como reconstruidos.
     */
    private function backfillFromPaidInvoices(): void
    {
        if (! Schema::hasTable('subscription_invoices')) {
            return;
        }

        DB::table('subscription_invoices')
            ->where('status', 'paid')
            ->orderBy('id')
            ->chunkById(200, function ($invoices) {
                $rows = [];

                foreach ($invoices as $invoice) {
                    $is_online = ! empty($invoice->bold_payment_id);

                    $rows[] = [
                        'business_id'             => $invoice->business_id,
                        'subscription_id'         => $invoice->subscription_id,
                        'subscription_invoice_id' => $invoice->id,
                        'channel'                 => $is_online ? 'online' : 'manual',
                        'gateway'                 => $is_online ? 'bold' : null,
                        'method'                  => $invoice->bold_payment_method ?: $invoice->payment_method,
                        'amount'                  => $invoice->amount,
                        'currency'                => 'COP',
                        'gateway_payment_id'      => $invoice->bold_payment_id ?: null,
                        'gateway_reference'       => $invoice->bold_reference ?: null,
                        'payment_reference'       => $invoice->payment_reference,
                        'paid_at'                 => $invoice->paid_at,
                        'metadata'                => json_encode(['backfilled' => true], JSON_UNESCAPED_UNICODE),
                        'notes'                   => $invoice->notes,
                        'created_by'              => $invoice->created_by,
                        'created_at'              => $invoice->paid_at ?? $invoice->created_at,
                        'updated_at'              => now(),
                    ];
                }

                foreach (array_chunk($rows, 300) as $chunk) {
                    DB::table('subscription_payments')->insert($chunk);
                }
            });
    }
};
