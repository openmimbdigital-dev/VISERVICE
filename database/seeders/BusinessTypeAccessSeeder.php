<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use App\Models\Role;
use App\Support\BusinessTypeAccess;
use Illuminate\Database\Seeder;

class BusinessTypeAccessSeeder extends Seeder
{
    /** @var list<string> */
    private array $catalog_permissions = [
        'business_types.view', 'business_types.create', 'business_types.edit', 'business_types.delete',
        'organization_types.view', 'organization_types.create', 'organization_types.edit', 'organization_types.delete',
        'business_types.access.view', 'business_types.access.manage',
    ];

    /** @var list<string> */
    private array $workshop_base_permissions = [
        'users.view', 'users.create', 'users.edit', 'users.delete',
        'users.activate', 'users.deactivate',
        'businesses.view', 'businesses.edit',
        'reports.view', 'reports.export',
        'settings.view', 'settings.edit',
        'settings.attributes.view', 'settings.attributes.create',
        'settings.attributes.edit', 'settings.attributes.delete',
        'roles.view',
        'workshop.clients.view', 'workshop.clients.create', 'workshop.clients.edit',
        'workshop.clients.delete', 'workshop.clients.activate', 'workshop.clients.deactivate',
        'workshop.equipment.view', 'workshop.equipment.create', 'workshop.equipment.edit',
        'workshop.equipment.delete',
    ];

    /** @var list<string> */
    private array $workshop_roles = [
        'Administrador',
        'Supervisor',
        'Operador',
        'Comercio',
    ];

    /** @var list<string> */
    private array $church_base_permissions = [
        'users.view', 'users.create', 'users.edit',
        'users.activate', 'users.deactivate',
        'businesses.view', 'businesses.edit',
        'reports.view', 'reports.export',
        'church.members.view', 'church.members.create', 'church.members.edit', 'church.members.delete',
        'church.congregations.view', 'church.congregations.create', 'church.congregations.edit', 'church.congregations.delete',
    ];

    /** @var list<string> */
    private array $church_roles = [
        'Pastor',
        'Secretario',
        'Lider de congregacion',
        'Comercio',
    ];

    /** @var list<string> */
    private array $education_base_permissions = [
        'users.view', 'users.create', 'users.edit',
        'users.activate', 'users.deactivate',
        'businesses.view', 'businesses.edit',
        'reports.view', 'reports.export',
    ];

    /** @var list<string> */
    private array $education_roles = [
        'Administrador',
        'Supervisor',
        'Comercio',
    ];

    public function run(): void
    {
        $taller = BusinessType::where('label', 'taller')->first();
        $iglesia = BusinessType::where('label', 'iglesia')->first();
        $centro  = BusinessType::where('label', 'centro_educativo')->first();

        if ($taller) {
            $this->syncType($taller, $this->workshop_roles, $this->withCatalogPermissions($this->workshop_base_permissions));
        }

        if ($iglesia) {
            $this->syncType($iglesia, $this->church_roles, $this->withCatalogPermissions($this->church_base_permissions));
        }

        if ($centro) {
            $this->syncType($centro, $this->education_roles, $this->withCatalogPermissions($this->education_base_permissions));
        }

        $this->command->info('Acceso por tipo de negocio sincronizado.');
    }

    /** @param list<string> $permissions @return list<string> */
    private function withCatalogPermissions(array $permissions): array
    {
        return array_values(array_unique([...$permissions, ...$this->catalog_permissions]));
    }

    /** @param list<string> $role_names @param list<string> $permission_names */
    private function syncType(BusinessType $type, array $role_names, array $permission_names): void
    {
        $role_ids = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $role_names)
            ->pluck('id')
            ->all();

        BusinessTypeAccess::syncBusinessTypeAccess($type, $role_ids, $permission_names);
    }
}
