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
 * El documento se arma primero como arreglo (bloques EXT, FAC, NOT, ASP, ACP, PYM,
 * TOT, TAX, IVL, REC, ADD) y de ahí se serializa a JSON o a XML, según el formato
 * de entrada con el que el proveedor haya configurado el perfil del emisor.
 */
class InvoiceDocumentBuilder
{
    /** Identificadores internos de las direcciones referenciadas por ASP y ACP. */
    private const ADDRESS_ISSUER_PHYSICAL = 'ED1';
    private const ADDRESS_ISSUER_FISCAL = 'ED2';
    private const ADDRESS_CUSTOMER_PHYSICAL = 'AD1';
    private const ADDRESS_CUSTOMER_FISCAL = 'AD2';

    /** Esquema de identificación DIAN: 31 = NIT. */
    private const SCHEME_NAME_NIT = '31';

    /** Estándar de adopción del contribuyente para el código de producto. */
    private const ITEM_SCHEME_ID = '999';


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

        // Cada bloque va como lista de ocurrencias: el conversor del proveedor descarta
        // los bloques enviados como objeto suelto y los genera vacíos.
        $document = [
            'EXT' => [$this->extensionBlock($setting)],
            'FAC' => [$this->invoiceHeaderBlock($electronic_invoice, $setting, count($lines))],
            'NOT' => [['Note' => (string) ($invoice->notes ?? '')]],
            'ASP' => [$this->issuerBlock($invoice, $setting)],
            'ACP' => [$this->customerBlock($invoice)],
            'PYM' => [$this->paymentBlock($invoice, $electronic_invoice)],
            'TOT' => [$this->totalsBlock($totals)],
        ];

        if ($totals['tax_amount'] > 0) {
            $document['TAX'] = [$this->taxBlock($totals)];
        }

        $document['IVL'] = $this->lineBlocks($lines);
        $document['REC'] = [$this->deliveryBlock($invoice, $setting)];
        $document['ADD'] = $this->addressBlocks($invoice);

        return ['Document' => $document];
    }

    /** @param array<string, mixed> $document */
    public function toJson(array $document): string
    {
        return (string) json_encode(
            $document,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }

    /** @param array<string, mixed> $document */
    public function toXml(array $document): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('Document');
        $dom->appendChild($root);

        foreach ((array) ($document['Document'] ?? []) as $block => $occurrences) {
            foreach ((array) $occurrences as $values) {
                $root->appendChild($this->xmlBlock($dom, $block, (array) $values));
            }
        }

        return (string) $dom->saveXML();
    }

    /* ----------------------------------------------------------------- *
     |  Bloques del documento
     * ----------------------------------------------------------------- */

    /** @return array<string, string> */
    private function extensionBlock(BusinessDianSetting $setting): array
    {
        $defaults = (array) config('dian.defaults');

        return [
            'InvoiceAuthorization'             => (string) $setting->resolution_number,
            'StartDate'                        => $setting->valid_from?->format('Y-m-d') ?? '',
            'EndDate'                          => $setting->valid_to?->format('Y-m-d') ?? '',
            'Prefix'                           => (string) $setting->prefix,
            'From'                             => (string) $setting->range_from,
            'To'                               => (string) $setting->range_to,
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

    /** @return array<string, string> */
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
            'UUID'                 => 'UUID',
            'IssueDate'            => $issued_at->format('Y-m-d'),
            'IssueTime'            => $issued_at->format('H:i:s'),
            'InvoiceTypeCode'      => (string) $defaults['invoice_type_code'],
            'DocumentCurrencyCode' => (string) $defaults['currency_code'],
            'LineCountNumeric'     => (string) $line_count,
        ];
    }

    /** @return array<string, string> */
    private function issuerBlock(WorkOrderInvoice $invoice, BusinessDianSetting $setting): array
    {
        $business = $invoice->business;

        return [
            'AdditionalAccountID'            => (string) ($business?->person_type ?: 1),
            'PartyName'                      => (string) $business?->name,
            'Physical_ADD_ID'                => self::ADDRESS_ISSUER_PHYSICAL,
            'Tax_RegistrationName'           => (string) $business?->name,
            'Tax_CompanyID'                  => DianNit::normalize($business?->nit),
            'Tax_CompanyID_schemeID'         => (string) DianNit::resolveVerificationDigit($business?->nit, $business?->verification_digit),
            'Tax_CompanyID_schemeName'       => self::SCHEME_NAME_NIT,
            'Tax_LevelCode'                  => (string) ($business?->fiscal_responsibilities ?: 'R-99-PN'),
            'Tax_LevelCode_listName'         => 'No aplica',
            'Tax_Scheme_ID'                  => '01',
            'Tax_Scheme_Name'                => 'IVA',
            'Registration_ADD_ID'            => self::ADDRESS_ISSUER_FISCAL,
            'CorporateRegistrationScheme_ID' => (string) $setting->prefix,
        ];
    }

    /** @return array<string, string> */
    private function customerBlock(WorkOrderInvoice $invoice): array
    {
        $client = $invoice->workOrder?->client;
        $is_company = (int) ($client?->person_type ?: 2) === 1;

        return [
            'AdditionalAccountID'      => (string) ($client?->person_type ?: 2),
            'PartyName'                => (string) $client?->name,
            'Physical_ADD_ID'          => self::ADDRESS_CUSTOMER_PHYSICAL,
            'Tax_RegistrationName'     => (string) $client?->name,
            'Tax_CompanyID'            => DianNit::normalize($client?->document_number),
            'Tax_CompanyID_schemeID'   => $is_company
                ? (string) DianNit::resolveVerificationDigit($client?->document_number, $client?->verification_digit)
                : '',
            'Tax_CompanyID_schemeName' => $this->documentTypeCode($client?->document_type),
            'Tax_LevelCode'            => (string) ($client?->fiscal_responsibilities ?: 'R-99-PN'),
            'Tax_LevelCode_listName'   => 'No aplica',
            'Tax_Scheme_ID'            => $is_company ? '01' : 'ZZ',
            'Tax_Scheme_Name'          => $is_company ? 'IVA' : 'No aplica',
            'Registration_ADD_ID'      => self::ADDRESS_CUSTOMER_FISCAL,
            'DeliveryContact_ID'       => 'ADC1',
        ];
    }

    /** @return array<string, string> */
    private function paymentBlock(WorkOrderInvoice $invoice, ElectronicInvoice $electronic_invoice): array
    {
        $issued_at = $electronic_invoice->issued_at ?? now();
        $due_date = $invoice->due_date;

        // 1 = Contado, 2 = Crédito.
        $is_credit = $due_date !== null && $due_date->gt($issued_at);

        return [
            'ID'               => $is_credit ? '2' : '1',
            'PaymentMeansCode' => '1',
            'PaymentDueDate'   => ($due_date ?? $issued_at)->format('Y-m-d'),
        ];
    }

    /**
     * @param  array<string, float>  $totals
     * @return array<string, string>
     */
    private function totalsBlock(array $totals): array
    {
        return [
            'LineExtensionAmount' => $this->amount($totals['line_extension']),
            'TaxExclusiveAmount'  => $this->amount($totals['taxable_base']),
            'TaxInclusiveAmount'  => $this->amount($totals['tax_inclusive']),
            'PayableAmount'       => $this->amount($totals['payable']),
        ];
    }

    /**
     * Totales de impuestos del documento.
     *
     * El documento de referencia del proveedor corresponde a una factura sin IVA, por lo
     * que este bloque sigue la nomenclatura del propio dialecto y el estándar DIAN. Si el
     * proveedor lo rechaza, este es el único punto a ajustar.
     *
     * @param  array<string, float>  $totals
     * @return array<string, string>
     */
    private function taxBlock(array $totals): array
    {
        return [
            'ID'              => '01',
            'TaxAmount'       => $this->amount($totals['tax_amount']),
            'TaxableAmount'   => $this->amount($totals['taxable_base']),
            'Percent'         => $this->amount($totals['tax_percentage']),
            'Tax_Scheme_ID'   => '01',
            'Tax_Scheme_Name' => 'IVA',
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, string>>
     */
    private function lineBlocks(array $lines): array
    {
        $blocks = [];

        foreach ($lines as $line) {
            $values = [
                'ID'                       => (string) $line['id'],
                'InvoicedQuantity'         => $this->amount($line['quantity']),
                'InvoicedQuantityUnitCode' => $line['unit_code'],
                'LineExtensionAmount'      => $this->amount($line['line_amount']),
                'PriceAmount'              => $this->amount($line['unit_price']),
                'BaseQuantity'             => $this->amount($line['quantity']),
                'BaseQuantity_unitCode'    => $line['unit_code'],
                'Item_Description'         => $line['description'],
                'Standard_ItemID'          => $line['item_code'],
                'Standard_ItemID_SchemeID' => self::ITEM_SCHEME_ID,
            ];

            if ($line['tax_amount'] > 0) {
                $values['Tax_TaxAmount']     = $this->amount($line['tax_amount']);
                $values['Tax_TaxableAmount'] = $this->amount($line['line_amount']);
                $values['Tax_Percent']       = $this->amount($line['tax_percentage']);
                $values['Tax_Scheme_ID']     = '01';
                $values['Tax_Scheme_Name']   = 'IVA';
            }

            $blocks[] = $values;
        }

        return $blocks;
    }

    /** @return array<string, string> */
    private function deliveryBlock(WorkOrderInvoice $invoice, BusinessDianSetting $setting): array
    {
        $client = $invoice->workOrder?->client;

        return [
            'Nombre'         => (string) $client?->name,
            'Email'          => (string) ($client?->email ?? ''),
            'Enviar_Email'   => $this->boolean($setting->notify_customer && filled($client?->email)),
            'Incluir_Anexos' => $this->boolean($setting->include_attachments),
            'Incluir_PDF'    => $this->boolean($setting->include_pdf),
            'Incluir_XML'    => $this->boolean($setting->include_xml),
        ];
    }

    /** @return list<array<string, string>> */
    private function addressBlocks(WorkOrderInvoice $invoice): array
    {
        $business = $invoice->business;
        $client = $invoice->workOrder?->client;

        $issuer = $this->addressValues(
            $business?->city,
            (string) ($business?->address ?? ''),
            (string) ($business?->postal_code ?? '')
        );

        $customer = $this->addressValues(
            $client?->city ?: $business?->city,
            (string) ($client?->address ?? ''),
            ''
        );

        return [
            ['ID' => self::ADDRESS_ISSUER_PHYSICAL] + $issuer,
            ['ID' => self::ADDRESS_ISSUER_FISCAL] + $issuer,
            ['ID' => self::ADDRESS_CUSTOMER_PHYSICAL] + $customer,
            ['ID' => self::ADDRESS_CUSTOMER_FISCAL] + $customer,
        ];
    }

    /* ----------------------------------------------------------------- *
     |  Cálculo de líneas y totales
     * ----------------------------------------------------------------- */

    /** @return list<array<string, mixed>> */
    private function buildLines(WorkOrderInvoice $invoice): array
    {
        $tax_percentage = (float) $invoice->tax_percentage;
        $lines = [];
        $position = 0;

        foreach ($invoice->items as $invoice_item) {
            $item = $invoice_item->workOrderItem;

            if (! $item) {
                continue;
            }

            $quantity = (float) $invoice_item->quantity;
            $unit_price = (float) $item->unit_price;
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
            'taxable_base'   => $tax_amount > 0 ? $line_extension : 0.0,
            'tax_amount'     => $tax_amount,
            'tax_percentage' => (float) $invoice->tax_percentage,
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

        return [
            'CityID'               => (string) ($city?->dane_code ?? ''),
            'CityName'             => (string) ($city?->name ?? ''),
            'PostalZone'           => $postal_code !== '' ? $postal_code : '000000',
            'CountrySubentity'     => (string) ($city?->department_name ?: $city?->state_province ?: ''),
            'CountrySubentityCode' => (string) ($city?->department_code ?? ''),
            'AddressLine'          => $address_line,
            'CountryName'          => (string) ($country?->name ?: config('dian.defaults.country_name')),
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

    /** @param array<string, string> $values */
    private function xmlBlock(DOMDocument $dom, string $block, array $values): DOMElement
    {
        $element = $dom->createElement($block);

        foreach ($values as $tag => $value) {
            $child = $dom->createElement($tag);

            if ((string) $value !== '') {
                $child->appendChild($dom->createTextNode((string) $value));
            }

            $element->appendChild($child);
        }

        return $element;
    }

    private function amount(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private function boolean(bool $value): string
    {
        return $value ? 'true' : 'false';
    }
}
