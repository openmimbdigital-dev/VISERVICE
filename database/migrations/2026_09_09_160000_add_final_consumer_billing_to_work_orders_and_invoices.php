<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Facturación a consumidor final.
 *
 * Cuando el cliente no quiere la factura a su nombre, la DIAN permite emitirla a
 * un adquiriente no identificado. La OT conserva igual al cliente real —es quien
 * trajo el equipo y a quien hay que llamar—; lo que cambia es a nombre de quién
 * sale el documento electrónico.
 *
 * La bandera se guarda en los dos lados a propósito: la de la OT es la decisión
 * por defecto, y la de la factura es el registro de cómo se emitió realmente, que
 * no debe cambiar aunque después alguien edite la OT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->boolean('bill_to_final_consumer')->default(false)->after('client_id')
                ->comment('El cliente pidió que la factura no salga a su nombre');
        });

        Schema::table('work_order_invoices', function (Blueprint $table) {
            $table->boolean('bill_to_final_consumer')->default(false)->after('work_order_id')
                ->comment('Emitida a consumidor final ante la DIAN');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('bill_to_final_consumer');
        });

        Schema::table('work_order_invoices', function (Blueprint $table) {
            $table->dropColumn('bill_to_final_consumer');
        });
    }
};
