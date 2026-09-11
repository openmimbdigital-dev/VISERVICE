<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Varias notas crédito por factura, pero una sola factura electrónica.
 *
 * Una devolución parcial puede repetirse —se devuelve una cosa hoy y otra el mes
 * que viene—, así que la unicidad «un documento de cada tipo por factura» se
 * queda corta. Pero relajarla del todo perdería lo que sí hay que garantizar: que
 * una factura no pueda tener dos facturas electrónicas, que sería numeración
 * duplicada ante la DIAN.
 *
 * La restricción correcta es condicional, y MySQL no tiene índices parciales. Se
 * consigue con una columna generada que solo toma valor cuando el documento es
 * una factura: en las notas queda nula, y un índice único admite tantos nulos
 * como haga falta.
 */
return new class extends Migration
{
    public function up(): void
    {
        // La clave foránea necesita un índice que empiece por su columna; se pone
        // antes de quitar el que se lo estaba dando.
        if (! $this->indexExists('electronic_invoices_invoice_idx')) {
            DB::statement('ALTER TABLE electronic_invoices ADD INDEX electronic_invoices_invoice_idx (work_order_invoice_id)');
        }

        if ($this->indexExists('electronic_invoices_invoice_document_unique')) {
            DB::statement('ALTER TABLE electronic_invoices DROP INDEX electronic_invoices_invoice_document_unique');
        }

        if (! $this->columnExists('single_invoice_key')) {
            DB::statement(
                'ALTER TABLE electronic_invoices ADD COLUMN single_invoice_key BIGINT UNSIGNED '
                ."GENERATED ALWAYS AS (CASE WHEN document_type = 'invoice' THEN work_order_invoice_id ELSE NULL END) VIRTUAL"
            );
        }

        if (! $this->indexExists('electronic_invoices_single_invoice_unique')) {
            DB::statement('ALTER TABLE electronic_invoices ADD UNIQUE INDEX electronic_invoices_single_invoice_unique (single_invoice_key)');
        }
    }

    public function down(): void
    {
        if ($this->indexExists('electronic_invoices_single_invoice_unique')) {
            DB::statement('ALTER TABLE electronic_invoices DROP INDEX electronic_invoices_single_invoice_unique');
        }

        if ($this->columnExists('single_invoice_key')) {
            DB::statement('ALTER TABLE electronic_invoices DROP COLUMN single_invoice_key');
        }

        if (! $this->indexExists('electronic_invoices_invoice_document_unique')) {
            DB::statement('ALTER TABLE electronic_invoices ADD UNIQUE INDEX electronic_invoices_invoice_document_unique (work_order_invoice_id, document_type)');
        }
    }

    private function indexExists(string $name): bool
    {
        return DB::selectOne('SHOW INDEX FROM electronic_invoices WHERE Key_name = ?', [$name]) !== null;
    }

    private function columnExists(string $name): bool
    {
        return Schema::hasColumn('electronic_invoices', $name);
    }
};
