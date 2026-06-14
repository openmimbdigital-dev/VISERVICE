<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existen datos en la tabla
        if (Country::count() > 0) {
            $this->command->info('La tabla countries ya tiene datos. No se insertarán nuevos registros.');
            return;
        }

        $countries = [
            // Colombia
            [
                'name' => 'Colombia',
                'code' => 'COL',
                'phone_code' => '+57',
                'currency' => 'COP',
                'currency_symbol' => '$',
                'is_active' => true,
            ],
            // Estados Unidos
            [
                'name' => 'Estados Unidos',
                'code' => 'USA',
                'phone_code' => '+1',
                'currency' => 'USD',
                'currency_symbol' => '$',
                'is_active' => true,
            ],
            // Argentina
            [
                'name' => 'Argentina',
                'code' => 'ARG',
                'phone_code' => '+54',
                'currency' => 'ARS',
                'currency_symbol' => '$',
                'is_active' => true,
            ],
            // Brasil
            [
                'name' => 'Brasil',
                'code' => 'BRA',
                'phone_code' => '+55',
                'currency' => 'BRL',
                'currency_symbol' => 'R$',
                'is_active' => true,
            ],
            // Chile
            [
                'name' => 'Chile',
                'code' => 'CHL',
                'phone_code' => '+56',
                'currency' => 'CLP',
                'currency_symbol' => '$',
                'is_active' => true,
            ],
            // Perú
            [
                'name' => 'Perú',
                'code' => 'PER',
                'phone_code' => '+51',
                'currency' => 'PEN',
                'currency_symbol' => 'S/',
                'is_active' => true,
            ],
            // Ecuador
            [
                'name' => 'Ecuador',
                'code' => 'ECU',
                'phone_code' => '+593',
                'currency' => 'USD',
                'currency_symbol' => '$',
                'is_active' => true,
            ],
            // Venezuela
            [
                'name' => 'Venezuela',
                'code' => 'VEN',
                'phone_code' => '+58',
                'currency' => 'VES',
                'currency_symbol' => 'Bs',
                'is_active' => true,
            ],
            // Uruguay
            [
                'name' => 'Uruguay',
                'code' => 'URY',
                'phone_code' => '+598',
                'currency' => 'UYU',
                'currency_symbol' => '$',
                'is_active' => true,
            ],
            // Paraguay
            [
                'name' => 'Paraguay',
                'code' => 'PRY',
                'phone_code' => '+595',
                'currency' => 'PYG',
                'currency_symbol' => '₲',
                'is_active' => true,
            ],
            // Bolivia
            [
                'name' => 'Bolivia',
                'code' => 'BOL',
                'phone_code' => '+591',
                'currency' => 'BOB',
                'currency_symbol' => 'Bs',
                'is_active' => true,
            ],


        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}
