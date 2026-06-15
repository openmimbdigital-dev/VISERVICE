<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BanksSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            ['name' => 'Bancolombia',                   'code' => '007', 'is_active' => true],
            ['name' => 'Davivienda',                    'code' => '051', 'is_active' => true],
            ['name' => 'Banco de Bogotá',               'code' => '001', 'is_active' => true],
            ['name' => 'BBVA Colombia',                 'code' => '013', 'is_active' => true],
            ['name' => 'Banco Popular',                 'code' => '002', 'is_active' => true],
            ['name' => 'Banco de Occidente',            'code' => '023', 'is_active' => true],
            ['name' => 'Banco AV Villas',               'code' => '052', 'is_active' => true],
            ['name' => 'Scotiabank Colpatria',          'code' => '019', 'is_active' => true],
            ['name' => 'Itaú Colombia',                 'code' => '006', 'is_active' => true],
            ['name' => 'Banco Caja Social',             'code' => '032', 'is_active' => true],
            ['name' => 'GNB Sudameris',                 'code' => '012', 'is_active' => true],
            ['name' => 'Banco Falabella',               'code' => '062', 'is_active' => true],
            ['name' => 'Banco Finandina',               'code' => '063', 'is_active' => true],
            ['name' => 'Banco W',                       'code' => '047', 'is_active' => true],
            ['name' => 'Bancamía',                      'code' => '059', 'is_active' => true],
            ['name' => 'Banco Serfinanza',              'code' => '069', 'is_active' => true],
            ['name' => 'Confiar Cooperativa',           'code' => '292', 'is_active' => true],
            ['name' => 'Cotrafa Cooperativa',           'code' => '289', 'is_active' => true],
            ['name' => 'JFK Cooperativa Financiera',    'code' => '286', 'is_active' => true],
            ['name' => 'Nequi',                         'code' => '507', 'is_active' => true],
            ['name' => 'Daviplata',                     'code' => '551', 'is_active' => true],
            ['name' => 'Lulo Bank',                     'code' => '070', 'is_active' => true],
            ['name' => 'Nu Colombia (Nubank)',           'code' => '637', 'is_active' => true],
            ['name' => 'Movii',                         'code' => '802', 'is_active' => true],
            ['name' => 'Rappipay',                      'code' => '811', 'is_active' => true],
        ];

        foreach ($banks as $bank) {
            Bank::firstOrCreate(['name' => $bank['name']], $bank);
        }
    }
}
