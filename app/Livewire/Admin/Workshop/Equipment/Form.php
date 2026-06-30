<?php

namespace App\Livewire\Admin\Workshop\Equipment;

use App\Actions\Workshop\Equipment\CreateOrUpdateEquipmentAction;
use App\Livewire\Forms\Admin\Workshop\EquipmentForm;
use App\Models\Business;
use App\Models\Equipment;
use App\Models\EquipmentType;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public EquipmentForm $form;

    public EquipmentType $equipment_type;

    public function mount(EquipmentType $equipmentType, ?Equipment $equipment = null): void
    {
        abort_unless($equipmentType->isAccessibleToUser(), 404);

        if (! auth()->user()->hasRole('superAdmin') && ! $equipmentType->active) {
            abort(404);
        }

        $this->equipment_type = $equipmentType;

        if ($equipment) {
            abort_unless(auth()->user()->can('workshop.equipment.edit'), 403);

            abort_unless(
                (int) $equipment->equipment_type_id === (int) $equipmentType->id,
                404
            );

            abort_unless(
                Equipment::query()->forAuthUser()->whereKey($equipment->id)->exists(),
                403
            );

            $this->form->setEquipment($equipment);

            return;
        }

        abort_unless(auth()->user()->can('workshop.equipment.create'), 403);

        $this->form->setEquipmentType($equipmentType);

        if (! auth()->user()->hasRole('superAdmin')) {
            $this->form->business_id = auth()->user()->business_id;
        }

        $this->form->hydrateAttributeDefaults();
    }

    public function updated($property): void
    {
        if ($property === 'form.business_id') {
            $this->form->client_id         = null;
            $this->form->brand_id          = null;
            $this->form->model_id            = null;
            $this->form->attribute_values    = [];
            $this->form->hydrateAttributeDefaults();
        }

        if ($property === 'form.brand_id') {
            $this->form->model_id = null;
        }
    }

    public function save(): void
    {
        abort_unless(
            $this->form->isEditing()
                ? auth()->user()->can('workshop.equipment.edit')
                : auth()->user()->can('workshop.equipment.create'),
            403
        );

        $business_id = $this->form->resolvedBusinessId();
        abort_unless($business_id, 403);

        CreateOrUpdateEquipmentAction::run(
            $business_id,
            $this->form->equipment_id,
            $this->form->validated()
        );

        $this->dispatch('swal', [
            'title' => $this->form->isEditing() ? 'Equipo actualizado' : 'Equipo registrado',
            'icon'  => 'success',
        ]);

        $this->redirectRoute(
            'admin.workshop.equipment.type',
            $this->equipment_type,
            navigate: true
        );
    }

    public function render()
    {
        $is_super_admin = auth()->user()->hasRole('superAdmin');

        return view('livewire.admin.workshop.equipment.form', [
            'is_editing'     => $this->form->isEditing(),
            'is_super_admin' => $is_super_admin,
            'businesses'     => $is_super_admin
                ? Business::where('status', true)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'clients'        => $this->form->getClients(),
            'brands'           => $this->form->getBrands(),
            'models'           => $this->form->getModels(),
            'attribute_links'  => $this->form->getAttributeLinks(),
        ])->layoutData([
            'title' => ($this->form->isEditing() ? 'Editar' : 'Nuevo') . ' equipo — ' . $this->equipment_type->name,
        ]);
    }
}
