<?php

namespace App\Services\Dian;

use App\Models\BusinessDianSetting;
use App\Models\ElectronicCreditNote;
use App\Models\ElectronicInvoice;
use App\Models\WorkOrderInvoice;

/**
 * Arma la nota crédito con la que se deja sin efecto una factura ya validada.
 *
 * Es casi la misma factura: mismo emisor, mismo adquiriente, mismas líneas y los
 * mismos totales —anular una factura completa es acreditar exactamente lo que se
 * cobró—. Por eso hereda del constructor de facturas en vez de repetirlo; lo que
 * cambia son tres cosas:
 *
 *  - El encabezado es «CRE» en lugar de «FAC», con su propio ProfileID y el tipo
 *    de documento 91.
 *  - Las líneas van en «CNL» en lugar de «IVL».
 *  - Aparece «BRF», que es lo que convierte el documento en una nota crédito de
 *    verdad: apunta a la factura original por su número, su CUFE y su fecha. Sin
 *    esa referencia la DIAN no sabría qué se está anulando.
 */
class CreditNoteDocumentBuilder extends InvoiceDocumentBuilder
{
    /** Serializa la nota en el formato configurado para el emisor. */
    public function buildCreditNote(
        ElectronicCreditNote $credit_note,
        ElectronicInvoice $original,
        WorkOrderInvoice $invoice,
        BusinessDianSetting $setting,
        string $reason_code,
        string $reason_description,
        array $returned = [],
    ): string {
        $document = $this->buildCreditNoteArray(
            $credit_note,
            $original,
            $invoice,
            $setting,
            $reason_code,
            $reason_description,
            $returned,
        );

        return $setting->document_format === 'xml'
            ? $this->toXml($document)
            : $this->toJson($document);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildCreditNoteArray(
        ElectronicCreditNote $credit_note,
        ElectronicInvoice $original,
        WorkOrderInvoice $invoice,
        BusinessDianSetting $setting,
        string $reason_code,
        string $reason_description,
        array $returned = [],
    ): array {
        $invoice->loadMissing([
            'items.workOrderItem.catalogProduct.unit',
            'workOrder.client.city.country',
            'workOrder.associatedDocuments',
            'business.city.country',
        ]);

        $lines = $this->buildLines($invoice);

        // Una devolución parcial acredita solo lo devuelto. Los totales salen de
        // las líneas, así que recortarlas basta para que todo el documento cuadre.
        if ($returned !== []) {
            $lines = $this->creditedLines($invoice, $lines, $returned);
        }

        $totals = $this->calculateTotals($invoice, $lines);

        $document = [
            'EXT' => $this->extensionBlock($setting),
            'CRE' => $this->creditNoteHeaderBlock(
                $credit_note,
                $setting,
                count($lines),
                $reason_code,
                $reason_description,
            ),
            'NOT' => $this->noteBlocks($invoice, $setting, $credit_note),
            'BRF' => $this->billingReferenceBlocks($original),
            'ASP' => $this->issuerBlock($invoice, $setting),
            'ACP' => $this->customerBlock($invoice),
            'PYM' => $this->paymentBlocks($invoice, $credit_note),
        ];

        $document['TXT'] = $this->documentTaxBlocks($totals);
        $document['TOT'] = $this->totalsBlock($totals);

        // Las líneas de una nota crédito viajan en CNL, no en IVL.
        $document['CNL'] = $this->lineBlocks($lines);
        $document['LIT'] = $this->lineTaxBlocks($lines);

        $document['REC'] = $this->deliveryBlocks($invoice, $setting);
        $document['ADD'] = $this->addressBlocks($invoice);
        $document['CON'] = $this->contactBlocks($invoice);

        return ['Document' => $document];
    }

    /** Serializa un documento ya armado, en el formato del emisor. */
    public function serialize(array $document, BusinessDianSetting $setting): string
    {
        return $setting->document_format === 'xml'
            ? $this->toXml($document)
            : $this->toJson($document);
    }

    /**
     * Deja solo las líneas devueltas, con la cantidad que se devuelve.
     *
     * Las líneas llegan ya construidas —con el descuento de la factura repartido
     * entre ellas—, así que en vez de rehacer esa cuenta se escala cada una por la
     * proporción devuelta. Así la parte acreditada conserva el mismo descuento y
     * el mismo impuesto que tuvo al facturarse, que es lo que hace que las dos
     * mitades sumen exactamente la factura.
     *
     * @param  list<array<string, mixed>>  $lines
     * @param  array<int, float>  $returned  Cantidad devuelta por ítem de la factura.
     * @return list<array<string, mixed>>
     */
    protected function creditedLines(WorkOrderInvoice $invoice, array $lines, array $returned): array
    {
        $credited = [];
        $position = 0;
        $index = 0;

        foreach ($invoice->items as $invoice_item) {
            if (! $invoice_item->workOrderItem) {
                continue;
            }

            $line = $lines[$index] ?? null;
            $index++;

            if (! $line) {
                continue;
            }

            $quantity = round((float) ($returned[$invoice_item->id] ?? 0), 2);

            if ($quantity <= 0) {
                continue;
            }

            $invoiced = round((float) $invoice_item->quantity, 2);
            $share = $invoiced > 0 ? $quantity / $invoiced : 0.0;
            $position++;

            $line_amount = round((float) $line['line_amount'] * $share, 2);

            $credited[] = array_merge($line, [
                'id'          => $position,
                'quantity'    => $quantity,
                'line_amount' => $line_amount,
                'tax_amount'  => round($line_amount * (float) $line['tax_percentage'] / 100, 2),
            ]);
        }

        return $credited;
    }

    /** @return array<string, mixed> */
    protected function creditNoteHeaderBlock(
        ElectronicCreditNote $credit_note,
        BusinessDianSetting $setting,
        int $line_count,
        string $reason_code,
        string $reason_description,
    ): array {
        $defaults = (array) config('dian.defaults');
        $credit_defaults = (array) config('dian.credit_note');
        $issued_at = $credit_note->issued_at ?? now();

        return [
            'UBLVersionID'                   => (string) $defaults['ubl_version_id'],
            'CustomizationID'                => (string) $credit_defaults['customization_id'],
            'ProfileID'                      => (string) $credit_defaults['profile_id'],
            'ProfileExecutionID'             => (string) ($defaults['profile_execution_id'][$setting->environment] ?? '2'),
            'ID'                             => (string) $credit_note->document_number,
            'UUID'                           => '',
            'IssueDate'                      => $issued_at->format('Y-m-d'),
            'IssueTime'                      => $issued_at->format('H:i:s'),
            'CreditNoteTypeCode'             => (int) $credit_defaults['type_code'],
            'DocumentCurrencyCode'           => (string) $defaults['currency_code'],
            'LineCountNumeric'               => $line_count,
            'DiscrepancyResponseCode'        => $reason_code,
            'DiscrepancyResponseDescription' => $reason_description,
        ];
    }

    /**
     * La factura que esta nota deja sin efecto.
     *
     * El CUFE es lo que la identifica ante la DIAN; sin él la referencia no sirve
     * de nada, así que si falta es mejor que reviente aquí y no que la DIAN
     * rechace un documento con un consecutivo ya gastado.
     *
     * @return list<array<string, mixed>>
     */
    protected function billingReferenceBlocks(ElectronicInvoice $original): array
    {
        return [[
            'Invoice_ID'        => (string) $original->document_number,
            'Invoice_UUID'      => (string) $original->cufe,
            'Invoice_IssueDate' => ($original->issued_at ?? $original->created_at)->format('Y-m-d'),
        ]];
    }
}
