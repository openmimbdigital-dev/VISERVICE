<?php

namespace App\Support;

use App\Models\BusinessBankAccount;
use App\Models\BusinessPaymentMethod;
use App\Models\Client;
use App\Models\CustomTax;
use App\Models\QuotationServiceType;

class SearchableSelectCreate
{
    /**
     * Configuración de alta rápida según el modelo del select.
     *
     * @return array{component: string, permission: string, button: string, title: string}|null
     */
    public static function for(string $modelClass): ?array
    {
        return match ($modelClass) {
            Client::class => [
                'component'  => 'ui.searchable-create.client-modal',
                'permission' => 'workshop.clients.create',
                'button'     => 'Crear cliente',
                'title'      => 'Nuevo cliente',
            ],
            QuotationServiceType::class => [
                'component'  => 'ui.searchable-create.quotation-service-type-modal',
                'permission' => 'workshop.quotation_service_types.create',
                'button'     => 'Crear tipo de servicio',
                'title'      => 'Nuevo tipo de servicio',
            ],
            BusinessPaymentMethod::class => [
                'component'  => 'ui.searchable-create.business-payment-method-modal',
                'permission' => 'business_payment_methods.create',
                'button'     => 'Crear forma de pago',
                'title'      => 'Nueva forma de pago',
            ],
            BusinessBankAccount::class => [
                'component'  => 'ui.searchable-create.business-bank-account-modal',
                'permission' => 'business_bank_accounts.create',
                'button'     => 'Crear cuenta bancaria',
                'title'      => 'Nueva cuenta bancaria',
            ],
            CustomTax::class => [
                'component'  => 'ui.searchable-create.custom-tax-modal',
                'permission' => 'custom_taxes.create',
                'button'     => 'Crear impuesto',
                'title'      => 'Nuevo impuesto',
            ],
            default => null,
        };
    }
}
