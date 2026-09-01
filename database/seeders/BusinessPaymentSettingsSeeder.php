<?php

namespace Database\Seeders;

use App\Enums\BusinessBankAccountType;
use App\Models\Bank;
use App\Models\Business;
use App\Models\BusinessBankAccount;
use App\Models\BusinessPaymentMethod;
use Illuminate\Database\Seeder;

class BusinessPaymentSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::query()->where('slug', 'transportes-transad')->first();

        if (! $business) {
            $this->command?->warn('BusinessPaymentSettingsSeeder: no se encontró transportes-transad.');

            return;
        }

        $this->seedPaymentMethods();
        $this->seedBankAccounts($business);

        $this->command?->info('Métodos de pago y datos bancarios sincronizados.');
    }

    private function seedPaymentMethods(): void
    {
        $general_methods = [
            ['name' => 'Efectivo', 'sort_order' => 10, 'is_default' => false],
            ['name' => 'Transferencia bancaria', 'sort_order' => 20, 'is_default' => true],
            ['name' => 'Tarjeta débito', 'sort_order' => 30, 'is_default' => false],
            ['name' => 'Tarjeta crédito', 'sort_order' => 40, 'is_default' => false],
            ['name' => 'Cheque', 'sort_order' => 50, 'is_default' => false],
        ];

        foreach ($general_methods as $data) {
            BusinessPaymentMethod::query()->updateOrCreate(
                [
                    'name'    => $data['name'],
                    'general' => true,
                ],
                [
                    'business_id' => null,
                    'label'       => BusinessPaymentMethod::normalizeLabel($data['name']),
                    'active'      => true,
                    'is_default'  => $data['is_default'],
                    'sort_order'  => $data['sort_order'],
                ]
            );
        }
    }

    private function seedBankAccounts(Business $business): void
    {
        $bancolombia = Bank::query()->where('name', 'Bancolombia')->first();

        BusinessBankAccount::query()->updateOrCreate(
            [
                'business_id'    => $business->id,
                'account_number' => '123 456 789 01',
            ],
            [
                'bank_id'          => $bancolombia?->id,
                'bank_name'        => 'Bancolombia',
                'account_type'     => BusinessBankAccountType::Corriente,
                'account_holder'   => $business->name,
                'document_type'    => 'NIT',
                'document_number'  => '900.123.456-1',
                'is_default'       => true,
                'active'           => true,
            ]
        );
    }
}
