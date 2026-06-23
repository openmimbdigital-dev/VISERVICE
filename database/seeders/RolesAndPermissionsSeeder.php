<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
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
        ])->mapWithKeys(fn ($name) => [
            $name => Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]),
        ]);

        // ── Roles ───────────────────────────────────────────────────────────

        $superAdmin  = Role::firstOrCreate(['name' => 'superAdmin',    'guard_name' => $guard]);
        $admin       = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => $guard]);
        $comercio    = Role::firstOrCreate(['name' => 'Comercio',      'guard_name' => $guard]);
        $supervisor  = Role::firstOrCreate(['name' => 'Supervisor',    'guard_name' => $guard]);
        $operador    = Role::firstOrCreate(['name' => 'Operador',      'guard_name' => $guard]);

        // superAdmin tiene todos los permisos
        $superAdmin->syncPermissions($perms->values());

        // Administrador: todo menos suscripciones (las gestiona solo el superAdmin)
        $admin->syncPermissions($perms->only([
            'users.view', 'users.create', 'users.edit',
            'businesses.view', 'businesses.create', 'businesses.edit',
            'reports.view', 'reports.export',
            'settings.view',
            'roles.view',
            'workshop.clients.view', 'workshop.clients.create', 'workshop.clients.edit',
            'workshop.clients.activate', 'workshop.clients.deactivate',
        ])->values());

        // Comercio: propietario del negocio registrado vía onboarding
        $comercio->syncPermissions($perms->only([
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'businesses.view', 'businesses.edit',
            'reports.view', 'reports.export',
            'settings.view',
            'workshop.clients.view', 'workshop.clients.create', 'workshop.clients.edit',
            'workshop.clients.delete', 'workshop.clients.activate', 'workshop.clients.deactivate',
        ])->values());

        // Supervisor: solo lectura
        $supervisor->syncPermissions($perms->only([
            'users.view', 'businesses.view', 'reports.view', 'settings.view', 'roles.view',
        ])->values());

        // Operador: mínimo
        $operador->syncPermissions($perms->only([
            'users.view', 'businesses.view', 'reports.view',
        ])->values());

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
