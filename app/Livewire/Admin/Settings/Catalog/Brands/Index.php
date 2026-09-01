<?php

namespace App\Livewire\Admin\Settings\Catalog\Brands;

use App\Livewire\Admin\Settings\Catalog\CatalogProductsSettingsConfig;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Marcas de producto')]
class Index extends Component
{
    #[On('catalog-brand-deleted')]
    public function onRecordDeleted(): void {}

    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.brands.view'), 403);
    }

    public function render()
    {
        return view('livewire.admin.settings.catalog.brands.index', [
            'config' => CatalogProductsSettingsConfig::sectionOrFail('brands'),
        ]);
    }
}
