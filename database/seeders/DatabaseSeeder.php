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
            MenuSeeder::class,
            CountySeeder::class,
            CitySeeder::class,
            BussinnesTypeSeeder::class,
            OrganizationTypeSeeder::class,
            BusinessCategorySeeder::class,
            BussinnesSeeder::class,
            BusinessMenuModuleSeeder::class,
            BusinessAccessSeeder::class,
            UsersSeeder::class,
            TeamPositionSeeder::class,
            SubscriptionPlansSeeder::class,
            BanksSeeder::class,
            BankAccountsSeeder::class,
            EquipmentCatalogSeeder::class,
            ItemCatalogSeeder::class,
            ItemsSeeder::class,
            BusinessPaymentSettingsSeeder::class,
            QuotationServiceTypesSeeder::class,
            AttributesSeeder::class,
            ClientsSeeder::class,
            EquipmentSeeder::class,
            QuotationsSeeder::class,
        ]);
    }
}
