<?php

namespace App\Livewire\Admin\Settings\General;

class GeneralSettingsConfig
{
    /**
     * Secciones disponibles en configuración general.
     */
    public static function sections(): array
    {
        return [
            'statuses' => [
                'key'                => 'statuses',
                'title'              => 'Estados',
                'description'        => 'Catálogo global de estados del sistema (cotizaciones, órdenes de trabajo, remisiones, pagos, etc.).',
                'button_text'        => 'Gestionar estados',
                'create_button_text' => 'Nuevo estado',
                'route'              => 'admin.settings.general.statuses.index',
                'permission'         => 'settings.statuses.view',
                'card_bg'            => 'bg-slate-50/60 border-slate-100/80',
                'icon_bg'            => 'bg-slate-100',
                'icon_c'             => 'text-slate-600',
                'btn_class'          => 'btn-primary',
                'icon'               => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
        ];
    }

    public static function section(string $key): ?array
    {
        return static::sections()[$key] ?? null;
    }

    public static function sectionOrFail(string $key): array
    {
        $section = static::section($key);

        if (! $section) {
            abort(404);
        }

        return $section + [
            'index_route' => 'admin.settings.general.index',
        ];
    }
}
