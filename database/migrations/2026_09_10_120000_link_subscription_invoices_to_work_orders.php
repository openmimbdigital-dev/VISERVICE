<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enlaza el cobro de una suscripción con la OT y la factura que lo respaldan.
 *
 * Al registrar el pago de una suscripción se genera una OT en el negocio dueño
 * de la plataforma y, de ella, la factura que se emite ante la DIAN. Guardar el
 * enlace sirve para dos cosas: llegar de un lado al otro y, sobre todo, no
 * volver a facturar un cobro que ya tiene su documento.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_invoices', function (Blueprint $table) {
            $table->foreignId('work_order_id')->nullable()->after('business_id')
                ->constrained('work_orders')->nullOnDelete()
                ->comment('OT generada al cobrar este período');

            $table->foreignId('work_order_invoice_id')->nullable()->after('work_order_id')
                ->constrained('work_order_invoices')->nullOnDelete()
                ->comment('Factura emitida por este cobro');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_invoices', function (Blueprint $table) {
            $table->dropForeign(['work_order_id']);
            $table->dropForeign(['work_order_invoice_id']);
            $table->dropColumn(['work_order_id', 'work_order_invoice_id']);
        });
    }
};
