<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Una factura puede tener dos documentos electrónicos: ella misma y su nota
 * crédito.
 *
 * Hasta ahora la tabla exigía uno solo por factura, que era cierto mientras el
 * único documento posible era la factura. La unicidad no desaparece, se afina:
 * sigue habiendo una sola factura electrónica y una sola nota crédito por cada
 * factura, que es lo que de verdad hay que impedir duplicar.
 */
return new class extends Migration
{
    private const OLD_INDEX = 'electronic_invoices_work_order_invoice_id_unique';

    public function up(): void
    {
        // El nuevo índice va primero: la clave foránea necesita alguno que
        // empiece por «work_order_invoice_id», y MySQL no deja quedarse sin él ni
        // por un instante.
        Schema::table('electronic_invoices', function (Blueprint $table) {
            $table->unique(['work_order_invoice_id', 'document_type'], 'electronic_invoices_invoice_document_unique');
        });

        if ($this->indexExists(self::OLD_INDEX)) {
            Schema::table('electronic_invoices', function (Blueprint $table) {
                $table->dropUnique(self::OLD_INDEX);
            });
        }
    }

    public function down(): void
    {
        Schema::table('electronic_invoices', function (Blueprint $table) {
            $table->dropUnique('electronic_invoices_invoice_document_unique');
        });

        // Volver atrás solo es posible si no quedan notas crédito emitidas.
        if (DB::table('electronic_invoices')->where('document_type', '!=', 'invoice')->doesntExist()) {
            Schema::table('electronic_invoices', function (Blueprint $table) {
                $table->unique('work_order_invoice_id', self::OLD_INDEX);
            });
        }
    }

    private function indexExists(string $name): bool
    {
        return DB::selectOne("SHOW INDEX FROM electronic_invoices WHERE Key_name = ?", [$name]) !== null;
    }
};
