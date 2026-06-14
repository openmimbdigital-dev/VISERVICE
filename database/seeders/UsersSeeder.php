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
    /**
     * Seed users for local login testing.
     */
    public function run(): void
    {
        $country = Country::query()->where('code', 'COL')->first();
        $city_bogota = $country
            ? City::query()->where('code', 'BOG')->where('country_id', $country->id)->first()
            : null;
        $city_medellin = $country
            ? City::query()->where('code', 'MED')->where('country_id', $country->id)->first()
            : null;
        $business = Business::query()->where('slug', 'transportes-transad')->first();

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'first_name' => 'Admin',
                'last_name' => 'VISERVICE',
                'email' => 'admin@viservice.local',
                'password' => Hash::make('Admin12345*'),
                'phone_number' => '+57 300 100 0001',
                'address' => 'Carrera 7 # 71-21, Bogotá',
                'profile_photo' => null,
                'status' => true,
                'document_type' => 'CC',
                'document_number' => 10_245_678,
                'country_id' => $country?->id,
                'city_id' => $city_bogota?->id,
                'business_id' => $business?->id,
            ]
        );

        User::updateOrCreate(
            ['username' => 'operador'],
            [
                'first_name' => 'Operador',
                'last_name' => 'VISERVICE',
                'email' => 'operador@viservice.local',
                'password' => Hash::make('Operador123*'),
                'phone_number' => '+57 300 200 0002',
                'address' => 'Calle 50 # 40-12, Medellín',
                'profile_photo' => null,
                'status' => true,
                'document_type' => 'CC',
                'document_number' => 52_987_654,
                'country_id' => $country?->id,
                'city_id' => $city_medellin?->id,
                'business_id' => $business?->id,
            ]
        );
    }
}
