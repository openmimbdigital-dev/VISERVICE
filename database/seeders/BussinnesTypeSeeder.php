<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Seeder;

class BussinnesTypeSeeder extends Seeder
{
    public function run(): void
    {
        $business_types = [
            ['name' => 'Transporte de Carga', 'status' => true],
            ['name' => 'Transporte de Pasajeros', 'status' => true],
            ['name' => 'Transporte de Mercancías', 'status' => true],
            ['name' => 'Logística y Distribución', 'status' => true],
            ['name' => 'Servicios de Mensajería', 'status' => true],
            ['name' => 'Transporte Especializado', 'status' => true],
            ['name' => 'Almacenamiento y Depósito', 'status' => true],
            ['name' => 'Servicios Portuarios', 'status' => true],
            ['name' => 'Servicios Aeroportuarios', 'status' => true],
            ['name' => 'Mantenimiento de Vehículos', 'status' => true],
            ['name' => 'Venta de Repuestos', 'status' => true],
            ['name' => 'Servicios de Seguridad', 'status' => true],
            ['name' => 'Servicios de Limpieza', 'status' => true],
            ['name' => 'Servicios de Alimentación', 'status' => true],
            ['name' => 'Servicios de Combustible', 'status' => true],
            ['name' => 'Servicios de Seguros', 'status' => true],
            ['name' => 'Servicios de Tecnología', 'status' => true],
            ['name' => 'Servicios de Consultoría', 'status' => true],
            ['name' => 'Servicios de Capacitación', 'status' => true],
            ['name' => 'Servicios de Contabilidad', 'status' => true],
            ['name' => 'Colegio', 'status' => true],
            ['name' => 'Iglesia', 'status' => true],
        ];

        foreach ($business_types as $type) {
            $label = BusinessType::normalizeLabel($type['name']);

            BusinessType::query()->updateOrCreate(
                ['label' => $label],
                [
                    'name'   => $type['name'],
                    'status' => $type['status'],
                ]
            );
        }

        $this->command->info('Tipos de empresas sincronizados exitosamente.');
    }
}
