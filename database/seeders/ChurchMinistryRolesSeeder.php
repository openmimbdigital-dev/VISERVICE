<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class ChurchMinistryRolesSeeder extends Seeder
{
    /** @return list<string> */
    private function roleNames(): array
    {
        return [
            'Lider diacono',
            'Diacono',
            'Maestro',
            'Coordinador educativo',
        ];
    }

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        $schedule_view = Permission::firstOrCreate([
            'name' => 'events.schedule.view',
            'guard_name' => $guard,
        ]);

        foreach ($this->roleNames() as $role_name) {
            $role = Role::firstOrCreate([
                'name' => $role_name,
                'guard_name' => $guard,
            ]);

            $role->syncPermissions([$schedule_view]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command?->info('Roles ministeriales de iglesia creados con permiso events.schedule.view.');
    }
}
