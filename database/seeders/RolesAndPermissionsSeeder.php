<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /** @return list<string> */
    private function businessCatalogPermissions(): array
    {
        return [
            'business_types.view', 'business_types.create', 'business_types.edit', 'business_types.delete',
            'organization_types.view', 'organization_types.create', 'organization_types.edit', 'organization_types.delete',
            'business_types.access.view', 'business_types.access.manage',
        ];
    }

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        // ── Permisos ────────────────────────────────────────────────────────

        $perms = collect([
            // Usuarios
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'users.activate', 'users.deactivate',
            // Empresas
            'businesses.view', 'businesses.create', 'businesses.edit', 'businesses.delete',
            'businesses.activate', 'businesses.deactivate', 'businesses.manage_addresses',
            // Reportes
            'reports.view', 'reports.export',
            // Configuración
            'settings.view', 'settings.edit',
            // Configuración — Atributos de equipo
            'settings.attributes.view', 'settings.attributes.create',
            'settings.attributes.edit', 'settings.attributes.delete',
            // Configuración — Tipos de equipo (solo superAdmin)
            'settings.equipment_types.view', 'settings.equipment_types.create',
            'settings.equipment_types.edit', 'settings.equipment_types.delete',
            // Roles
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.view', 'permissions.assign',
            // Suscripciones
            'subscriptions.view', 'subscriptions.create', 'subscriptions.edit', 'subscriptions.cancel',
            'subscriptions.plans.view', 'subscriptions.plans.manage',
            'subscriptions.invoices.view', 'subscriptions.invoices.manage',
            // Taller — Clientes
            'workshop.clients.view', 'workshop.clients.create', 'workshop.clients.edit',
            'workshop.clients.delete', 'workshop.clients.activate', 'workshop.clients.deactivate',
            // Taller — Equipos
            'workshop.equipment.view', 'workshop.equipment.create', 'workshop.equipment.edit',
            'workshop.equipment.delete',
            // Negocios — Tipos y acceso
            ...$this->businessCatalogPermissions(),
        ])->mapWithKeys(fn ($name) => [
            $name => Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]),
        ]);

        // ── Roles ───────────────────────────────────────────────────────────

        $superAdmin  = Role::firstOrCreate(['name' => 'superAdmin',    'guard_name' => $guard]);
        $admin       = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => $guard]);
        $comercio    = Role::firstOrCreate(['name' => 'Comercio',      'guard_name' => $guard]);
        $supervisor  = Role::firstOrCreate(['name' => 'Supervisor',    'guard_name' => $guard]);
        $operador    = Role::firstOrCreate(['name' => 'Operador',      'guard_name' => $guard]);
        $pastor      = Role::firstOrCreate(['name' => 'Pastor',        'guard_name' => $guard]);
        $secretario  = Role::firstOrCreate(['name' => 'Secretario',    'guard_name' => $guard]);
        $lider       = Role::firstOrCreate(['name' => 'Lider de congregacion', 'guard_name' => $guard]);

        // superAdmin tiene todos los permisos
        $superAdmin->syncPermissions($perms->values());

        // Administrador: todo menos suscripciones (las gestiona solo el superAdmin)
        $admin->syncPermissions($perms->only([
            'users.view', 'users.create', 'users.edit',
            'businesses.view', 'businesses.create', 'businesses.edit',
            'reports.view', 'reports.export',
            'settings.view', 'settings.edit',
            'settings.attributes.view', 'settings.attributes.create',
            'settings.attributes.edit', 'settings.attributes.delete',
            'roles.view',
            'workshop.clients.view', 'workshop.clients.create', 'workshop.clients.edit',
            'workshop.clients.activate', 'workshop.clients.deactivate',
            'workshop.equipment.view', 'workshop.equipment.create', 'workshop.equipment.edit',
            ...$this->businessCatalogPermissions(),
        ])->values());

        // Comercio: propietario del negocio registrado vía onboarding
        $comercio->syncPermissions($perms->only([
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'businesses.view', 'businesses.edit',
            'reports.view', 'reports.export',
            'settings.view', 'settings.edit',
            'settings.attributes.view', 'settings.attributes.create',
            'settings.attributes.edit', 'settings.attributes.delete',
            'workshop.clients.view', 'workshop.clients.create', 'workshop.clients.edit',
            'workshop.clients.delete', 'workshop.clients.activate', 'workshop.clients.deactivate',
            'workshop.equipment.view', 'workshop.equipment.create', 'workshop.equipment.edit',
            'workshop.equipment.delete',
            ...$this->businessCatalogPermissions(),
        ])->values());

        // Supervisor: solo lectura
        $supervisor->syncPermissions($perms->only([
            'users.view', 'businesses.view', 'reports.view', 'roles.view',
        ])->values());

        // Operador: mínimo
        $operador->syncPermissions($perms->only([
            'users.view', 'businesses.view', 'reports.view',
        ])->values());

        // Pastor: liderazgo pastoral
        $pastor->syncPermissions($perms->only([
            'users.view', 'users.create', 'users.edit', 'users.activate', 'users.deactivate',
            'businesses.view', 'businesses.edit',
            'reports.view', 'reports.export',
            ...$this->businessCatalogPermissions(),
            ])->values());

        // Secretario: administración
        $secretario->syncPermissions($perms->only([
            'users.view', 'users.create', 'users.edit',
            'businesses.view',
            'reports.view',
        ])->values());

        // Líder de congregación: operación de campo
        $lider->syncPermissions($perms->only([
            'users.view',
            'businesses.view',
            'reports.view',
        ])->values());

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
