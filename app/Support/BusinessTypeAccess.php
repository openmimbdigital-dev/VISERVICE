<?php

namespace App\Support;

use App\Models\BusinessType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

class BusinessTypeAccess
{
    /** @return list<string> */
    public static function globalRoleNames(): array
    {
        return config('permissions.global_roles', []);
    }

    /** @return list<string> */
    public static function systemRoleNames(): array
    {
        return config('permissions.system_roles', ['superAdmin']);
    }

    /** @return list<int> */
    public static function businessTypeIdsForUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        if ($user->relationLoaded('businesses')) {
            return $user->businesses
                ->pluck('business_type_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        return $user->businesses()
            ->pluck('businesses.business_type_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public static function primaryBusinessTypeId(?User $user): ?int
    {
        $business = $user?->primaryBusiness();

        return $business?->business_type_id ? (int) $business->business_type_id : null;
    }

    public static function rolesQueryForBusinessTypes(array $business_type_ids): \Illuminate\Database\Eloquent\Builder
    {
        $query = Role::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', static::systemRoleNames());

        $global = static::globalRoleNames();

        if ($business_type_ids === []) {
            if ($global === []) {
                return $query->whereRaw('0 = 1');
            }

            return $query->whereIn('name', $global);
        }

        if ($global === []) {
            return $query->whereHas(
                'businessTypes',
                fn ($bt) => $bt->whereIn('business_types.id', $business_type_ids)
            );
        }

        return $query->where(function ($q) use ($business_type_ids, $global) {
            $q->whereIn('name', $global)
                ->orWhereHas('businessTypes', fn ($bt) => $bt->whereIn('business_types.id', $business_type_ids));
        });
    }

    public static function assignableRolesForUser(?User $target_user): Collection
    {
        if (! $target_user) {
            return Role::query()
                ->where('guard_name', 'web')
                ->whereNotIn('name', static::systemRoleNames())
                ->orderBy('name')
                ->get();
        }

        $type_ids = static::businessTypeIdsForUser($target_user);

        return static::rolesQueryForBusinessTypes($type_ids)
            ->orderBy('name')
            ->get();
    }

    public static function roleAllowedForUser(string $role_name, User $target_user): bool
    {
        if (in_array($role_name, static::systemRoleNames(), true)) {
            return false;
        }

        return static::assignableRolesForUser($target_user)->contains('name', $role_name);
    }

    public static function permissionEnabledForBusinessType(string $permission, ?int $business_type_id): bool
    {
        if ($business_type_id === null) {
            return true;
        }

        return BusinessType::query()
            ->whereKey($business_type_id)
            ->whereHas('permissions', fn ($q) => $q->where('name', $permission))
            ->exists();
    }

    public static function manageableRolesForUser(?User $user): Collection
    {
        if (! $user || $user->hasRole('superAdmin')) {
            return Role::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get();
        }

        $type_id = static::primaryBusinessTypeId($user);

        if ($type_id === null) {
            $global = static::globalRoleNames();

            if ($global === []) {
                return collect();
            }

            return Role::query()
                ->where('guard_name', 'web')
                ->whereIn('name', $global)
                ->orderBy('name')
                ->get();
        }

        return static::rolesQueryForBusinessTypes([$type_id])
            ->orderBy('name')
            ->get();
    }

    public static function roleManageableByUser(\Spatie\Permission\Models\Role $role, ?User $user): bool
    {
        if (! $user || $user->hasRole('superAdmin')) {
            return true;
        }

        return static::manageableRolesForUser($user)->contains('id', $role->id);
    }

    /** @return list<string> */
    public static function manageablePermissionNamesForUser(?User $user): array
    {
        if (! $user || $user->hasRole('superAdmin')) {
            return Permission::query()
                ->where('guard_name', 'web')
                ->pluck('name')
                ->all();
        }

        $type_id = static::primaryBusinessTypeId($user);

        if ($type_id === null) {
            return [];
        }

        return static::enabledPermissionNamesForBusinessType($type_id);
    }

    /** @return array<string, array{name: string, permissions: array<string, string>}> */
    public static function manageableModulesForUser(?User $user): array
    {
        $modules = config('permissions.modules', []);

        if (! $user || $user->hasRole('superAdmin')) {
            return $modules;
        }

        $allowed = static::manageablePermissionNamesForUser($user);

        return collect($modules)
            ->map(function (array $module) use ($allowed) {
                $permissions = array_filter(
                    $module['permissions'],
                    fn (string $label, string $name) => in_array($name, $allowed, true),
                    ARRAY_FILTER_USE_BOTH
                );

                if ($permissions === []) {
                    return null;
                }

                $module['permissions'] = $permissions;

                return $module;
            })
            ->filter()
            ->all();
    }

    public static function permissionEnabledForUser(?User $user, string $permission): bool
    {
        return (bool) $user?->can($permission);
    }

    /** @return list<string> */
    public static function enabledPermissionNamesForBusinessType(int $business_type_id): array
    {
        return BusinessType::query()
            ->whereKey($business_type_id)
            ->first()
            ?->permissions()
            ->pluck('name')
            ->all() ?? [];
    }

    public static function syncBusinessTypeAccess(
        BusinessType $business_type,
        array $role_ids,
        array $permission_names
    ): void {
        $business_type->roles()->sync($role_ids);

        $permission_ids = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $permission_names)
            ->pluck('id');

        $business_type->permissions()->sync($permission_ids);
    }
}
