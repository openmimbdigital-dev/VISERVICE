<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessType;
use App\Models\Role;
use App\Support\BusinessAccess;
use Illuminate\Database\Seeder;

class BusinessAccessSeeder extends Seeder
{
    /** @var list<string> */
    private array $business_catalog_permissions = [
        'business_types.view', 'business_types.create', 'business_types.edit', 'business_types.delete',
        'organization_types.view', 'organization_types.create', 'organization_types.edit', 'organization_types.delete',
        'business_types.access.view', 'business_types.access.manage',
    ];

    /** @return list<string> */
    private function teamPositionPermissions(): array
    {
        return [
            'team_positions.view', 'team_positions.create', 'team_positions.edit', 'team_positions.delete',
        ];
    }

    /** @var list<string> */
    private array $workshop_module_permissions = [
        'workshop.view',
        'workshop.clients.view', 'workshop.clients.create', 'workshop.clients.edit',
        'workshop.clients.delete', 'workshop.clients.activate', 'workshop.clients.deactivate',
        'workshop.equipment.view', 'workshop.equipment.create', 'workshop.equipment.edit',
        'workshop.equipment.delete',
        'workshop.quotations.view',
        'workshop.work-orders.view',
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
        'settings.brands.view', 'settings.brands.create',
        'settings.brands.edit', 'settings.brands.delete',
        'settings.model_equipment.view', 'settings.model_equipment.create',
        'settings.model_equipment.edit', 'settings.model_equipment.delete',
        'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        'permissions.view', 'permissions.assign',
    ];

    /** @return list<string> */
    private function catalogItemsPermissions(): array
    {
        return [
            'catalog.view',
            'catalog.items.view', 'catalog.items.create', 'catalog.items.edit', 'catalog.items.delete',
        ];
    }

    /** @return list<string> */
    private function catalogProductsSettingsPermissions(): array
    {
        return [
            'settings.item_types.view', 'settings.item_types.create', 'settings.item_types.edit', 'settings.item_types.delete',
            'settings.item_categories.view', 'settings.item_categories.create', 'settings.item_categories.edit', 'settings.item_categories.delete',
            'settings.units.view', 'settings.units.create', 'settings.units.edit', 'settings.units.delete',
        ];
    }

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
        'roles.view', 'permissions.view',
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
        'roles.view', 'permissions.view',
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
            $this->syncBusinessesOfType($taller, $this->workshop_roles, $this->tallerPermissions());
        }

        if ($iglesia) {
            $this->syncBusinessesOfType($iglesia, $this->church_roles, $this->withBusinessCatalogPermissions($this->church_base_permissions));
        }

        if ($centro) {
            $this->syncBusinessesOfType($centro, $this->education_roles, $this->withBusinessCatalogPermissions($this->education_base_permissions));
        }

        $this->command->info('Acceso por negocio sincronizado.');
    }

    /** @param list<string> $permissions @return list<string> */
    private function withBusinessCatalogPermissions(array $permissions): array
    {
        return array_values(array_unique([
            ...$permissions,
            ...$this->business_catalog_permissions,
            ...$this->teamPositionPermissions(),
            ...$this->catalogItemsPermissions(),
            ...$this->catalogProductsSettingsPermissions(),
        ]));
    }

    /** @return list<string> */
    private function tallerPermissions(): array
    {
        return array_values(array_unique([
            ...$this->workshop_base_permissions,
            ...$this->workshop_module_permissions,
            ...$this->business_catalog_permissions,
            ...$this->teamPositionPermissions(),
            ...$this->catalogItemsPermissions(),
            ...$this->catalogProductsSettingsPermissions(),
        ]));
    }

    /** @param list<string> $role_names @param list<string> $permission_names */
    private function syncBusinessesOfType(BusinessType $type, array $role_names, array $permission_names): void
    {
        $role_ids = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $role_names)
            ->pluck('id')
            ->all();

        $businesses = Business::query()
            ->where('business_type_id', $type->id)
            ->get();

        foreach ($businesses as $business) {
            BusinessAccess::syncBusinessAccess($business, $role_ids, $permission_names);
        }
    }
}
