<?php

namespace App\Livewire\Admin\Settings\Catalog\Units;

use App\Actions\Settings\Catalog\CreateOrUpdateUnitAction;
use App\Livewire\Admin\Settings\Catalog\CatalogProductsSettingsConfig;
use App\Livewire\Forms\Admin\Settings\Catalog\UnitForm;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Unidades')]
class Index extends Component
{
    public UnitForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.units.view'), 403);

        $edit_id = request()->integer('edit');

        if ($edit_id > 0) {
            $this->openEdit($edit_id);
        }
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->can('settings.units.create'), 403);

        $this->form->reset();
        $this->form->active = true;
        $this->showModal    = true;
        $this->resetValidation();
    }

    #[On('open-unit-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->can('settings.units.edit'), 403);

        $unit = Unit::query()->visibleToUser()->findOrFail($id);
        abort_unless($unit->isEditableBy(), 403);

        $this->form->setUnit($unit);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('unit-deleted')]
    public function onRecordDeleted(): void {}

    #[On('unit-saved')]
    public function onRecordSaved(): void {}

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->form->reset();
        $this->form->active = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()->can($this->form->isEditing() ? 'settings.units.edit' : 'settings.units.create'),
            403
        );

        if ($this->form->isEditing()) {
            abort_unless(
                Unit::query()->visibleToUser()->whereKey($this->form->unit_id)->exists(),
                404
            );
        }

        $was_editing = $this->form->isEditing();

        CreateOrUpdateUnitAction::run(
            $this->form->unit_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Unidad actualizada' : 'Unidad creada',
            'icon'  => 'success',
        ]);

        $this->dispatch('unit-saved');
    }

    public function render()
    {
        return view('livewire.admin.settings.catalog.units.index', [
            'config'         => CatalogProductsSettingsConfig::sectionOrFail('units'),
            'is_super_admin' => $this->form->isSuperAdmin(),
        ]);
    }
}
