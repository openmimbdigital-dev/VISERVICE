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

        $role = Role::query()->where('name', 'Comercio')->where('guard_name', 'web')->first();
        if (! $role || $role->hasPermissionTo('users.delete')) {
            return;
        }

        $role->givePermissionTo($permission);
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::query()->where('name', 'Comercio')->where('guard_name', 'web')->first();
        if ($role?->hasPermissionTo('users.delete')) {
            $role->revokePermissionTo('users.delete');
        }
    }
};
