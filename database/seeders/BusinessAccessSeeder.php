<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\OrganizationType;
use App\Models\Role;
use App\Support\BusinessAccess;
use Illuminate\Database\Seeder;

class BusinessAccessSeeder extends Seeder
{
    /** @return list<string> */
    private function teamPositionPermissions(): array
    {
        return [
            'team_positions.view', 'team_positions.create', 'team_positions.edit', 'team_positions.delete',
        ];
    }

    /** @return list<string> */
    private function businessPaymentSettingsPermissions(): array
    {
        return [
            'business_payment_methods.view', 'business_payment_methods.create', 'business_payment_methods.edit', 'business_payment_methods.delete',
            'business_bank_accounts.view', 'business_bank_accounts.create', 'business_bank_accounts.edit', 'business_bank_accounts.delete',
        ];
    }

    /** @return list<string> */
    private function quotationModulePermissions(): array
    {
        return [
            'workshop.quotations.view',
            'workshop.quotations.create',
            'workshop.quotations.edit',
            'workshop.quotations.delete',
            'workshop.quotation_service_types.view',
            'workshop.quotation_service_types.create',
            'workshop.quotation_service_types.edit',
            'workshop.quotation_service_types.delete',
        ];
    }

    /** @return list<string> */
    private function workshopModulePermissions(): array
    {
        return array_values(array_unique([
            'workshop.view',
            'workshop.clients.view', 'workshop.clients.create', 'workshop.clients.edit',
            'workshop.clients.delete', 'workshop.clients.activate', 'workshop.clients.deactivate',
            'workshop.equipment.view', 'workshop.equipment.create', 'workshop.equipment.edit',
            'workshop.equipment.delete',
            ...$this->quotationModulePermissions(),
            'workshop.work-orders.view', 'workshop.work-orders.create', 'workshop.work-orders.edit',
            'workshop.work-orders.delete',
        ]));
    }

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
    private function catalogProductsPermissions(): array
    {
        return [
            'catalog.view',
            'catalog.products.view', 'catalog.products.create', 'catalog.products.edit', 'catalog.products.delete',
        ];
    }

    /** @return list<string> */
    private function catalogProductsSettingsPermissions(): array
    {
        return [
            'settings.product_types.view', 'settings.product_types.create', 'settings.product_types.edit', 'settings.product_types.delete',
            'settings.product_categories.view', 'settings.product_categories.create', 'settings.product_categories.edit', 'settings.product_categories.delete',
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
        $taller = OrganizationType::where('label', 'taller')->first();
        $iglesia = OrganizationType::where('label', 'iglesia')->first();
        $centro  = OrganizationType::where('label', 'centro_educativo')->first();

        if ($taller) {
            $this->syncBusinessesOfType($taller, $this->workshop_roles, $this->tallerPermissions());
        }

        if ($iglesia) {
            $this->syncBusinessesOfType($iglesia, $this->church_roles, $this->withPlatformExcludedBusinessPermissions($this->church_base_permissions));
        }

        if ($centro) {
            $this->syncBusinessesOfType($centro, $this->education_roles, $this->withPlatformExcludedBusinessPermissions($this->education_base_permissions));
        }

        $this->command->info('Acceso por negocio sincronizado.');
    }

    /**
     * Permisos de negocio (sin Gestión de Negocios: solo superAdmin).
     *
     * @param  list<string>  $permissions
     * @return list<string>
     */
    private function withPlatformExcludedBusinessPermissions(array $permissions): array
    {
        return array_values(array_unique([
            ...$permissions,
            ...$this->teamPositionPermissions(),
            ...$this->businessPaymentSettingsPermissions(),
            ...$this->catalogProductsPermissions(),
            ...$this->catalogProductsSettingsPermissions(),
        ]));
    }

    /** @return list<string> */
    private function tallerPermissions(): array
    {
        return array_values(array_unique([
            ...$this->workshop_base_permissions,
            ...$this->workshopModulePermissions(),
            ...$this->teamPositionPermissions(),
            ...$this->businessPaymentSettingsPermissions(),
            ...$this->catalogProductsPermissions(),
            ...$this->catalogProductsSettingsPermissions(),
        ]));
    }

    /** @param list<string> $role_names @param list<string> $permission_names */
    private function syncBusinessesOfType(OrganizationType $type, array $role_names, array $permission_names): void
    {
        $role_ids = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $role_names)
            ->pluck('id')
            ->all();

        $businesses = Business::query()
            ->where('organization_type_id', $type->id)
            ->get();

        foreach ($businesses as $business) {
            BusinessAccess::syncBusinessAccess($business, $role_ids, $permission_names);
        }
    }
}
