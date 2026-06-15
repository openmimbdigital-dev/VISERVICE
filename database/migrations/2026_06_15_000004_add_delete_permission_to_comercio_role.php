<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate(['name' => 'users.delete', 'guard_name' => 'web']);

        $role = Role::findByName('Comercio', 'web');
        if ($role && ! $role->hasPermissionTo('users.delete')) {
            $role->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::findByName('Comercio', 'web');
        $role?->revokePermissionTo('users.delete');
    }
};
