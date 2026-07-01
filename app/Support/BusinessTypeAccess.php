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
        return config('permissions.global_roles', ['Comercio']);
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
        $query = Role::query()->where('guard_name', 'web');

        if ($business_type_ids === []) {
            return $query->whereIn('name', static::globalRoleNames());
        }

        return $query->where(function ($q) use ($business_type_ids) {
            $q->whereIn('name', static::globalRoleNames())
                ->orWhereHas('businessTypes', fn ($bt) => $bt->whereIn('business_types.id', $business_type_ids));
        })->whereNotIn('name', static::systemRoleNames());
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

    public static function permissionEnabledForUser(?User $user, string $permission): bool
    {
        if (! $user || $user->hasRole('superAdmin')) {
            return true;
        }

        if (! $user->can($permission)) {
            return false;
        }

        $type_id = static::primaryBusinessTypeId($user);

        if ($type_id === null) {
            return true;
        }

        return static::permissionEnabledForBusinessType($permission, $type_id);
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
