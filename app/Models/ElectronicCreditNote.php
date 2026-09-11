<?php

namespace App\Models;

/**
 * Nota crédito electrónica: el documento con el que se deja sin efecto una
 * factura que la DIAN ya validó.
 *
 * Vive en la misma tabla que las facturas porque es la misma vida: se arma, se
 * manda al proveedor, la DIAN la valida y quedan su CUDE y sus archivos. Lo que
 * cambia es el tipo de documento, y de eso se encarga el alcance global que
 * hereda —cada modelo ve solo sus filas—.
 */
class ElectronicCreditNote extends ElectronicInvoice
{
    public const DOCUMENT_TYPE = 'credit_note';

    /** Comparte tabla con la factura: Eloquent la deduciria del nombre de la clase. */
    protected $table = 'electronic_invoices';

    /** La factura que esta nota anula. */
    public function voidedInvoice()
    {
        return $this->belongsTo(WorkOrderInvoice::class, 'work_order_invoice_id');
    }
}
