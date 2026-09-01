<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use App\Models\OrganizationType;
use Illuminate\Database\Seeder;

class BusinessTypeSeeder extends Seeder
{
    public function run(): void
    {
        $taller = OrganizationType::where('label', 'taller')->first();
        $iglesia = OrganizationType::where('label', 'iglesia')->first();
        $centro  = OrganizationType::where('label', 'centro_educativo')->first();

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

        $this->command->info('Tipos de negocio sincronizados exitosamente.');
    }

    /** @param list<string> $names */
    private function seedForType(OrganizationType $organization_type, array $names): void
    {
        foreach ($names as $name) {
            $label = BusinessType::normalizeLabel($name);

            BusinessType::query()->updateOrCreate(
                [
                    'organization_type_id' => $organization_type->id,
                    'label'                => $label,
                ],
                [
                    'name'   => $name,
                    'active' => true,
                ]
            );
        }
    }
}
