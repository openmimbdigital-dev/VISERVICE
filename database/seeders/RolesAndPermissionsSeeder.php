<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Roles: superadmin, admin (CRUD users, business_types, businesses).
     * supervisor, tecnic: solo lectura sobre los mismos recursos.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard_name = 'web';

        $permission_users_view = Permission::firstOrCreate(
            ['name' => 'users.view', 'guard_name' => $guard_name]
        );
        $permission_users_create = Permission::firstOrCreate(
            ['name' => 'users.create', 'guard_name' => $guard_name]
        );
        $permission_users_update = Permission::firstOrCreate(
            ['name' => 'users.update', 'guard_name' => $guard_name]
        );
        $permission_users_delete = Permission::firstOrCreate(
            ['name' => 'users.delete', 'guard_name' => $guard_name]
        );

        $permission_business_types_view = Permission::firstOrCreate(
            ['name' => 'business_types.view', 'guard_name' => $guard_name]
        );
        $permission_business_types_create = Permission::firstOrCreate(
            ['name' => 'business_types.create', 'guard_name' => $guard_name]
        );
        $permission_business_types_update = Permission::firstOrCreate(
            ['name' => 'business_types.update', 'guard_name' => $guard_name]
        );
        $permission_business_types_delete = Permission::firstOrCreate(
            ['name' => 'business_types.delete', 'guard_name' => $guard_name]
        );

        $permission_businesses_view = Permission::firstOrCreate(
            ['name' => 'businesses.view', 'guard_name' => $guard_name]
        );
        $permission_businesses_create = Permission::firstOrCreate(
            ['name' => 'businesses.create', 'guard_name' => $guard_name]
        );
        $permission_businesses_update = Permission::firstOrCreate(
            ['name' => 'businesses.update', 'guard_name' => $guard_name]
        );
        $permission_businesses_delete = Permission::firstOrCreate(
            ['name' => 'businesses.delete', 'guard_name' => $guard_name]
        );

        $full_crud_permissions = [
            $permission_users_view,
            $permission_users_create,
            $permission_users_update,
            $permission_users_delete,
            $permission_business_types_view,
            $permission_business_types_create,
            $permission_business_types_update,
            $permission_business_types_delete,
            $permission_businesses_view,
            $permission_businesses_create,
            $permission_businesses_update,
            $permission_businesses_delete,
        ];

        $view_only_permissions = [
            $permission_users_view,
            $permission_business_types_view,
            $permission_businesses_view,
        ];

        $role_superadmin = Role::firstOrCreate(
            ['name' => 'superadmin', 'guard_name' => $guard_name]
        );
        $role_admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => $guard_name]
        );
        $role_supervisor = Role::firstOrCreate(
            ['name' => 'supervisor', 'guard_name' => $guard_name]
        );
        $role_tecnic = Role::firstOrCreate(
            ['name' => 'tecnic', 'guard_name' => $guard_name]
        );

        $role_superadmin->syncPermissions($full_crud_permissions);
        $role_admin->syncPermissions($full_crud_permissions);
        $role_supervisor->syncPermissions($view_only_permissions);
        $role_tecnic->syncPermissions($view_only_permissions);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
