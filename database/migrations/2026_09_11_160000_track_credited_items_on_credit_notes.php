<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Qué acreditó cada nota crédito.
 *
 * Una factura puede recibir varias notas —se devuelve una cosa hoy y otra el mes
 * que viene— y hay un límite evidente: entre todas no se puede acreditar más de
 * lo que se cobró, ni devolver de un ítem más unidades de las que se facturaron.
 *
 * Sin guardar qué acreditó cada una no hay forma de comprobarlo, porque el
 * documento ya enviado no es un sitio del que se puedan sacar cuentas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('electronic_invoices', function (Blueprint $table) {
            $table->decimal('credited_amount', 14, 2)->nullable()->after('document_number')
                ->comment('Total acreditado por esta nota crédito');
            $table->json('credited_items')->nullable()->after('credited_amount')
                ->comment('Cantidades devueltas por ítem de la factura');
        });
    }

    public function down(): void
    {
        Schema::table('electronic_invoices', function (Blueprint $table) {
            $table->dropColumn(['credited_amount', 'credited_items']);
        });
    }
};
