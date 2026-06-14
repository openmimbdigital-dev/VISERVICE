<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BussinnesTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existen datos en la tabla
        if (BusinessType::count() > 0) {
            $this->command->info('La tabla business_types ya tiene datos. No se insertarán nuevos registros.');
            return;
        }

        $businessTypes = [
            [
                'name' => 'Transporte de Carga',
                'status' => true,
            ],
            [
                'name' => 'Transporte de Pasajeros',
                'status' => true,
            ],
            [
                'name' => 'Transporte de Mercancías',
                'status' => true,
            ],
            [
                'name' => 'Logística y Distribución',
                'status' => true,
            ],
            [
                'name' => 'Servicios de Mensajería',
                'status' => true,
            ],
            [
                'name' => 'Transporte Especializado',
                'status' => true,
            ],
            [
                'name' => 'Almacenamiento y Depósito',
                'status' => true,
            ],
            [
                'name' => 'Servicios Portuarios',
                'status' => true,
            ],
            [
                'name' => 'Servicios Aeroportuarios',
                'status' => true,
            ],
            [
                'name' => 'Mantenimiento de Vehículos',
                'status' => true,
            ],
            [
                'name' => 'Venta de Repuestos',
                'status' => true,
            ],
            [
                'name' => 'Servicios de Seguridad',
                'status' => true,
            ],
            [
                'name' => 'Servicios de Limpieza',
                'status' => true,
            ],
            [
                'name' => 'Servicios de Alimentación',
                'status' => true,
            ],
            [
                'name' => 'Servicios de Combustible',
                'status' => true,
            ],
            [
                'name' => 'Servicios de Seguros',
                'status' => true,
            ],
            [
                'name' => 'Servicios de Tecnología',
                'status' => true,
            ],
            [
                'name' => 'Servicios de Consultoría',
                'status' => true,
            ],
            [
                'name' => 'Servicios de Capacitación',
                'status' => true,
            ],
            [
                'name' => 'Servicios de Contabilidad',
                'status' => true,
            ],
        ];

        foreach ($businessTypes as $type) {
            BusinessType::create($type);
        }

        $this->command->info('Tipos de empresas creados exitosamente.');
    }
}
