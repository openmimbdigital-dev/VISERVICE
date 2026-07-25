<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\OrganizationType;
use App\Models\City;
use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'Password123*';

    public function run(): void
    {
        $password = Hash::make(self::DEFAULT_PASSWORD);

        $country  = Country::where('code', 'COL')->first();
        $bogota   = $country ? City::where('code', 'BOG')->where('country_id', $country->id)->first() : null;
        $medellin = $country ? City::where('code', 'MED')->where('country_id', $country->id)->first() : null;
        $cali     = $country ? City::where('code', 'CAL')->where('country_id', $country->id)->first() : null;

        $b_transad     = Business::where('slug', 'transportes-transad')->first();
        $b_cargarapida = Business::where('slug', 'carga-rapida-sas')->first();
        $b_valle       = Business::where('slug', 'transportes-del-valle')->first();

        $superAdmin = User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'first_name'      => 'Super',
                'last_name'       => 'Admin',
                'email'           => 'superadmin@viservice.local',
                'password'        => $password,
                'phone_number'    => '+57 300 000 0001',
                'address'         => 'Oficina Central VISERVICE',
                'status'          => true,
                'document_type'   => 'CC',
                'document_number' => 1000000001,
                'country_id'      => $country?->id,
                'city_id'         => $bogota?->id,
            ]
        );
        $superAdmin->businesses()->detach();
        $superAdmin->syncRoles(['superAdmin']);

        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'first_name'      => 'Juan Carlos',
                'last_name'       => 'Pérez',
                'email'           => 'admin@viservice.local',
                'password'        => $password,
                'phone_number'    => '+57 300 100 0001',
                'address'         => 'Carrera 7 # 71-21, Bogotá',
                'status'          => true,
                'document_type'   => 'CC',
                'document_number' => 10245678,
                'country_id'      => $country?->id,
                'city_id'         => $bogota?->id,
            ]
        );
        if ($b_transad) {
            $admin->businesses()->sync([$b_transad->id => ['is_primary' => true]]);
        }
        $admin->syncRoles(['Administrador']);

        $supervisor = User::updateOrCreate(
            ['username' => 'supervisor'],
            [
                'first_name'      => 'María Elena',
                'last_name'       => 'Rodríguez',
                'email'           => 'supervisor@viservice.local',
                'password'        => $password,
                'phone_number'    => '+57 300 150 0001',
                'address'         => 'Calle 50 # 20-10, Bogotá',
                'status'          => true,
                'document_type'   => 'CC',
                'document_number' => 20345678,
                'country_id'      => $country?->id,
                'city_id'         => $bogota?->id,
            ]
        );
        if ($b_transad) {
            $supervisor->businesses()->sync([$b_transad->id => ['is_primary' => true]]);
        }
        $supervisor->syncRoles(['Supervisor']);

        $operador = User::updateOrCreate(
            ['username' => 'operador'],
            [
                'first_name'      => 'Carlos',
                'last_name'       => 'López',
                'email'           => 'operador@viservice.local',
                'password'        => $password,
                'phone_number'    => '+57 300 200 0002',
                'address'         => 'Calle 50 # 40-12, Medellín',
                'status'          => true,
                'document_type'   => 'CC',
                'document_number' => 52987654,
                'country_id'      => $country?->id,
                'city_id'         => $medellin?->id,
            ]
        );
        if ($b_cargarapida) {
            $operador->businesses()->sync([$b_cargarapida->id => ['is_primary' => true]]);
        }
        $operador->syncRoles(['Operador']);

        $adminValle = User::updateOrCreate(
            ['username' => 'admin.valle'],
            [
                'first_name'      => 'Ana Patricia',
                'last_name'       => 'González',
                'email'           => 'admin.valle@viservice.local',
                'password'        => $password,
                'phone_number'    => '+57 300 300 0003',
                'address'         => 'Avenida 6N # 28-75, Cali',
                'status'          => true,
                'document_type'   => 'CC',
                'document_number' => 34567890,
                'country_id'      => $country?->id,
                'city_id'         => $cali?->id,
            ]
        );
        if ($b_valle) {
            $adminValle->businesses()->sync([$b_valle->id => ['is_primary' => true]]);
        }
        $adminValle->syncRoles(['Administrador']);

        $church_rows = $this->seedChurchUsers($country, $bogota, $medellin, $cali, $password);

        $rows = [
            ['superadmin',  'superAdmin',    self::DEFAULT_PASSWORD],
            ['admin',       'Administrador', self::DEFAULT_PASSWORD],
            ['supervisor',  'Supervisor',    self::DEFAULT_PASSWORD],
            ['operador',    'Operador',      self::DEFAULT_PASSWORD],
            ['admin.valle', 'Administrador', self::DEFAULT_PASSWORD],
            ...$church_rows,
        ];

        $this->command->table(
            ['Usuario', 'Rol', 'Password'],
            $rows
        );

        $this->command->info('Clave global para todos los usuarios: ' . self::DEFAULT_PASSWORD);
    }

    /**
     * @return list<array{0: string, 1: string, 2: string}>
     */
    private function seedChurchUsers(?Country $country, ?City $bogota, ?City $medellin, ?City $cali, string $password): array
    {
        $iglesia_type = OrganizationType::query()->where('label', 'iglesia')->first();

        if (! $iglesia_type) {
            return [];
        }

        $church_users = [
            [
                'username'        => 'pastor.feesperanza',
                'first_name'      => 'Samuel',
                'last_name'       => 'Mendoza',
                'email'           => 'pastor.feesperanza@viservice.local',
                'phone_number'    => '+57 300 400 0001',
                'address'         => 'Calle 10 # 5-20, Sampués',
                'document_number' => 40100001,
                'city_id'         => $bogota?->id,
                'role'            => 'Pastor',
                'business_slug'   => 'centro-de-fe-y-esperanza-sampues',
            ],
            [
                'username'        => 'secretario.feesperanza1',
                'first_name'      => 'Rosa',
                'last_name'       => 'Cárdenas',
                'email'           => 'secretario.feesperanza1@viservice.local',
                'phone_number'    => '+57 300 410 0001',
                'address'         => 'Calle 12 # 8-14, Sampués',
                'document_number' => 40200001,
                'city_id'         => $bogota?->id,
                'role'            => 'Secretario',
                'business_slug'   => 'centro-de-fe-y-esperanza-sampues',
            ],
            [
                'username'        => 'secretario.feesperanza2',
                'first_name'      => 'Héctor',
                'last_name'       => 'Palacio',
                'email'           => 'secretario.feesperanza2@viservice.local',
                'phone_number'    => '+57 300 410 0002',
                'address'         => 'Carrera 4 # 15-30, Sampués',
                'document_number' => 40200002,
                'city_id'         => $bogota?->id,
                'role'            => 'Secretario',
                'business_slug'   => 'centro-de-fe-y-esperanza-sampues',
            ],
            [
                'username'        => 'lider.feesperanza1',
                'first_name'      => 'Yolanda',
                'last_name'       => 'Mejía',
                'email'           => 'lider.feesperanza1@viservice.local',
                'phone_number'    => '+57 300 410 0003',
                'address'         => 'Barrio Centro, Sampués',
                'document_number' => 40200003,
                'city_id'         => $bogota?->id,
                'role'            => 'Lider de congregacion',
                'business_slug'   => 'centro-de-fe-y-esperanza-sampues',
            ],
            [
                'username'        => 'miembro.feesperanza1',
                'first_name'      => 'Camila',
                'last_name'       => 'Torres',
                'email'           => 'miembro.feesperanza1@viservice.local',
                'phone_number'    => '+57 300 420 0001',
                'address'         => 'Calle 3 # 2-10, Sampués',
                'document_number' => 40300001,
                'city_id'         => $bogota?->id,
                'role'            => 'Miembro',
                'business_slug'   => 'centro-de-fe-y-esperanza-sampues',
            ],
            [
                'username'        => 'miembro.feesperanza2',
                'first_name'      => 'Diego',
                'last_name'       => 'Salazar',
                'email'           => 'miembro.feesperanza2@viservice.local',
                'phone_number'    => '+57 300 420 0002',
                'address'         => 'Carrera 7 # 9-22, Sampués',
                'document_number' => 40300002,
                'city_id'         => $bogota?->id,
                'role'            => 'Miembro',
                'business_slug'   => 'centro-de-fe-y-esperanza-sampues',
            ],
            [
                'username'        => 'miembro.feesperanza3',
                'first_name'      => 'Valentina',
                'last_name'       => 'Rojas',
                'email'           => 'miembro.feesperanza3@viservice.local',
                'phone_number'    => '+57 300 420 0003',
                'address'         => 'Calle 18 # 4-05, Sampués',
                'document_number' => 40300003,
                'city_id'         => $bogota?->id,
                'role'            => 'Miembro',
                'business_slug'   => 'centro-de-fe-y-esperanza-sampues',
            ],
            [
                'username'        => 'miembro.feesperanza4',
                'first_name'      => 'Esteban',
                'last_name'       => 'Quiroz',
                'email'           => 'miembro.feesperanza4@viservice.local',
                'phone_number'    => '+57 300 420 0004',
                'address'         => 'Vereda El Carmen, Sampués',
                'document_number' => 40300004,
                'city_id'         => $bogota?->id,
                'role'            => 'Miembro',
                'business_slug'   => 'centro-de-fe-y-esperanza-sampues',
            ],
            [
                'username'        => 'miembro.feesperanza5',
                'first_name'      => 'Natalia',
                'last_name'       => 'Benítez',
                'email'           => 'miembro.feesperanza5@viservice.local',
                'phone_number'    => '+57 300 420 0005',
                'address'         => 'Barrio La Esperanza, Sampués',
                'document_number' => 40300005,
                'city_id'         => $bogota?->id,
                'role'            => 'Miembro',
                'business_slug'   => 'centro-de-fe-y-esperanza-sampues',
            ],
            [
                'username'        => 'liderdiacono.feesperanza',
                'first_name'      => 'Miguel',
                'last_name'       => 'Arrieta',
                'email'           => 'liderdiacono.feesperanza@viservice.local',
                'phone_number'    => '+57 300 430 0001',
                'address'         => 'Calle 8 # 6-18, Sampués',
                'document_number' => 40400001,
                'city_id'         => $bogota?->id,
                'role'            => 'Lider diacono',
                'business_slug'   => 'centro-de-fe-y-esperanza-sampues',
            ],
            [
                'username'        => 'diacono.feesperanza',
                'first_name'      => 'Pedro',
                'last_name'       => 'Galván',
                'email'           => 'diacono.feesperanza@viservice.local',
                'phone_number'    => '+57 300 430 0002',
                'address'         => 'Carrera 5 # 11-09, Sampués',
                'document_number' => 40400002,
                'city_id'         => $bogota?->id,
                'role'            => 'Diacono',
                'business_slug'   => 'centro-de-fe-y-esperanza-sampues',
            ],
            [
                'username'        => 'maestro.feesperanza',
                'first_name'      => 'Lucía',
                'last_name'       => 'Pineda',
                'email'           => 'maestro.feesperanza@viservice.local',
                'phone_number'    => '+57 300 430 0003',
                'address'         => 'Calle 14 # 3-27, Sampués',
                'document_number' => 40400003,
                'city_id'         => $bogota?->id,
                'role'            => 'Maestro',
                'business_slug'   => 'centro-de-fe-y-esperanza-sampues',
            ],
            [
                'username'        => 'coordinador.feesperanza',
                'first_name'      => 'Andrea',
                'last_name'       => 'Cortés',
                'email'           => 'coordinador.feesperanza@viservice.local',
                'phone_number'    => '+57 300 430 0004',
                'address'         => 'Barrio San José, Sampués',
                'document_number' => 40400004,
                'city_id'         => $bogota?->id,
                'role'            => 'Coordinador educativo',
                'business_slug'   => 'centro-de-fe-y-esperanza-sampues',
            ],
            [
                'username'        => 'secretario.restauracion',
                'first_name'      => 'Laura',
                'last_name'       => 'Vargas',
                'email'           => 'secretario.restauracion@viservice.local',
                'phone_number'    => '+57 300 400 0002',
                'address'         => 'Carrera 15 # 85-40, Bogotá',
                'document_number' => 40100002,
                'city_id'         => $bogota?->id,
                'role'            => 'Secretario',
                'business_slug'   => 'iglesia-restauracion-bogota',
            ],
            [
                'username'        => 'lider.redentor',
                'first_name'      => 'Andrés',
                'last_name'       => 'Castillo',
                'email'           => 'lider.redentor@viservice.local',
                'phone_number'    => '+57 300 400 0003',
                'address'         => 'Calle 44 # 75-12, Medellín',
                'document_number' => 40100003,
                'city_id'         => $medellin?->id,
                'role'            => 'Lider de congregacion',
                'business_slug'   => 'comunidad-cristiana-el-redentor',
            ],
            [
                'username'        => 'comercio.alabanza',
                'first_name'      => 'Diana',
                'last_name'       => 'Ríos',
                'email'           => 'comercio.alabanza@viservice.local',
                'phone_number'    => '+57 300 400 0004',
                'address'         => 'Avenida 5 # 23N-45, Cali',
                'document_number' => 40100004,
                'city_id'         => $cali?->id,
                'role'            => 'Comercio',
                'business_slug'   => 'templo-de-alabanza-cali',
            ],
            [
                'username'        => 'pastor.feyvida',
                'first_name'      => 'Jorge',
                'last_name'       => 'Peña',
                'email'           => 'pastor.feyvida@viservice.local',
                'phone_number'    => '+57 300 400 0005',
                'address'         => 'Barrio El Progreso, Sampués',
                'document_number' => 40100005,
                'city_id'         => $bogota?->id,
                'role'            => 'Pastor',
                'business_slug'   => 'congregacion-fe-y-vida-sampues',
            ],
            [
                'username'        => 'secretario.semilla',
                'first_name'      => 'Claudia',
                'last_name'       => 'Herrera',
                'email'           => 'secretario.semilla@viservice.local',
                'phone_number'    => '+57 300 400 0006',
                'address'         => 'Calle 127 # 15-30, Suba, Bogotá',
                'document_number' => 40100006,
                'city_id'         => $bogota?->id,
                'role'            => 'Secretario',
                'business_slug'   => 'iglesia-hija-la-buena-semilla',
            ],
            [
                'username'        => 'lider.betel',
                'first_name'      => 'Felipe',
                'last_name'       => 'Ortiz',
                'email'           => 'lider.betel@viservice.local',
                'phone_number'    => '+57 300 400 0007',
                'address'         => 'Carrera 65 # 45-10, Laureles, Medellín',
                'document_number' => 40100007,
                'city_id'         => $medellin?->id,
                'role'            => 'Lider de congregacion',
                'business_slug'   => 'congregacion-hija-betel-medellin',
            ],
            [
                'username'        => 'comercio.sincelejo',
                'first_name'      => 'Martha',
                'last_name'       => 'Suárez',
                'email'           => 'comercio.sincelejo@viservice.local',
                'phone_number'    => '+57 300 400 0008',
                'address'         => 'Vereda La Esperanza, Sincelejo',
                'document_number' => 40100008,
                'city_id'         => $bogota?->id,
                'role'            => 'Comercio',
                'business_slug'   => 'campo-blanco-sincelejo',
            ],
            [
                'username'        => 'pastor.facatativa',
                'first_name'      => 'Ricardo',
                'last_name'       => 'Navarro',
                'email'           => 'pastor.facatativa@viservice.local',
                'phone_number'    => '+57 300 400 0009',
                'address'         => 'Km 3 vía Facatativá - El Rosal',
                'document_number' => 40100009,
                'city_id'         => $bogota?->id,
                'role'            => 'Pastor',
                'business_slug'   => 'campo-blanco-facatativa',
            ],
            [
                'username'        => 'secretario.palmira',
                'first_name'      => 'Paola',
                'last_name'       => 'Jiménez',
                'email'           => 'secretario.palmira@viservice.local',
                'phone_number'    => '+57 300 400 0010',
                'address'         => 'Sector Rozo, Palmira',
                'document_number' => 40100010,
                'city_id'         => $cali?->id,
                'role'            => 'Secretario',
                'business_slug'   => 'campo-blanco-palmira',
            ],
        ];

        $rows = [];

        foreach ($church_users as $data) {
            $business = Business::query()
                ->where('slug', $data['business_slug'])
                ->where('organization_type_id', $iglesia_type->id)
                ->first();

            if (! $business) {
                continue;
            }

            $role = $data['role'];
            unset($data['role'], $data['business_slug']);

            $user = User::updateOrCreate(
                ['username' => $data['username']],
                [
                    ...$data,
                    'password'      => $password,
                    'status'        => true,
                    'document_type' => 'CC',
                    'country_id'    => $country?->id,
                ]
            );

            $user->businesses()->sync([$business->id => ['is_primary' => true]]);
            $user->syncRoles([$role]);

            $rows[] = [$data['username'], $role, self::DEFAULT_PASSWORD];
        }

        return $rows;
    }
}
