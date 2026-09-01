<?php

namespace App\Livewire\Admin;

use App\Support\SidebarMenuBuilder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Inicio')]
class Dashboard extends Component
{
    public function render()
    {
        $sections = app(SidebarMenuBuilder::class)->build(auth()->user());

        $modules = $sections
            ->values()
            ->map(function (array $section, int $index) {
                $theme = $this->cardTheme($section['icon_color_class'] ?? null, $index);
                $links = $this->sectionLinks($section);

                return [
                    'title' => org_term($section['name']),
                    'desc' => $this->sectionDescription($section),
                    'icon' => $section['icon_svg_path'] ?? '',
                    'bg' => $theme['bg'],
                    'icon_bg' => $theme['icon_bg'],
                    'icon_c' => $theme['icon_c'],
                    'links' => $links,
                ];
            })
            ->filter(fn (array $module) => $module['links'] !== [])
            ->values()
            ->all();

        return view('livewire.dashboard', [
            'modules' => $modules,
        ]);
    }

    /** @param  array{behavior: string, url: ?string, name: string, items: list<array{name: string, url: string}>}  $section */
    private function sectionLinks(array $section): array
    {
        if ($section['behavior'] === 'single_link') {
            if (empty($section['url'])) {
                return [];
            }

            return [[
                'label' => org_term($section['name']),
                'url' => $section['url'],
            ]];
        }

        return collect($section['items'] ?? [])
            ->filter(fn (array $item) => ! empty($item['url']) && $item['url'] !== '#')
            ->map(fn (array $item) => [
                'label' => org_term($item['name']),
                'url' => $item['url'],
            ])
            ->values()
            ->all();
    }

    /** @param  array{behavior: string, items: list<array{name: string}>}  $section */
    private function sectionDescription(array $section): string
    {
        $labels = collect($section['items'] ?? [])
            ->pluck('name')
            ->map(fn ($name) => org_term((string) $name))
            ->filter()
            ->take(4)
            ->values();

        if ($labels->isEmpty()) {
            return 'Acceso al módulo asignado a tu negocio.';
        }

        return $labels->join(', ').'.';
    }

    /** @return array{bg: string, icon_bg: string, icon_c: string} */
    private function cardTheme(?string $icon_color_class, int $index): array
    {
        $themes = [
            ['bg' => 'bg-indigo-50 border-indigo-100', 'icon_bg' => 'bg-indigo-100', 'icon_c' => 'text-indigo-600'],
            ['bg' => 'bg-emerald-50 border-emerald-100', 'icon_bg' => 'bg-emerald-100', 'icon_c' => 'text-emerald-600'],
            ['bg' => 'bg-sky-50 border-sky-100', 'icon_bg' => 'bg-sky-100', 'icon_c' => 'text-sky-600'],
            ['bg' => 'bg-amber-50 border-amber-100', 'icon_bg' => 'bg-amber-100', 'icon_c' => 'text-amber-600'],
            ['bg' => 'bg-violet-50 border-violet-100', 'icon_bg' => 'bg-violet-100', 'icon_c' => 'text-violet-600'],
            ['bg' => 'bg-rose-50 border-rose-100', 'icon_bg' => 'bg-rose-100', 'icon_c' => 'text-rose-600'],
        ];

        foreach (['emerald', 'sky', 'amber', 'violet', 'rose', 'indigo'] as $color) {
            if ($icon_color_class && str_contains($icon_color_class, $color) && $color !== 'indigo') {
                return collect($themes)->first(
                    fn (array $theme) => str_contains($theme['icon_c'], $color)
                ) ?? $themes[0];
            }
        }

        return $themes[$index % count($themes)];
    }
}
