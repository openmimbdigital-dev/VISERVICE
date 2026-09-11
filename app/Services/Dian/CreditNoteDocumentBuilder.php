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
    ): string {
        $document = $this->buildCreditNoteArray(
            $credit_note,
            $original,
            $invoice,
            $setting,
            $reason_code,
            $reason_description,
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
    ): array {
        $invoice->loadMissing([
            'items.workOrderItem.catalogProduct.unit',
            'workOrder.client.city.country',
            'workOrder.associatedDocuments',
            'business.city.country',
        ]);

        $lines = $this->buildLines($invoice);
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
