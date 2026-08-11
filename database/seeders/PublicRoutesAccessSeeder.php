<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Support\Public\PublicRouteAccess;
use Illuminate\Database\Seeder;

class PublicRoutesAccessSeeder extends Seeder
{
    public function run(): void
    {
        $iglesia = OrganizationType::query()->where('label', 'iglesia')->first();

        if (! $iglesia) {
            $this->command?->warn('PublicRoutesAccessSeeder: no existe el tipo iglesia.');

            return;
        }

        PublicRouteAccess::syncOrganizationTypeItems((int) $iglesia->id, [
            'public.participants.events',
        ]);

        $this->command?->info('Ítem Eventos del portal Participantes habilitado para: iglesia.');
    }
}
