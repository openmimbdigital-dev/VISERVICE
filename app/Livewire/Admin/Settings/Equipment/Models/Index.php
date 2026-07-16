<?php

namespace App\Livewire\Admin\Settings\Equipment\Models;

use App\Actions\Settings\Equipment\CreateOrUpdateEquipmentModelAction;
use App\Livewire\Admin\Settings\Equipment\EquipmentSettingsConfig;
use App\Livewire\Forms\Admin\Settings\Equipment\EquipmentModelForm;
use App\Models\Brand;
use App\Models\EquipmentModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Modelos')]
class Index extends Component
{
    public EquipmentModelForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.model_equipment.view'), 403);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->can('settings.model_equipment.create'), 403);

        $this->form->reset();
        $this->form->active = true;
        $this->showModal    = true;
        $this->resetValidation();
    }

    #[On('open-equipment-model-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->can('settings.model_equipment.edit'), 403);

        $equipment_model = EquipmentModel::query()->visibleToUser()->findOrFail($id);
        abort_unless($equipment_model->isEditableBy(), 403);

        $this->form->setEquipmentModel($equipment_model);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('equipment-model-deleted')]
    public function onEquipmentModelDeleted(): void {}

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
            auth()->user()->can($this->form->isEditing() ? 'settings.model_equipment.edit' : 'settings.model_equipment.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        CreateOrUpdateEquipmentModelAction::run(
            $this->form->equipment_model_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Modelo actualizado' : 'Modelo creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('equipment-model-saved');
    }

    public function render()
    {
        return view('livewire.admin.settings.equipment.models.index', [
            'config'         => EquipmentSettingsConfig::sectionOrFail('models'),
            'is_super_admin' => $this->form->isSuperAdmin(),
            'brands'         => Brand::query()->visibleToUser()->active()->orderBy('name')->get(),
            'can_edit'       => auth()->user()->can('settings.model_equipment.edit'),
        ]);
    }
}
