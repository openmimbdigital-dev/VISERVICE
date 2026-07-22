<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\EventTeam;
use App\Models\EventTeamMember;
use App\Models\EventTeamRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventTeamsSeeder extends Seeder
{
    private const BUSINESS_SLUG = 'centro-de-fe-y-esperanza-sampues';

    public function run(): void
    {
        $business = Business::query()->where('slug', self::BUSINESS_SLUG)->first();

        if (! $business) {
            $this->command?->warn('Equipos de eventos: no se encontró el Centro de Fe y Esperanza Sampues.');

            return;
        }

        $roles = $this->seedRoles($business);
        $teams = $this->seedTeams($business);

        $this->attachRoles($teams, $roles);
        $this->assignDemoMember($business, $teams, $roles);

        $this->command?->info('Equipos de eventos demo creados correctamente.');
    }

    /**
     * @return array<string, EventTeamRole>
     */
    private function seedRoles(Business $business): array
    {
        $role_data = [
            'Coordinador' => 'Planificar el evento, distribuir responsabilidades y supervisar al equipo.',
            'Alabanza' => 'Dirigir la música, el canto y los momentos de adoración.',
            'Ujieres' => 'Recibir, orientar y ubicar a los asistentes durante el evento.',
            'Multimedia' => 'Operar el sonido, la iluminación, la proyección y las transmisiones.',
            'Intercesión' => 'Coordinar la oración antes, durante y después del evento.',
            'Logística' => 'Preparar espacios, materiales, transporte y recursos necesarios.',
            'Protocolo' => 'Atender invitados y coordinar el orden ceremonial del evento.',
        ];

        $roles = [];

        foreach ($role_data as $name => $functions) {
            $roles[$name] = EventTeamRole::withTrashed()->updateOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $name,
                ],
                [
                    'functions' => $functions,
                    'active' => true,
                    'deleted_at' => null,
                ]
            );
        }

        return $roles;
    }

    /**
     * @return array<string, EventTeam>
     */
    private function seedTeams(Business $business): array
    {
        $team_data = [
            'Equipo de culto dominical' => 'Responsable de la preparación y ejecución del culto dominical.',
            'Equipo de eventos especiales' => 'Responsable de conferencias, vigilias y demás eventos eventuales.',
        ];

        $teams = [];

        foreach ($team_data as $name => $description) {
            $teams[$name] = EventTeam::withTrashed()->updateOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $name,
                ],
                [
                    'description' => $description,
                    'active' => true,
                    'deleted_at' => null,
                ]
            );
        }

        return $teams;
    }

    /**
     * @param  array<string, EventTeam>  $teams
     * @param  array<string, EventTeamRole>  $roles
     */
    private function attachRoles(array $teams, array $roles): void
    {
        $teams['Equipo de culto dominical']->roles()->syncWithoutDetaching([
            $roles['Coordinador']->id,
            $roles['Alabanza']->id,
            $roles['Ujieres']->id,
            $roles['Multimedia']->id,
            $roles['Intercesión']->id,
        ]);

        $teams['Equipo de eventos especiales']->roles()->syncWithoutDetaching([
            $roles['Coordinador']->id,
            $roles['Logística']->id,
            $roles['Protocolo']->id,
            $roles['Multimedia']->id,
            $roles['Intercesión']->id,
        ]);
    }

    /**
     * @param  array<string, EventTeam>  $teams
     * @param  array<string, EventTeamRole>  $roles
     */
    private function assignDemoMember(Business $business, array $teams, array $roles): void
    {
        $user = User::query()
            ->where('username', 'pastor.feesperanza')
            ->whereHas('businesses', fn ($query) => $query->whereKey($business->id))
            ->first();

        if (! $user) {
            $this->command?->warn('Equipos de eventos: no se encontró el usuario pastor.feesperanza.');

            return;
        }

        foreach ($teams as $team) {
            EventTeamMember::withTrashed()->updateOrCreate(
                [
                    'event_team_id' => $team->id,
                    'event_team_role_id' => $roles['Coordinador']->id,
                    'user_id' => $user->id,
                ],
                [
                    'business_id' => $business->id,
                    'deleted_at' => null,
                ]
            );
        }
    }
}
