<?php

namespace App\Livewire\Admin\Settings\Catalog\Brands;

use App\Actions\Settings\Catalog\CreateOrUpdateCatalogBrandAction;
use App\Livewire\Admin\Settings\Catalog\CatalogProductsSettingsConfig;
use App\Livewire\Forms\Admin\Settings\Catalog\CatalogBrandForm;
use App\Models\Brand;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    use LivewireAlert;

    public CatalogBrandForm $form;

    public function mount(?Brand $brand = null): void
    {
        if ($brand) {
            abort_unless(auth()->user()->can('settings.brands.edit'), 403);
            abort_unless(
                Brand::query()->visibleToUser()->forItemsCatalog()->whereKey($brand->id)->exists(),
                404
            );
            abort_unless($brand->isEditableBy(), 403);

            $this->form->setBrand($brand);
        } else {
            abort_unless(auth()->user()->can('settings.brands.create'), 403);
            $this->form->reset();
            $this->form->active = true;
        }
    }

    public function save(): void
    {
        try {
            abort_unless(
                auth()->user()->can($this->form->isEditing() ? 'settings.brands.edit' : 'settings.brands.create'),
                403
            );

            if ($this->form->isEditing()) {
                abort_unless(
                    Brand::query()->visibleToUser()->forItemsCatalog()->whereKey($this->form->brand_id)->exists(),
                    404
                );
            }

            $was_editing = $this->form->isEditing();

            CreateOrUpdateCatalogBrandAction::run(
                $this->form->brand_id,
                $this->form->validated()
            );

            $this->alert('success', $was_editing ? 'Marca actualizada correctamente.' : 'Marca creada correctamente.', [
                'position' => 'top-end',
                'timer'    => 3000,
                'toast'    => true,
            ]);

            $this->redirectRoute('admin.settings.catalog-products.brands.index', navigate: true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->alert('error', 'Error: ' . $e->getMessage(), [
                'position' => 'top-end',
                'timer'    => 5000,
                'toast'    => true,
            ]);
        }
    }

    public function render()
    {
        $config = CatalogProductsSettingsConfig::sectionOrFail('brands');

        return view('livewire.admin.settings.catalog.brands.form', [
            'config'          => $config,
            'item_categories' => $this->form->getAvailableItemCategories(),
            'is_super_admin'  => $this->form->isSuperAdmin(),
        ])->layoutData([
            'title' => $this->form->isEditing() ? 'Configuración — Editar marca' : 'Configuración — Nueva marca',
        ]);
    }
}
