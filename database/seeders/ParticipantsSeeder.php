<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Models\Business;
use App\Models\City;
use App\Models\Country;
use App\Models\Participant;
use App\Models\ParticipantRole;
use Illuminate\Database\Seeder;

class ParticipantsSeeder extends Seeder
{
    private const BUSINESS_SLUG = 'centro-de-fe-y-esperanza-sampues';

    public function run(): void
    {
        $business = Business::query()->where('slug', self::BUSINESS_SLUG)->first();

        if (! $business) {
            $this->command?->warn('Participantes: no se encontró el Centro de Fe y Esperanza Sampues.');

            return;
        }

        $roles = $this->seedRoles($business);
        $this->seedParticipants($business, $roles);

        $this->command?->info('Roles y participantes demo creados correctamente.');
    }

    /**
     * @return array<string, ParticipantRole>
     */
    private function seedRoles(Business $business): array
    {
        $role_data = [
            'Pastor' => 'Liderazgo espiritual, predicación y cuidado pastoral de la congregación.',
            'Anciano' => 'Gobierno espiritual, consejo y supervisión de la vida de la iglesia.',
            'Diácono' => 'Servicio práctico, apoyo a necesidades de la congregación y logística.',
            'Líder' => 'Coordinación de grupos, células o ministerios asignados.',
            'Maestro' => 'Enseñanza bíblica en escuelas dominicales, grupos o discipulado.',
            'Miembro' => 'Participante activo de la congregación con compromiso regular.',
            'Adolescente' => 'Joven en etapa adolescente vinculado a ministerios juveniles.',
            'Niño' => 'Niño participante de actividades infantiles de la iglesia.',
            'Invitado' => 'Visitante o asistente ocasional sin membresía formal.',
            'Voluntario' => 'Colaborador eventual en actividades y eventos de la iglesia.',
        ];

        $roles = [];

        foreach ($role_data as $name => $description) {
            $roles[$name] = ParticipantRole::withTrashed()->updateOrCreate(
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

        return $roles;
    }

    /**
     * @param  array<string, ParticipantRole>  $roles
     */
    private function seedParticipants(Business $business, array $roles): void
    {
        $city = City::query()->where('code', 'BAQ')->first()
            ?? City::query()->where('code', 'BOG')->first();
        $country = Country::query()->where('name', 'Colombia')->first();

        $role_names = array_keys($roles);

        $people = [
            ['first_name' => 'Carlos', 'last_name' => 'Martínez', 'role' => 'Pastor'],
            ['first_name' => 'Ana', 'last_name' => 'Rodríguez', 'role' => 'Anciano'],
            ['first_name' => 'Luis', 'last_name' => 'Pérez', 'role' => 'Diácono'],
            ['first_name' => 'María', 'last_name' => 'Gómez', 'role' => 'Líder'],
            ['first_name' => 'José', 'last_name' => 'Hernández', 'role' => 'Maestro'],
            ['first_name' => 'Laura', 'last_name' => 'Sánchez', 'role' => 'Maestro'],
            ['first_name' => 'Andrés', 'last_name' => 'Ramírez', 'role' => 'Líder'],
            ['first_name' => 'Paula', 'last_name' => 'Torres', 'role' => 'Miembro'],
            ['first_name' => 'Diego', 'last_name' => 'Vargas', 'role' => 'Miembro'],
            ['first_name' => 'Camila', 'last_name' => 'Castro', 'role' => 'Miembro'],
            ['first_name' => 'Sebastián', 'last_name' => 'Morales', 'role' => 'Adolescente'],
            ['first_name' => 'Valentina', 'last_name' => 'Rojas', 'role' => 'Adolescente'],
            ['first_name' => 'Mateo', 'last_name' => 'Jiménez', 'role' => 'Niño'],
            ['first_name' => 'Sofía', 'last_name' => 'Ortiz', 'role' => 'Niño'],
            ['first_name' => 'Felipe', 'last_name' => 'Navarro', 'role' => 'Invitado'],
            ['first_name' => 'Daniela', 'last_name' => 'Mendoza', 'role' => 'Invitado'],
            ['first_name' => 'Ricardo', 'last_name' => 'Silva', 'role' => 'Voluntario'],
            ['first_name' => 'Carolina', 'last_name' => 'Ruiz', 'role' => 'Voluntario'],
            ['first_name' => 'Miguel', 'last_name' => 'Aguilar', 'role' => 'Miembro'],
            ['first_name' => 'Isabel', 'last_name' => 'Delgado', 'role' => 'Diácono'],
        ];

        foreach ($people as $index => $person) {
            $role_name = $person['role'];
            $role = $roles[$role_name] ?? $roles[$role_names[$index % count($role_names)]];
            $document_number = 1000000000 + ($business->id * 1000) + ($index + 1);

            Participant::withTrashed()->updateOrCreate(
                [
                    'business_id' => $business->id,
                    'document_type' => DocumentType::CitizenshipCard,
                    'document_number' => $document_number,
                ],
                [
                    'participant_role_id' => $role->id,
                    'first_name' => $person['first_name'],
                    'last_name' => $person['last_name'],
                    'email' => strtolower($this->slugify($person['first_name'].'.'.$person['last_name'])).'@demo-iglesia.test',
                    'phone_number' => '300'.str_pad((string) (1000000 + $index), 7, '0', STR_PAD_LEFT),
                    'address' => 'Calle '.($index + 10).' # '.($index + 20).'-'.($index + 5).', Sampues',
                    'profile_photo' => null,
                    'status' => true,
                    'city_id' => $city?->id,
                    'country_id' => $country?->id,
                    'team_position_id' => null,
                    'name_team_position' => null,
                    'deleted_at' => null,
                ]
            );
        }
    }

    private function slugify(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtolower($value);

        return (string) preg_replace('/[^a-z0-9.]+/', '', $value);
    }
}
