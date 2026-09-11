<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lo que hace falta para emitir notas crédito.
 *
 * La nota crédito es un documento aparte de la factura y el proveedor la trata
 * como tal: tiene su propio perfil —su propio «tr_tipo_id», creado con el tipo de
 * documento 20— y su propia numeración, independiente de la de las facturas.
 *
 * Va junto a la configuración de facturación porque es la misma resolución y el
 * mismo emisor; lo único que cambia es el documento.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_dian_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('credit_note_tr_tipo_id')->nullable()->after('tr_tipo_id')
                ->comment('Perfil de emisión de notas crédito en el proveedor');
            $table->string('credit_note_prefix', 10)->nullable()->after('prefix')
                ->comment('Prefijo propio de las notas crédito');
            $table->unsignedInteger('credit_note_next_consecutive')->nullable()->after('next_consecutive')
                ->comment('Siguiente número de nota crédito');
        });
    }

    public function down(): void
    {
        Schema::table('business_dian_settings', function (Blueprint $table) {
            $table->dropColumn([
                'credit_note_tr_tipo_id',
                'credit_note_prefix',
                'credit_note_next_consecutive',
            ]);
        });
    }
};
