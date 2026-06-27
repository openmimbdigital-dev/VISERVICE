<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CountySeeder::class,
            CitySeeder::class,
            BussinnesTypeSeeder::class,
            BussinnesSeeder::class,
            UsersSeeder::class,
            SubscriptionPlansSeeder::class,
            BanksSeeder::class,
            BankAccountsSeeder::class,
            EquipmentCatalogSeeder::class,
            ClientsSeeder::class,
        ]);
    }
}
