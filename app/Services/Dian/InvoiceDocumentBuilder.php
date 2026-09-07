<?php

namespace App\Services\Dian;

use App\Models\BusinessDianSetting;
use App\Models\City;
use App\Models\ElectronicInvoice;
use App\Models\WorkOrderInvoice;
use App\Support\DianNit;
use DOMDocument;
use DOMElement;

/**
 * Construye el documento en el formato DATASET de TITANIO a partir de una factura
 * de orden de trabajo. La plataforma lo traduce luego a UBL 2.1 DIAN.
 *
 * La estructura sigue el documento de referencia que el proveedor confirmó como
 * aceptado. Dos detalles son determinantes y no se deben cambiar a la ligera:
 *
 *  - Los bloques de una sola ocurrencia (EXT, FAC, ASP, ACP, TOT) van como objeto;
 *    los repetibles (NOT, PYM, TXT, IVL, LIT, REC, ADD) van como lista.
 *  - Los tipos importan: la plataforma deserializa contra un modelo tipado, así que
 *    un importe o un contador enviado como texto descarta el bloque completo y lo
 *    genera vacío, lo que después hace fallar la validación del esquema.
 */
class InvoiceDocumentBuilder
{
    /** Identificadores de las direcciones que referencian el emisor y el adquiriente. */
    private const ADDRESS_ISSUER = '1';
    private const ADDRESS_CUSTOMER = '2';

    /** Identificadores de los contactos: la DIAN los exige en ambas partes. */
    private const CONTACT_ISSUER = '1';
    private const CONTACT_CUSTOMER = '2';

    /** Esquema de identificación DIAN: 31 = NIT. */
    private const SCHEME_NAME_NIT = '31';

    /** Versión de la lista de responsabilidades fiscales de la DIAN. */
    private const TAX_LEVEL_LIST_NAME = '48';

    /** Estándar de adopción del contribuyente para el código de producto. */
    private const ITEM_SCHEME_ID = '999';

    /** Código DIAN del IVA. */
    private const TAX_SCHEME_ID = '01';
    private const TAX_SCHEME_NAME = 'IVA';

    /** Serializa el documento en el formato configurado para el emisor. */
    public function build(
        ElectronicInvoice $electronic_invoice,
        WorkOrderInvoice $invoice,
        BusinessDianSetting $setting,
    ): string {
        $document = $this->buildArray($electronic_invoice, $invoice, $setting);

        return $setting->document_format === 'xml'
            ? $this->toXml($document)
            : $this->toJson($document);
    }

    /**
     * Estructura completa del documento.
     *
     * @return array<string, mixed>
     */
    public function buildArray(
        ElectronicInvoice $electronic_invoice,
        WorkOrderInvoice $invoice,
        BusinessDianSetting $setting,
    ): array {
        $invoice->loadMissing([
            'items.workOrderItem.catalogProduct.unit',
            'workOrder.client.city.country',
            'business.city.country',
        ]);

        $lines = $this->buildLines($invoice);
        $totals = $this->calculateTotals($invoice, $lines);

        $document = [
            'EXT' => $this->extensionBlock($setting),
            'FAC' => $this->invoiceHeaderBlock($electronic_invoice, $setting, count($lines)),
            'NOT' => $this->noteBlocks($invoice, $setting, $electronic_invoice),
            'ASP' => $this->issuerBlock($invoice, $setting),
            'ACP' => $this->customerBlock($invoice),
            'PYM' => $this->paymentBlocks($invoice, $electronic_invoice),
        ];

        // El bloque de impuestos se envía siempre: la fórmula del CUFE de la DIAN
        // incluye el código de impuesto y su valor, aunque la factura no lleve IVA.
        $document['TXT'] = $this->documentTaxBlocks($totals);
        $document['TOT'] = $this->totalsBlock($totals);
        $document['IVL'] = $this->lineBlocks($lines);

        // Cada impuesto declarado en TXT debe tener su contraparte por línea en LIT.
        $document['LIT'] = $this->lineTaxBlocks($lines);

        $document['REC'] = $this->deliveryBlocks($invoice, $setting);
        $document['ADD'] = $this->addressBlocks($invoice);
        $document['CON'] = $this->contactBlocks($invoice);

        return ['Document' => $document];
    }

    /** @param array<string, mixed> $document */
    public function toJson(array $document): string
    {
        return (string) json_encode(
            $document,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION
        );
    }

    /** @param array<string, mixed> $document */
    public function toXml(array $document): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('Document');
        $dom->appendChild($root);

        foreach ((array) ($document['Document'] ?? []) as $block => $content) {
            // Un bloque repetible llega como lista; uno único, como mapa de campos.
            $occurrences = array_is_list($content) ? $content : [$content];

            foreach ($occurrences as $values) {
                $root->appendChild($this->xmlBlock($dom, $block, (array) $values));
            }
        }

        return (string) $dom->saveXML();
    }

    /* ----------------------------------------------------------------- *
     |  Bloques del documento
     * ----------------------------------------------------------------- */

    /** @return array<string, mixed> */
    private function extensionBlock(BusinessDianSetting $setting): array
    {
        $defaults = (array) config('dian.defaults');

        return [
            'InvoiceAuthorization'             => (int) $setting->resolution_number,
            'StartDate'                        => $setting->valid_from?->format('Y-m-d') ?? '',
            'EndDate'                          => $setting->valid_to?->format('Y-m-d') ?? '',
            'Prefix'                           => (string) $setting->prefix,
            'From'                             => (int) $setting->range_from,
            'To'                               => (int) $setting->range_to,
            'IdentificationCode'               => (string) $defaults['identification_code'],
            'ProviderID'                       => (string) $defaults['provider_id'],
            'ProviderID_schemeID'              => (string) $defaults['provider_id_scheme_id'],
            'SoftwareID'                       => (string) ($setting->software_id ?? ''),
            'SoftwareSecurityCode'             => '',
            'AuthorizationProviderID'          => (string) $defaults['authorization_provider_id'],
            'AuthorizationProviderID_schemeID' => (string) $defaults['authorization_provider_id_scheme_id'],
            'QRCode'                           => '',
        ];
    }

    /** @return array<string, mixed> */
    private function invoiceHeaderBlock(
        ElectronicInvoice $electronic_invoice,
        BusinessDianSetting $setting,
        int $line_count,
    ): array {
        $defaults = (array) config('dian.defaults');
        $issued_at = $electronic_invoice->issued_at ?? now();

        return [
            'UBLVersionID'         => (string) $defaults['ubl_version_id'],
            'CustomizationID'      => (string) $defaults['customization_id'],
            'ProfileID'            => (string) $defaults['profile_id'],
            'ProfileExecutionID'   => (string) ($defaults['profile_execution_id'][$setting->environment] ?? '2'),
            'ID'                   => (string) $electronic_invoice->document_number,
            'UUID'                 => '',
            'IssueDate'            => $issued_at->format('Y-m-d'),
            'IssueTime'            => $issued_at->format('H:i:s'),
            'InvoiceTypeCode'      => (string) $defaults['invoice_type_code'],
            'DocumentCurrencyCode' => (string) $defaults['currency_code'],
            'LineCountNumeric'     => $line_count,
        ];
    }

    /**
     * Notas del documento. La primera declara la resolución vigente, como en el
     * documento de referencia del proveedor.
     *
     * @return list<array<string, string>>
     */
    private function noteBlocks(
        WorkOrderInvoice $invoice,
        BusinessDianSetting $setting,
        ElectronicInvoice $electronic_invoice,
    ): array {
        $notes = [];

        if ($setting->resolution_number && $setting->range_from && $setting->range_to) {
            $notes[] = ['Note' => sprintf(
                'AUTORIZACIÓN DIAN NO. %s%s DESDE %s-%s HASTA %s-%s',
                $setting->resolution_number,
                $setting->valid_to ? ' VENCE EL '.$setting->valid_to->format('Y/m/d') : '',
                $setting->prefix,
                $setting->range_from,
                $setting->prefix,
                $setting->range_to,
            )];
        }

        if (filled($invoice->notes)) {
            $notes[] = ['Note' => (string) $invoice->notes];
        }

        if (filled($invoice->workOrder?->reference)) {
            $notes[] = ['Note' => 'Orden de trabajo '.$invoice->workOrder->reference];
        }

        return $notes !== [] ? $notes : [['Note' => '']];
    }

    /** @return array<string, mixed> */
    private function issuerBlock(WorkOrderInvoice $invoice, BusinessDianSetting $setting): array
    {
        $business = $invoice->business;

        return [
            'AdditionalAccountID'            => (string) ($business?->person_type ?: 1),
            'PartyName'                      => (string) $business?->name,
            'Physical_ADD_ID'                => self::ADDRESS_ISSUER,
            'Tax_RegistrationName'           => (string) $business?->name,
            'Tax_CompanyID'                  => DianNit::normalize($business?->nit),
            'Tax_CompanyID_schemeID'         => (string) DianNit::resolveVerificationDigit($business?->nit, $business?->verification_digit),
            'Tax_CompanyID_schemeName'       => self::SCHEME_NAME_NIT,
            'Tax_LevelCode'                  => (string) ($business?->fiscal_responsibilities ?: 'R-99-PN'),
            'Tax_LevelCode_listName'         => self::TAX_LEVEL_LIST_NAME,
            'Tax_Scheme_ID'                  => self::TAX_SCHEME_ID,
            'Tax_Scheme_Name'                => self::TAX_SCHEME_NAME,
            'Registration_ADD_ID'            => self::ADDRESS_ISSUER,
            'CorporateRegistrationScheme_ID' => (string) $setting->prefix,
            'Contact_ID'                     => self::CONTACT_ISSUER,
        ];
    }

    /** @return array<string, mixed> */
    private function customerBlock(WorkOrderInvoice $invoice): array
    {
        $client = $invoice->workOrder?->client;
        $is_company = (int) ($client?->person_type ?: 2) === 1;

        $block = [
            'CustomerAssignedAccountID' => (string) ($client?->id ?? ''),
            'AdditionalAccountID'       => (string) ($client?->person_type ?: 2),
            'PartyName'                 => (string) $client?->name,
            'Physical_ADD_ID'           => self::ADDRESS_CUSTOMER,
            'Tax_RegistrationName'      => (string) $client?->name,
            'Tax_CompanyID'             => DianNit::normalize($client?->document_number),
            'Tax_CompanyID_schemeName'  => $this->documentTypeCode($client?->document_type),
            'Tax_LevelCode'             => (string) ($client?->fiscal_responsibilities ?: 'R-99-PN'),
            'Tax_LevelCode_listName'    => self::TAX_LEVEL_LIST_NAME,
            'Tax_Scheme_ID'             => $is_company ? self::TAX_SCHEME_ID : 'ZZ',
            'Tax_Scheme_Name'           => $is_company ? self::TAX_SCHEME_NAME : 'No aplica',
            'Registration_ADD_ID'       => self::ADDRESS_CUSTOMER,
            'Contact_ID'                => self::CONTACT_CUSTOMER,
        ];

        // El dígito de verificación solo aplica a quien se identifica con NIT.
        if ($is_company) {
            $block['Tax_CompanyID_schemeID'] = (string) DianNit::resolveVerificationDigit(
                $client?->document_number,
                $client?->verification_digit
            );
        }

        return $block;
    }

    /** @return list<array<string, mixed>> */
    private function paymentBlocks(WorkOrderInvoice $invoice, ElectronicInvoice $electronic_invoice): array
    {
        $issued_at = $electronic_invoice->issued_at ?? now();
        $due_date = $invoice->due_date;

        // 1 = Contado, 2 = Crédito.
        $is_credit = $due_date !== null && $due_date->gt($issued_at);

        return [[
            'ID'               => $is_credit ? '2' : '1',
            'PaymentMeansCode' => '1',
            'PaymentDueDate'   => ($due_date ?? $issued_at)->format('Y-m-d'),
            'PaymentID'        => $is_credit ? 'CRÉDITO' : 'CONTADO',
        ]];
    }

    /**
     * Impuestos totales del documento.
     *
     * @param  array<string, float>  $totals
     * @return list<array<string, mixed>>
     */
    private function documentTaxBlocks(array $totals): array
    {
        return [[
            'TaxAmount'                  => $totals['tax_amount'],
            'SchemeID'                   => self::TAX_SCHEME_ID,
            'SchemeName'                 => self::TAX_SCHEME_NAME,
            'TaxSubTotal1_TaxableAmount' => $totals['taxable_base'],
            'TaxSubTotal1_TaxAmount'     => $totals['tax_amount'],
            'TaxSubTotal1_Percent'       => $totals['tax_percentage'],
        ]];
    }

    /**
     * @param  array<string, float>  $totals
     * @return array<string, mixed>
     */
    private function totalsBlock(array $totals): array
    {
        return [
            'LineExtensionAmount'  => $totals['line_extension'],
            'TaxExclusiveAmount'   => $totals['taxable_base'],
            'TaxInclusiveAmount'   => $totals['tax_inclusive'],
            'AllowanceTotalAmount' => 0.0,
            'ChargeTotalAmount'    => 0.0,
            'PrepaidAmount'        => 0.0,
            'PayableAmount'        => $totals['payable'],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    private function lineBlocks(array $lines): array
    {
        $blocks = [];

        foreach ($lines as $line) {
            $blocks[] = [
                'ID'                       => (string) $line['id'],
                'UUID'                     => '',
                'InvoicedQuantity'         => $line['quantity'],
                'InvoicedQuantityUnitCode' => $line['unit_code'],
                'LineExtensionAmount'      => $line['line_amount'],
                'FreeOfChargeIndicator'    => false,
                'PriceAmount'              => $line['unit_price'],
                'BaseQuantity'             => $line['quantity'],
                'BaseQuantity_unitCode'    => $line['unit_code'],
                'Item_Description'         => $line['description'],
                'Standard_ItemID'          => $line['item_code'],
                'Standard_ItemID_SchemeID' => self::ITEM_SCHEME_ID,
            ];
        }

        return $blocks;
    }

    /**
     * Impuestos por línea. Van en su propio bloque, no dentro de la línea.
     *
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    private function lineTaxBlocks(array $lines): array
    {
        $blocks = [];

        foreach ($lines as $line) {
            $blocks[] = [
                'ID'                        => (string) $line['id'],
                'TaxAmount'                 => $line['tax_amount'],
                'SchemeID'                  => self::TAX_SCHEME_ID,
                'SchemeName'                => self::TAX_SCHEME_NAME,
                'TaxSubTotal_TaxableAmount' => $line['line_amount'],
                'TaxSubTotal_TaxAmount'     => $line['tax_amount'],
                'TaxSubTotal_Percent'       => $line['tax_percentage'],
            ];
        }

        return $blocks;
    }

    /** @return list<array<string, mixed>> */
    private function deliveryBlocks(WorkOrderInvoice $invoice, BusinessDianSetting $setting): array
    {
        $client = $invoice->workOrder?->client;

        return [[
            'Nombre'         => (string) $client?->name,
            'Email'          => (string) ($client?->email ?? ''),
            'Enviar_Email'   => $setting->notify_customer && filled($client?->email),
            'Incluir_Anexos' => $setting->include_attachments,
            'Incluir_PDF'    => $setting->include_pdf,
            'Incluir_XML'    => $setting->include_xml,
        ]];
    }

    /** @return list<array<string, string>> */
    private function addressBlocks(WorkOrderInvoice $invoice): array
    {
        $business = $invoice->business;
        $client = $invoice->workOrder?->client;

        return [
            ['ID' => self::ADDRESS_ISSUER] + $this->addressValues(
                $business?->city,
                (string) ($business?->address ?? ''),
                (string) ($business?->postal_code ?? '')
            ),
            ['ID' => self::ADDRESS_CUSTOMER] + $this->addressValues(
                $client?->city ?: $business?->city,
                (string) ($client?->address ?? ''),
                ''
            ),
        ];
    }

    /**
     * Contactos de ambas partes. La DIAN los exige y, si faltan, el proveedor
     * los reemplaza por los del perfil.
     *
     * @return list<array<string, string>>
     */
    private function contactBlocks(WorkOrderInvoice $invoice): array
    {
        $business = $invoice->business;
        $client = $invoice->workOrder?->client;

        return [
            [
                'ID'             => self::CONTACT_ISSUER,
                'Name'           => (string) $business?->name,
                'Telephone'      => (string) ($business?->phone_number ?? ''),
                'ElectronicMail' => (string) ($business?->email ?? ''),
                'Note'           => '',
            ],
            [
                'ID'             => self::CONTACT_CUSTOMER,
                'Name'           => (string) ($client?->contact_name ?: $client?->name),
                'Telephone'      => (string) ($client?->phone ?? ''),
                'ElectronicMail' => (string) ($client?->email ?? ''),
                'Note'           => '',
            ],
        ];
    }

    /* ----------------------------------------------------------------- *
     |  Cálculo de líneas y totales
     * ----------------------------------------------------------------- */

    /** @return list<array<string, mixed>> */
    private function buildLines(WorkOrderInvoice $invoice): array
    {
        $tax_percentage = round((float) $invoice->tax_percentage, 2);
        $lines = [];
        $position = 0;

        foreach ($invoice->items as $invoice_item) {
            $item = $invoice_item->workOrderItem;

            if (! $item) {
                continue;
            }

            $quantity = round((float) $invoice_item->quantity, 2);
            $unit_price = round((float) $item->unit_price, 2);
            $discount = (float) $item->discount_percentage;
            $line_amount = round($quantity * $unit_price * (1 - $discount / 100), 2);

            $product = $item->catalogProduct;
            $position++;

            $lines[] = [
                'id'             => $position,
                'quantity'       => $quantity,
                'unit_price'     => $unit_price,
                'line_amount'    => $line_amount,
                'unit_code'      => (string) ($product?->unit?->unece_code ?: 'NIU'),
                'description'    => (string) $item->description,
                'item_code'      => (string) ($product?->sku ?: $position),
                'tax_percentage' => $tax_percentage,
                'tax_amount'     => round($line_amount * $tax_percentage / 100, 2),
            ];
        }

        return $this->spreadInvoiceDiscount($lines, round((float) $invoice->discount_amount, 2), $tax_percentage);
    }

    /**
     * Reparte el descuento de la OT (el cupón) entre las líneas, en proporción a
     * lo que pesa cada una.
     *
     * El descuento del cupón es del documento completo, pero este formato solo
     * conoce importes por línea y deriva los totales de ellas. Si no se repartiera,
     * se le facturaría a la DIAN más de lo que el cliente realmente paga.
     *
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    private function spreadInvoiceDiscount(array $lines, float $discount, float $tax_percentage): array
    {
        $total = round(array_sum(array_column($lines, 'line_amount')), 2);

        if ($discount <= 0 || $total <= 0) {
            return $lines;
        }

        $discount = min($discount, $total);
        $assigned = 0.0;
        $last     = array_key_last($lines);

        foreach ($lines as $index => $line) {
            // La última línea absorbe el redondeo para que la suma cuadre al centavo.
            $line_discount = $index === $last
                ? round($discount - $assigned, 2)
                : round($discount * $line['line_amount'] / $total, 2);

            $assigned += $line_discount;

            $line_amount = round($line['line_amount'] - $line_discount, 2);
            $quantity    = (float) $line['quantity'];

            $lines[$index]['line_amount'] = $line_amount;
            $lines[$index]['unit_price']  = $quantity > 0 ? round($line_amount / $quantity, 2) : 0.0;
            $lines[$index]['tax_amount']  = round($line_amount * $tax_percentage / 100, 2);
        }

        return $lines;
    }

    /**
     * Los totales se derivan de las líneas para garantizar que el documento sea
     * internamente consistente, que es lo que valida la DIAN.
     *
     * @param  list<array<string, mixed>>  $lines
     * @return array<string, float>
     */
    private function calculateTotals(WorkOrderInvoice $invoice, array $lines): array
    {
        $line_extension = round(array_sum(array_column($lines, 'line_amount')), 2);
        $tax_amount = round(array_sum(array_column($lines, 'tax_amount')), 2);

        return [
            'line_extension' => $line_extension,
            // La base imponible del documento debe cuadrar con la suma de las bases
            // de cada línea, incluso cuando la tarifa del impuesto es cero.
            'taxable_base'   => $line_extension,
            'tax_amount'     => $tax_amount,
            'tax_percentage' => round((float) $invoice->tax_percentage, 2),
            'tax_inclusive'  => round($line_extension + $tax_amount, 2),
            'payable'        => round($line_extension + $tax_amount, 2),
        ];
    }

    /* ----------------------------------------------------------------- *
     |  Utilidades
     * ----------------------------------------------------------------- */

    /** @return array<string, string> */
    private function addressValues(?City $city, string $address_line, string $postal_code): array
    {
        $country = $city?->country;
        $department = (string) ($city?->department_name ?: $city?->state_province ?: '');

        return [
            'CityID'               => (string) ($city?->dane_code ?? ''),
            'CityName'             => mb_strtoupper((string) ($city?->name ?? '')),
            'PostalZone'           => $postal_code !== '' ? $postal_code : '000000',
            'CountrySubentity'     => mb_strtoupper($department),
            'CountrySubentityCode' => (string) ($city?->department_code ?? ''),
            'AddressLine'          => $address_line,
            'CountryName'          => mb_strtoupper((string) ($country?->name ?: config('dian.defaults.country_name'))),
            'CountryCode'          => (string) ($country?->iso_alpha2 ?: config('dian.defaults.country_code')),
        ];
    }

    /** Código DIAN del tipo de documento de identificación. */
    private function documentTypeCode(?string $document_type): string
    {
        return match ($document_type) {
            'NIT' => '31',
            'CC'  => '13',
            'CE'  => '22',
            'PA'  => '41',
            'TI'  => '12',
            'PPT' => '47',
            default => '13',
        };
    }

    /** @param array<string, mixed> $values */
    private function xmlBlock(DOMDocument $dom, string $block, array $values): DOMElement
    {
        $element = $dom->createElement($block);

        foreach ($values as $tag => $value) {
            $text = is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
            $child = $dom->createElement($tag);

            if ($text !== '') {
                $child->appendChild($dom->createTextNode($text));
            }

            $element->appendChild($child);
        }

        return $element;
    }
}
