<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cobro en línea de suscripciones con la pasarela Bold.
 *
 * Se suma a lo que ya había —transferencia con comprobante y confirmación
 * manual—: el comercio puede pagar con tarjeta, PSE, Nequi o botón Bancolombia
 * desde un link, y el pago se confirma solo cuando Bold nos avisa.
 *
 * La tabla de eventos existe por la idempotencia que Bold pide: reintenta cada
 * notificación hasta cinco veces si no recibe un 200, así que el mismo pago
 * puede llegar varias veces y solo debe procesarse una.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_invoices', function (Blueprint $table) {
            $table->string('bold_payment_link', 60)->nullable()->after('payment_proof')
                ->comment('Identificador del link de pago en Bold (LNK_...)');
            $table->string('bold_link_url')->nullable()->after('bold_payment_link')
                ->comment('URL del checkout que se le comparte al comercio');
            $table->string('bold_reference', 80)->nullable()->after('bold_link_url')
                ->comment('Referencia enviada a Bold; es como vuelve identificado el pago');
            $table->string('bold_status', 20)->nullable()->after('bold_reference')
                ->comment('ACTIVE, PROCESSING, PAID o EXPIRED');
            $table->string('bold_payment_id', 80)->nullable()->after('bold_status')
                ->comment('Identificador del pago aprobado en Bold');
            $table->string('bold_payment_method', 40)->nullable()->after('bold_payment_id');
            $table->timestamp('bold_link_created_at')->nullable()->after('bold_payment_method');

            $table->index('bold_reference', 'subscription_invoices_bold_reference_idx');
            $table->index('bold_payment_link', 'subscription_invoices_bold_link_idx');
        });

        Schema::create('bold_webhook_events', function (Blueprint $table) {
            $table->id();

            // El identificador del evento es único en Bold: es la llave con la
            // que se descartan los reintentos.
            $table->uuid('event_id')->unique();

            $table->string('type', 40)->comment('SALE_APPROVED, SALE_REJECTED, VOID_APPROVED…');
            $table->string('reference', 80)->nullable()->comment('Nuestra referencia, si vino');
            $table->string('payment_id', 80)->nullable();
            $table->unsignedBigInteger('subscription_invoice_id')->nullable();
            $table->boolean('signature_valid')->default(false);
            $table->boolean('processed')->default(false)
                ->comment('Si el evento llegó a mover el cobro');
            $table->string('result', 255)->nullable()->comment('Qué se hizo o por qué no');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->foreign('subscription_invoice_id', 'bold_events_invoice_fk')
                ->references('id')->on('subscription_invoices')->nullOnDelete();

            $table->index(['type', 'created_at'], 'bold_events_type_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bold_webhook_events');

        Schema::table('subscription_invoices', function (Blueprint $table) {
            $table->dropIndex('subscription_invoices_bold_reference_idx');
            $table->dropIndex('subscription_invoices_bold_link_idx');
            $table->dropColumn([
                'bold_payment_link',
                'bold_link_url',
                'bold_reference',
                'bold_status',
                'bold_payment_id',
                'bold_payment_method',
                'bold_link_created_at',
            ]);
        });
    }
};
