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
            ChurchMinistryRolesSeeder::class,
            MenuSeeder::class,
            CountySeeder::class,
            CitySeeder::class,
            OrganizationTypeSeeder::class,
            BusinessTypeSeeder::class,
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
            ProductCatalogSeeder::class,
            ProductsSeeder::class,
            BusinessPaymentSettingsSeeder::class,
            QuotationServiceTypesSeeder::class,
            StatusesSeeder::class,
            AssociatedDocumentOtSeeder::class,
            AttributesSeeder::class,
            ClientsSeeder::class,
            EquipmentSeeder::class,
            QuotationsSeeder::class,
            WorkOrdersSeeder::class,
            RemissionsSeeder::class,
            EventCategoriesAndAttendeeTypesSeeder::class,
            ParticipantsSeeder::class,
            EventTeamsSeeder::class,
            EventsSeeder::class,
        ]);
    }
}
