<?php

namespace App\Livewire\Admin\Settings\Equipment\Brands;

use App\Actions\Settings\Equipment\CreateOrUpdateBrandAction;
use App\Livewire\Admin\Settings\Equipment\EquipmentSettingsConfig;
use App\Livewire\Forms\Admin\Settings\Equipment\BrandForm;
use App\Models\Brand;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Marcas')]
class Index extends Component
{
    public BrandForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.view'), 403);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->can('settings.edit'), 403);

        $this->form->reset();
        $this->form->active = true;
        $this->showModal    = true;
        $this->resetValidation();
    }

    #[On('open-brand-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->can('settings.edit'), 403);

        $brand = Brand::findOrFail($id);
        abort_unless($brand->isEditableBy(), 403);

        $this->form->setBrand($brand);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('brand-deleted')]
    public function onBrandDeleted(): void {}

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->form->reset();
        $this->form->active = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('settings.edit'), 403);

        $was_editing = $this->form->isEditing();

        CreateOrUpdateBrandAction::run(
            $this->form->brand_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Marca actualizada' : 'Marca creada',
            'icon'  => 'success',
        ]);

        $this->dispatch('brand-saved');
    }

    public function render()
    {
        return view('livewire.admin.settings.equipment.brands.index', [
            'config'           => EquipmentSettingsConfig::sectionOrFail('brands'),
            'is_super_admin'   => $this->form->isSuperAdmin(),
            'can_edit'         => auth()->user()->can('settings.edit'),
            'equipment_types'  => $this->form->getAvailableEquipmentTypes(),
        ]);
    }
}
