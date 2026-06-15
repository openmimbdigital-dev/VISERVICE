<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $bancolombia = Bank::where('name', 'Bancolombia')->first();
        $davivienda  = Bank::where('name', 'Davivienda')->first();

        $accounts = [
            [
                'bank_id'         => $bancolombia?->id,
                'account_type'    => 'ahorros',
                'account_number'  => '123-456789-00',
                'account_holder'  => 'VISERVICE S.A.S.',
                'document_type'   => 'NIT',
                'document_number' => '900.123.456-7',
                'is_active'       => true,
                'notes'           => 'Cuenta principal para recepción de pagos.',
            ],
            [
                'bank_id'         => $davivienda?->id,
                'account_type'    => 'corriente',
                'account_number'  => '654-321000-01',
                'account_holder'  => 'VISERVICE S.A.S.',
                'document_type'   => 'NIT',
                'document_number' => '900.123.456-7',
                'is_active'       => true,
                'notes'           => null,
            ],
        ];

        foreach ($accounts as $account) {
            BankAccount::firstOrCreate(
                ['account_number' => $account['account_number']],
                $account
            );
        }
    }
}
