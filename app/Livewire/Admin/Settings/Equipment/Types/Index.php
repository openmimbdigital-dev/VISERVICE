<?php

namespace App\Livewire\Admin\Settings\Equipment\Types;

use App\Actions\Settings\Equipment\CreateOrUpdateEquipmentTypeAction;
use App\Livewire\Admin\Settings\Equipment\EquipmentSettingsConfig;
use App\Livewire\Forms\Admin\Settings\Equipment\EquipmentTypeForm;
use App\Models\EquipmentType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Tipos de equipo')]
class Index extends Component
{
    public EquipmentTypeForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('settings.equipment_types.view'), 403);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->can('settings.equipment_types.create'), 403);

        $this->form->reset();
        $this->form->active = true;
        $this->showModal    = true;
        $this->resetValidation();
    }

    #[On('open-equipment-type-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->can('settings.equipment_types.edit'), 403);

        $equipment_type = EquipmentType::query()
            ->when(
                ! auth()->user()->hasRole('superAdmin'),
                fn ($query) => $query->visibleToUser()
            )
            ->findOrFail($id);
        abort_unless($equipment_type->isEditableBy(), 403);

        $this->form->setEquipmentType($equipment_type);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('equipment-type-deleted')]
    public function onEquipmentTypeDeleted(): void {}

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
            auth()->user()->can($this->form->isEditing() ? 'settings.equipment_types.edit' : 'settings.equipment_types.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        CreateOrUpdateEquipmentTypeAction::run(
            $this->form->equipment_type_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Tipo actualizado' : 'Tipo creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('equipment-type-saved');
    }

    public function render()
    {
        return view('livewire.admin.settings.equipment.types.index', [
            'config'     => EquipmentSettingsConfig::sectionOrFail('types'),
            'businesses' => $this->form->getActiveBusinesses(),
        ]);
    }
}
