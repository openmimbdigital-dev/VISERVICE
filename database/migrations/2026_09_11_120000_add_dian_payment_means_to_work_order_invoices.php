<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Medio de pago que se le declara a la DIAN.
 *
 * El documento electrónico siempre lleva uno —es obligatorio en el bloque PYM—,
 * y hasta ahora salía fijo como «instrumento no definido» porque nadie lo
 * preguntaba. El pago se registra después de facturar, cuando el documento ya
 * viajó, así que ese dato nunca llegaba a la DIAN.
 *
 * Se guarda aparte de «payment_method», que es el nombre libre con el que el
 * negocio lleva su propia contabilidad: aquí va el código de la tabla 12 de la
 * DIAN, que es lo único que ella entiende.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_order_invoices', function (Blueprint $table) {
            $table->string('dian_payment_means_code', 3)->nullable()->after('payment_reference')
                ->comment('Código del medio de pago según la tabla 12 de la DIAN (UN/ECE 4461)');
        });
    }

    public function down(): void
    {
        Schema::table('work_order_invoices', function (Blueprint $table) {
            $table->dropColumn('dian_payment_means_code');
        });
    }
};
