<?php

namespace App\Support;

use App\Models\MenuSection;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class SidebarMenuBuilder
{
    public function build(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return MenuSection::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->with(['items' => fn ($query) => $query->where('active', true)->orderBy('sort_order')])
            ->get()
            ->filter(fn (MenuSection $section) => $section->isVisibleTo($user))
            ->map(fn (MenuSection $section) => $this->mapSection($section, $user))
            ->filter(fn (array $section) => $this->shouldRenderSection($section))
            ->values();
    }

    public function activeSectionSlugs(?User $user): array
    {
        return $this->build($user)
            ->filter(fn (array $section) => $section['is_active'])
            ->pluck('slug')
            ->all();
    }

    private function mapSection(MenuSection $section, User $user): array
    {
        $items = $section->items
            ->filter(fn ($item) => $item->isVisibleTo($user))
            ->map(fn ($item) => $this->mapItem($item))
            ->values()
            ->all();

        $route_name = $section->route_name;
        $url        = $route_name && Route::has($route_name) ? route($route_name) : null;

        return [
            'slug'              => $section->slug,
            'name'              => $section->name,
            'icon_svg_path'     => $section->icon_svg_path,
            'icon_color_class'  => $section->icon_color_class,
            'behavior'          => $section->behavior,
            'route_name'        => $route_name,
            'url'               => $url,
            'route_patterns'    => $section->route_patterns ?? [],
            'is_active'         => $section->isActiveForRequest(),
            'items'             => $items,
        ];
    }

    private function mapItem($item): array
    {
        $url = Route::has($item->route_name) ? route($item->route_name) : '#';

        return [
            'name'              => $item->name,
            'url'               => $url,
            'icon_svg_path'     => $item->icon_svg_path,
            'icon_color_class'  => $item->icon_color_class,
            'is_active'         => $item->isActiveForRequest(),
            'badge'             => $this->resolveBadge($item->badge_key),
        ];
    }

    private function resolveBadge(?string $badge_key): ?int
    {
        return match ($badge_key) {
            'pending_subscription_payments' => SubscriptionInvoice::query()
                ->where('status', 'pending')
                ->whereHas('subscription', fn ($q) => $q->where('status', 'pending'))
                ->count() ?: null,
            default => null,
        };
    }

    private function shouldRenderSection(array $section): bool
    {
        if ($section['behavior'] === 'single_link') {
            return $section['url'] !== null;
        }

        return count($section['items']) > 0;
    }
}
