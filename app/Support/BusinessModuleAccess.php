<?php

namespace App\Support;

use App\Models\Business;
use App\Models\MenuItem;
use App\Models\MenuSection;
use App\Models\User;

class BusinessModuleAccess
{
    public static function bypassesModuleChecks(?User $user): bool
    {
        return $user !== null && $user->hasRole('superAdmin');
    }

    /** Los hijos heredan módulos del ancestro raíz. */
    public static function moduleOwnerBusiness(Business $business): Business
    {
        $current = $business;

        while ($current->business_id) {
            $parent = $current->relationLoaded('parent_business')
                ? $current->parent_business
                : $current->parent_business()->first();

            if (! $parent) {
                break;
            }

            $current = $parent;
        }

        return $current;
    }

    public static function canManageModules(Business $business): bool
    {
        return $business->business_id === null;
    }

    public static function sectionIsPlatform(MenuSection $section): bool
    {
        return in_array($section->slug, config('business_modules.platform_section_slugs', []), true)
            || ! $section->assignable_to_business;
    }

    /** @return list<int> */
    public static function enabledSectionIdsForBusiness(Business $business): array
    {
        $owner = self::moduleOwnerBusiness($business);

        return $owner->menuSections()->pluck('menu_sections.id')->map(fn ($id) => (int) $id)->all();
    }

    /** @return list<int> */
    public static function enabledMenuItemIdsForBusiness(Business $business): array
    {
        $owner = self::moduleOwnerBusiness($business);

        return $owner->menuItems()->pluck('menu_items.id')->map(fn ($id) => (int) $id)->all();
    }

    public static function sectionEnabledForUser(User $user, MenuSection $section): bool
    {
        if (self::bypassesModuleChecks($user) || self::sectionIsPlatform($section)) {
            return true;
        }

        $business = CurrentBusiness::get() ?? $user->primaryBusiness();

        if (! $business) {
            return false;
        }

        return in_array($section->id, self::enabledSectionIdsForBusiness($business), true);
    }

    public static function menuItemEnabledForUser(User $user, MenuItem $item): bool
    {
        if (self::bypassesModuleChecks($user)) {
            return true;
        }

        $section = $item->relationLoaded('section') ? $item->section : $item->section()->first();

        if (! $section || self::sectionIsPlatform($section)) {
            return true;
        }

        if (! self::sectionEnabledForUser($user, $section)) {
            return false;
        }

        $business = CurrentBusiness::get() ?? $user->primaryBusiness();

        if (! $business) {
            return false;
        }

        return in_array($item->id, self::enabledMenuItemIdsForBusiness($business), true);
    }

    public static function routeIsAllowedForUser(User $user, ?string $route_name): bool
    {
        if ($route_name === null || self::bypassesModuleChecks($user)) {
            return true;
        }

        $item = self::findMenuItemForRoute($route_name);

        if ($item) {
            return self::menuItemEnabledForUser($user, $item);
        }

        $section = self::findSectionForRoute($route_name);

        if (! $section || self::sectionIsPlatform($section)) {
            return true;
        }

        return self::sectionEnabledForUser($user, $section);
    }

    public static function syncBusinessModules(
        Business $business,
        array $menu_section_ids,
        array $menu_item_ids
    ): void {
        abort_unless(self::canManageModules($business), 403);

        $assignable_section_ids = MenuSection::query()
            ->where('assignable_to_business', true)
            ->whereIn('id', $menu_section_ids)
            ->pluck('id')
            ->all();

        $valid_item_ids = MenuItem::query()
            ->whereIn('menu_section_id', $assignable_section_ids)
            ->whereIn('id', $menu_item_ids)
            ->pluck('id')
            ->all();

        $business->menuSections()->sync($assignable_section_ids);
        $business->menuItems()->sync($valid_item_ids);
    }

    private static function findSectionForRoute(string $route_name): ?MenuSection
    {
        $sections = MenuSection::query()
            ->where('active', true)
            ->get();

        foreach ($sections as $section) {
            if ($section->route_name === $route_name) {
                return $section;
            }

            foreach ($section->route_patterns ?? [] as $pattern) {
                if (self::routeMatchesPattern($route_name, $pattern)) {
                    return $section;
                }
            }
        }

        return null;
    }

    private static function findMenuItemForRoute(string $route_name): ?MenuItem
    {
        return MenuItem::query()
            ->where('active', true)
            ->with('section')
            ->get()
            ->first(function (MenuItem $item) use ($route_name) {
                if ($item->route_name === $route_name) {
                    return true;
                }

                $pattern = $item->active_route_pattern ?? $item->route_name;

                return $pattern && self::routeMatchesPattern($route_name, $pattern);
            });
    }

    private static function routeMatchesPattern(string $route_name, string $pattern): bool
    {
        $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/';

        return (bool) preg_match($regex, $route_name);
    }
}
