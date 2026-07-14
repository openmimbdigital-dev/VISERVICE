<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\TeamPosition;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamPositionSeeder extends Seeder
{
    /** @var list<string> */
    private const WORKSHOP_POSITIONS = [
        'Gerente de Taller',
        'Jefe de Taller',
        'Asesor de Servicio',
        'Supervisor de Taller',
        'Mecánico General',
        'Mecánico Especialista en Motor',
        'Electricista Automotriz',
        'Técnico en Diagnóstico Electrónico',
        'Especialista en Frenos y Suspensión',
        'Latonero y Pintor',
        'Jefe de Repuestos',
        'Auxiliar de Taller',
    ];

    /** @var array<string, string> Rol Spatie → label del cargo */
    private const ROLE_TO_POSITION_LABEL = [
        'Administrador' => 'gerente_de_taller',
        'Supervisor'    => 'jefe_de_taller',
        'Operador'      => 'mecanico_general',
        'Comercio'      => 'asesor_de_servicio',
    ];

    public function run(): void
    {
        $taller = OrganizationType::query()->where('label', 'taller')->first();

        if (! $taller) {
            $this->command->warn('Tipo de organización "taller" no encontrado. Omitiendo TeamPositionSeeder.');

            return;
        }

        $positions_by_label = $this->seedWorkshopPositions($taller);
        $assigned           = $this->assignPositionsToWorkshopUsers($taller, $positions_by_label);

        $this->command->info(sprintf(
            'Cargos de taller sincronizados: %d registros, %d usuarios asignados.',
            count($positions_by_label),
            $assigned
        ));
    }

    /** @return array<string, TeamPosition> */
    private function seedWorkshopPositions(OrganizationType $taller): array
    {
        $positions_by_label = [];

        foreach (self::WORKSHOP_POSITIONS as $name) {
            $label = TeamPosition::normalizeLabel($name);

            $position = TeamPosition::query()->updateOrCreate(
                [
                    'organization_type_id' => $taller->id,
                    'label'                => $label,
                    'general'              => true,
                ],
                [
                    'business_id' => null,
                    'name'        => $name,
                    'active'      => true,
                ]
            );

            $positions_by_label[$label] = $position;
        }

        return $positions_by_label;
    }

    /** @param array<string, TeamPosition> $positions_by_label */
    private function assignPositionsToWorkshopUsers(OrganizationType $taller, array $positions_by_label): int
    {
        $fallback_label = TeamPosition::normalizeLabel('Auxiliar de Taller');
        $fallback       = $positions_by_label[$fallback_label] ?? reset($positions_by_label);

        $users = User::query()
            ->whereHas('businesses', fn ($q) => $q->where('organization_type_id', $taller->id))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'superAdmin'))
            ->with('roles')
            ->get();

        $assigned = 0;

        foreach ($users as $user) {
            $role_name     = $user->roles->first()?->name;
            $position_label = self::ROLE_TO_POSITION_LABEL[$role_name] ?? null;
            $position      = $position_label !== null
                ? ($positions_by_label[$position_label] ?? $fallback)
                : $fallback;

            if (! $position instanceof TeamPosition) {
                continue;
            }

            $user->update([
                'team_position_id'   => $position->id,
                'name_team_position' => $position->name,
            ]);

            $assigned++;
        }

        return $assigned;
    }
}
