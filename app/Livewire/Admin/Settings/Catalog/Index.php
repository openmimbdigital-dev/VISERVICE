<?php

namespace App\Livewire\Admin\Settings\Catalog;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Productos')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.view'), 403);
    }

    public function render()
    {
        return view('livewire.admin.settings.catalog.index', [
            'sections' => collect(CatalogProductsSettingsConfig::sections())
                ->filter(fn (array $section) => auth()->user()->can($section['permission'] ?? 'settings.view'))
                ->all(),
        ]);
    }
}
