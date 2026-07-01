<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use App\Models\OrganizationType;
use Illuminate\Database\Seeder;

class OrganizationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $taller = BusinessType::where('label', 'taller')->first();
        $iglesia = BusinessType::where('label', 'iglesia')->first();
        $centro  = BusinessType::where('label', 'centro_educativo')->first();

        if ($taller) {
            $this->seedForType($taller, [
                'Transporte de Carga',
                'Transporte de Pasajeros',
                'Transporte de Mercancías',
                'Logística y Distribución',
                'Servicios de Mensajería',
                'Transporte Especializado',
                'Almacenamiento y Depósito',
                'Servicios Portuarios',
                'Servicios Aeroportuarios',
                'Mantenimiento de Vehículos',
                'Venta de Repuestos',
                'Servicios de Seguridad',
                'Servicios de Limpieza',
                'Servicios de Alimentación',
                'Servicios de Combustible',
                'Servicios de Seguros',
                'Servicios de Tecnología',
                'Servicios de Consultoría',
                'Servicios de Capacitación',
                'Servicios de Contabilidad',
            ]);
        }

        if ($iglesia) {
            $this->seedForType($iglesia, [
                'Iglesia',
                'Comunidad Cristiana',
                'Ministerio',
                'Congregación',
            ]);
        }

        if ($centro) {
            $this->seedForType($centro, [
                'Colegio',
                'Instituto Técnico',
                'Universidad',
                'Servicio Educativo',
                'Jardín Infantil',
            ]);
        }

        $this->command->info('Tipos de organización sincronizados exitosamente.');
    }

    /** @param list<string> $names */
    private function seedForType(BusinessType $business_type, array $names): void
    {
        foreach ($names as $name) {
            $label = OrganizationType::normalizeLabel($name);

            OrganizationType::query()->updateOrCreate(
                [
                    'business_type_id' => $business_type->id,
                    'label'            => $label,
                ],
                [
                    'name'   => $name,
                    'active' => true,
                ]
            );
        }
    }
}
