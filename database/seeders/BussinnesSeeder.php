<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessType;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BussinnesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existen datos en la tabla
        if (Business::count() > 0) {
            $this->command->info('La tabla businesses ya tiene datos. No se insertarán nuevos registros.');
            return;
        }

        // Obtener tipos de negocio
        $transportCarga = BusinessType::where('name', 'Transporte de Carga')->first();
        $transportPasajeros = BusinessType::where('name', 'Transporte de Pasajeros')->first();
        $logistica = BusinessType::where('name', 'Logística y Distribución')->first();
        $mensajeria = BusinessType::where('name', 'Servicios de Mensajería')->first();
        $mantenimiento = BusinessType::where('name', 'Mantenimiento de Vehículos')->first();

        // Obtener ciudades
        $bogota = City::where('code', 'BOG')->first();
        $medellin = City::where('code', 'MED')->first();
        $cali = City::where('code', 'CAL')->first();
        $barranquilla = City::where('code', 'BAQ')->first();
        $cartagena = City::where('code', 'CTG')->first();

        // Obtener países
        $colombia = Country::where('code', 'COL')->first();

        $businesses = [
            // Empresas de Transporte de Carga
            [
                'name' => 'Transportes TRANSAD',
                'address' => 'Carrera 68 # 25-47',
                'email' => 'info@transad.com.co',
                'slug' => 'transportes-transad',
                'nit' => '900123456-1',
                'logo' => 'logos/transad-logo.png',
                'website' => 'https://www.transad.com.co',
                'phone_number' => '+57 1 234 5678',
                'city_id' => $bogota->id,
                'country_id' => $colombia->id,
                'business_type_id' => $transportCarga->id,
                'representative' => [
                    'name' => 'Juan Carlos Pérez',
                    'document_type' => 'CC',
                    'document_number' => '12345678',
                    'phone' => '+57 300 123 4567',
                    'email' => 'jperez@transad.com.co'
                ],
                'configurations' => [
                    'max_vehicles' => 50,
                    'service_areas' => ['Bogotá', 'Medellín', 'Cali'],
                    'specializations' => ['Carga pesada', 'Carga refrigerada', 'Carga peligrosa']
                ],
                'status' => true,
            ],
            [
                'name' => 'Carga Rápida S.A.S',
                'address' => 'Calle 25 # 68-90',
                'email' => 'contacto@cargarapida.com',
                'slug' => 'carga-rapida-sas',
                'nit' => '900234567-1',
                'phone_number' => '+57 1 345 6789',
                'city_id' => $medellin->id,
                'country_id' => $colombia->id,
                'business_type_id' => $transportCarga->id,
                'representative' => [
                    'name' => 'María Elena Rodríguez',
                    'document_type' => 'CC',
                    'document_number' => '23456789',
                    'phone' => '+57 300 234 5678',
                    'email' => 'mrodriguez@cargarapida.com'
                ],
                'status' => true,
            ],
            [
                'name' => 'Transportes del Valle',
                'address' => 'Avenida 6N # 28-75',
                'email' => 'info@transvalle.com',
                'slug' => 'transportes-del-valle',
                'nit' => '900345678-1',
                'phone_number' => '+57 2 456 7890',
                'city_id' => $cali->id,
                'country_id' => $colombia->id,
                'business_type_id' => $transportCarga->id,
                'representative' => [
                    'name' => 'Carlos Alberto López',
                    'document_type' => 'CC',
                    'document_number' => '34567890',
                    'phone' => '+57 300 345 6789',
                    'email' => 'clopez@transvalle.com'
                ],
                'status' => true,
            ],

            // Empresas de Transporte de Pasajeros
            [
                'name' => 'Flota Magdalena',
                'address' => 'Carrera 46 # 74-174',
                'email' => 'ventas@flotamagdalena.com',
                'slug' => 'flota-magdalena',
                'nit' => '900456789-1',
                'phone_number' => '+57 5 567 8901',
                'city_id' => $barranquilla->id,
                'country_id' => $colombia->id,
                'business_type_id' => $transportPasajeros->id,
                'representative' => [
                    'name' => 'Ana Patricia González',
                    'document_type' => 'CC',
                    'document_number' => '45678901',
                    'phone' => '+57 300 456 7890',
                    'email' => 'agonzalez@flotamagdalena.com'
                ],
                'status' => true,
            ],
            [
                'name' => 'Transportes Caribe',
                'address' => 'Calle 30 # 8-15',
                'email' => 'info@transcaribe.com',
                'slug' => 'transportes-caribe',
                'nit' => '900567890-1',
                'phone_number' => '+57 5 678 9012',
                'city_id' => $cartagena->id,
                'country_id' => $colombia->id,
                'business_type_id' => $transportPasajeros->id,
                'representative' => [
                    'name' => 'Roberto Carlos Martínez',
                    'document_type' => 'CC',
                    'document_number' => '56789012',
                    'phone' => '+57 300 567 8901',
                    'email' => 'rmartinez@transcaribe.com'
                ],
                'status' => true,
            ],

            // Empresas de Logística
            [
                'name' => 'Logística Integral S.A.S',
                'address' => 'Carrera 7 # 32-16',
                'email' => 'servicios@logisticaintegral.com',
                'slug' => 'logistica-integral-sas',
                'nit' => '900678901-1',
                'website' => 'https://www.logisticaintegral.com',
                'phone_number' => '+57 1 789 0123',
                'city_id' => $bogota->id,
                'country_id' => $colombia->id,
                'business_type_id' => $logistica->id,
                'representative' => [
                    'name' => 'Luis Fernando Silva',
                    'document_type' => 'CC',
                    'document_number' => '67890123',
                    'phone' => '+57 300 678 9012',
                    'email' => 'lsilva@logisticaintegral.com'
                ],
                'configurations' => [
                    'warehouses' => 3,
                    'service_areas' => ['Nacional', 'Internacional'],
                    'specializations' => ['Almacenamiento', 'Distribución', 'Cross-docking']
                ],
                'status' => true,
            ],

            // Empresas de Mensajería
            [
                'name' => 'Mensajería Express',
                'address' => 'Calle 80 # 11-42',
                'email' => 'envios@mensajeriaexpress.com',
                'slug' => 'mensajeria-express',
                'nit' => '900789012-1',
                'phone_number' => '+57 1 890 1234',
                'city_id' => $bogota->id,
                'country_id' => $colombia->id,
                'business_type_id' => $mensajeria->id,
                'representative' => [
                    'name' => 'Patricia Alejandra Herrera',
                    'document_type' => 'CC',
                    'document_number' => '78901234',
                    'phone' => '+57 300 789 0123',
                    'email' => 'pherrera@mensajeriaexpress.com'
                ],
                'status' => true,
            ],

            // Empresas de Mantenimiento
            [
                'name' => 'Taller Mecánico El Motor',
                'address' => 'Carrera 50 # 25-30',
                'email' => 'servicios@elmotor.com',
                'slug' => 'taller-mecanico-el-motor',
                'nit' => '900890123-1',
                'phone_number' => '+57 1 901 2345',
                'city_id' => $bogota->id,
                'country_id' => $colombia->id,
                'business_type_id' => $mantenimiento->id,
                'representative' => [
                    'name' => 'Miguel Ángel Vargas',
                    'document_type' => 'CC',
                    'document_number' => '89012345',
                    'phone' => '+57 300 890 1234',
                    'email' => 'mvargas@elmotor.com'
                ],
                'status' => true,
            ],
        ];

        foreach ($businesses as $business) {
            Business::create($business);
        }

        $this->command->info('Empresas de transporte creadas exitosamente.');
    }
}
