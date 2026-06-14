<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existen datos en la tabla
        if (City::count() > 0) {
            $this->command->info('La tabla cities ya tiene datos. No se insertarán nuevos registros.');
            return;
        }

        // Obtener Colombia
        $colombia = Country::where('code', 'COL')->first();

        if (!$colombia) {
            $this->command->error('Colombia no encontrada. Ejecuta primero CountrySeeder.');
            return;
        }

        $cities = [
            // Ciudades principales de Colombia
            [
                'name' => 'Bogotá',
                'code' => 'BOG',
                'country_id' => $colombia->id,
                'state_province' => 'Cundinamarca',
                'latitude' => 4.6097100,
                'longitude' => -74.0817500,
                'is_active' => true,
            ],
            [
                'name' => 'Medellín',
                'code' => 'MED',
                'country_id' => $colombia->id,
                'state_province' => 'Antioquia',
                'latitude' => 6.2518400,
                'longitude' => -75.5635900,
                'is_active' => true,
            ],
            [
                'name' => 'Cali',
                'code' => 'CAL',
                'country_id' => $colombia->id,
                'state_province' => 'Valle del Cauca',
                'latitude' => 3.4372200,
                'longitude' => -76.5225000,
                'is_active' => true,
            ],
            [
                'name' => 'Barranquilla',
                'code' => 'BAQ',
                'country_id' => $colombia->id,
                'state_province' => 'Atlántico',
                'latitude' => 10.9685400,
                'longitude' => -74.7813200,
                'is_active' => true,
            ],
            [
                'name' => 'Cartagena',
                'code' => 'CTG',
                'country_id' => $colombia->id,
                'state_province' => 'Bolívar',
                'latitude' => 10.3997200,
                'longitude' => -75.5144400,
                'is_active' => true,
            ],
            [
                'name' => 'Cúcuta',
                'code' => 'CUC',
                'country_id' => $colombia->id,
                'state_province' => 'Norte de Santander',
                'latitude' => 7.8939100,
                'longitude' => -72.5078200,
                'is_active' => true,
            ],
            [
                'name' => 'Bucaramanga',
                'code' => 'BGA',
                'country_id' => $colombia->id,
                'state_province' => 'Santander',
                'latitude' => 7.1193500,
                'longitude' => -73.1227200,
                'is_active' => true,
            ],
            [
                'name' => 'Pereira',
                'code' => 'PEI',
                'country_id' => $colombia->id,
                'state_province' => 'Risaralda',
                'latitude' => 4.8133300,
                'longitude' => -75.6961100,
                'is_active' => true,
            ],
            [
                'name' => 'Santa Marta',
                'code' => 'SMR',
                'country_id' => $colombia->id,
                'state_province' => 'Magdalena',
                'latitude' => 11.2407900,
                'longitude' => -74.2110400,
                'is_active' => true,
            ],
            [
                'name' => 'Ibagué',
                'code' => 'IBE',
                'country_id' => $colombia->id,
                'state_province' => 'Tolima',
                'latitude' => 4.4388900,
                'longitude' => -75.2322200,
                'is_active' => true,
            ],
            [
                'name' => 'Pasto',
                'code' => 'PSO',
                'country_id' => $colombia->id,
                'state_province' => 'Nariño',
                'latitude' => 1.2136100,
                'longitude' => -77.2811100,
                'is_active' => true,
            ],
            [
                'name' => 'Manizales',
                'code' => 'MZL',
                'country_id' => $colombia->id,
                'state_province' => 'Caldas',
                'latitude' => 5.0688900,
                'longitude' => -75.5173800,
                'is_active' => true,
            ],
            [
                'name' => 'Neiva',
                'code' => 'NVA',
                'country_id' => $colombia->id,
                'state_province' => 'Huila',
                'latitude' => 2.9275000,
                'longitude' => -75.2877800,
                'is_active' => true,
            ],
            [
                'name' => 'Villavicencio',
                'code' => 'VVC',
                'country_id' => $colombia->id,
                'state_province' => 'Meta',
                'latitude' => 4.1420000,
                'longitude' => -73.6266400,
                'is_active' => true,
            ],
            [
                'name' => 'Armenia',
                'code' => 'AXM',
                'country_id' => $colombia->id,
                'state_province' => 'Quindío',
                'latitude' => 4.5338900,
                'longitude' => -75.6811100,
                'is_active' => true,
            ],
            [
                'name' => 'Valledupar',
                'code' => 'VUP',
                'country_id' => $colombia->id,
                'state_province' => 'Cesar',
                'latitude' => 10.4631400,
                'longitude' => -73.2532200,
                'is_active' => true,
            ],
            [
                'name' => 'Montería',
                'code' => 'MTR',
                'country_id' => $colombia->id,
                'state_province' => 'Córdoba',
                'latitude' => 8.7479800,
                'longitude' => -75.8814300,
                'is_active' => true,
            ],
            [
                'name' => 'Sincelejo',
                'code' => 'SIN',
                'country_id' => $colombia->id,
                'state_province' => 'Sucre',
                'latitude' => 9.3047200,
                'longitude' => -75.3977800,
                'is_active' => true,
            ],
            [
                'name' => 'Popayán',
                'code' => 'PPN',
                'country_id' => $colombia->id,
                'state_province' => 'Cauca',
                'latitude' => 2.4382300,
                'longitude' => -76.6131600,
                'is_active' => true,
            ],
            [
                'name' => 'Tunja',
                'code' => 'TUN',
                'country_id' => $colombia->id,
                'state_province' => 'Boyacá',
                'latitude' => 5.5352800,
                'longitude' => -73.3677800,
                'is_active' => true,
            ],
        ];

        foreach ($cities as $city) {
            City::create($city);
        }

        $this->command->info('Ciudades de Colombia creadas exitosamente.');
    }
}
