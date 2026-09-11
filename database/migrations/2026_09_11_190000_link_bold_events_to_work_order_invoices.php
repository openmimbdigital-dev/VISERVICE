<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El webhook de Bold ahora recibe pagos de dos cosas distintas: las suscripciones
 * que nos pagan los negocios y las facturas que les pagan sus clientes.
 *
 * La bitácora ya apuntaba a la suscripción; le falta poder apuntar a la factura,
 * que es lo que permite reconstruir después a qué se refería cada aviso.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bold_webhook_events', function (Blueprint $table) {
            $table->unsignedBigInteger('work_order_invoice_id')->nullable()->after('subscription_invoice_id');

            $table->foreign('work_order_invoice_id', 'bold_events_work_order_invoice_fk')
                ->references('id')->on('work_order_invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bold_webhook_events', function (Blueprint $table) {
            $table->dropForeign('bold_events_work_order_invoice_fk');
            $table->dropColumn('work_order_invoice_id');
        });
    }
};
