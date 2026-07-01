<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\City;
use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
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
                'password'        => Hash::make('SuperAdmin12345*'),
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
                'password'        => Hash::make('Admin12345*'),
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
                'password'        => Hash::make('Supervisor123*'),
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
                'password'        => Hash::make('Operador123*'),
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
                'password'        => Hash::make('AdminValle123*'),
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

        $this->command->table(
            ['Usuario', 'Rol', 'Password'],
            [
                ['superadmin',  'superAdmin',    'SuperAdmin12345*'],
                ['admin',       'Administrador', 'Admin12345*'],
                ['supervisor',  'Supervisor',    'Supervisor123*'],
                ['operador',    'Operador',      'Operador123*'],
                ['admin.valle', 'Administrador', 'AdminValle123*'],
            ]
        );
    }
}
