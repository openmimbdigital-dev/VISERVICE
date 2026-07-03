<?php

namespace App\Support;

use App\Models\Business;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

class BusinessAccess
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
    public static function businessIdsForUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return $user->businessIds();
    }

    public static function primaryBusinessId(?User $user): ?int
    {
        $id = $user?->primaryBusiness()?->id;

        return $id !== null ? (int) $id : null;
    }

    public static function rolesQueryForBusinesses(array $business_ids): \Illuminate\Database\Eloquent\Builder
    {
        $query = Role::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', static::systemRoleNames());

        $global = static::globalRoleNames();

        if ($business_ids === []) {
            if ($global === []) {
                return $query->whereRaw('0 = 1');
            }

            return $query->whereIn('name', $global);
        }

        if ($global === []) {
            return $query->whereHas(
                'businesses',
                fn ($b) => $b->whereIn('businesses.id', $business_ids)
            );
        }

        return $query->where(function ($q) use ($business_ids, $global) {
            $q->whereIn('name', $global)
                ->orWhereHas('businesses', fn ($b) => $b->whereIn('businesses.id', $business_ids));
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

        $business_ids = static::businessIdsForUser($target_user);

        return static::rolesQueryForBusinesses($business_ids)
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

    public static function manageableRolesForUser(?User $user): Collection
    {
        if (! $user || $user->hasRole('superAdmin')) {
            return Role::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get();
        }

        $business_id = static::primaryBusinessId($user);

        if ($business_id === null) {
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

        return static::rolesQueryForBusinesses([$business_id])
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

        $business_id = static::primaryBusinessId($user);

        if ($business_id === null) {
            return [];
        }

        return static::enabledPermissionNamesForBusiness($business_id);
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

    /** @return list<string> */
    public static function enabledPermissionNamesForBusiness(int $business_id): array
    {
        return Business::query()
            ->whereKey($business_id)
            ->first()
            ?->permissions()
            ->pluck('name')
            ->all() ?? [];
    }

    public static function syncBusinessAccess(
        Business $business,
        array $role_ids,
        array $permission_names
    ): void {
        $business->roles()->sync($role_ids);

        $permission_ids = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $permission_names)
            ->pluck('id');

        $business->permissions()->sync($permission_ids);
    }
}
