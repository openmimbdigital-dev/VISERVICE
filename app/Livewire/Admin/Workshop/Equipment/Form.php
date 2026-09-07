<?php

namespace App\Livewire\Admin\Workshop\Equipment;

use App\Actions\Workshop\Equipment\CreateOrUpdateEquipmentAction;
use App\Livewire\Forms\Admin\Workshop\EquipmentForm;
use App\Models\Business;
use App\Models\Equipment;
use App\Models\EquipmentType;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public EquipmentForm $form;

    public EquipmentType $equipment_type;

    public int $step = EquipmentForm::STEP_GENERAL;

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
            $this->step = $equipment->isComplete()
                ? EquipmentForm::STEP_GENERAL
                : max(EquipmentForm::STEP_GENERAL, min((int) $equipment->step, EquipmentForm::TOTAL_STEPS));

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

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        $this->advanceToStep(min($this->step + 1, EquipmentForm::TOTAL_STEPS));
    }

    public function previousStep(): void
    {
        $this->step = max($this->step - 1, EquipmentForm::STEP_GENERAL);
    }

    public function goToStep(int $step): void
    {
        if ($step < EquipmentForm::STEP_GENERAL || $step > EquipmentForm::TOTAL_STEPS) {
            return;
        }

        if ($step > $this->step) {
            for ($current = $this->step; $current < $step; $current++) {
                $this->form->validate($this->form->rulesForStep($current));
            }
        }

        $this->advanceToStep($step);
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

        $was_editing = $this->form->isEditing();

        try {
            CreateOrUpdateEquipmentAction::run(
                $business_id,
                $this->form->equipment_id,
                $this->form->validated()
            );
        } catch (ValidationException $exception) {
            $this->step = $this->form->firstStepWithErrors($exception->errors());

            throw $exception;
        }

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Equipo actualizado' : 'Equipo registrado',
            'icon'  => 'success',
        ]);

        $this->dispatch('equipment-saved');

        $this->redirectRoute(
            'admin.workshop.equipment.type',
            $this->equipment_type,
            navigate: true
        );
    }

    public function render()
    {
        $is_super_admin = auth()->user()->hasRole('superAdmin');
        $total_steps    = EquipmentForm::TOTAL_STEPS;
        $progress       = (int) round(($this->step / $total_steps) * 100);
        $radius         = 30;
        $circumference  = round(2 * M_PI * $radius, 2);

        return view('livewire.admin.workshop.equipment.form', [
            'is_editing'             => $this->form->isEditing(),
            'is_super_admin'         => $is_super_admin,
            'step'                   => $this->step,
            'total_steps'            => $total_steps,
            'progress'               => $progress,
            'progress_circumference' => $circumference,
            'progress_offset'        => round($circumference * (1 - $progress / 100), 2),
            'steps'                  => [
                EquipmentForm::STEP_GENERAL => [
                    'title'       => 'Información general',
                    'description' => 'Cliente, nombre y placa',
                ],
                EquipmentForm::STEP_IDENTIFICATION => [
                    'title'       => 'Identificación',
                    'description' => 'Marca, modelo y año',
                ],
                EquipmentForm::STEP_DETAILS => [
                    'title'       => 'Atributos',
                    'description' => 'Atributos del tipo, notas y estado',
                ],
            ],
            'businesses'      => $is_super_admin
                ? Business::where('status', true)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'clients'         => $this->form->getClients(),
            'brands'          => $this->form->getBrands(),
            'models'          => $this->form->getModels(),
            'attribute_links' => $this->form->getAttributeLinks(),
        ])->layoutData([
            'title' => ($this->form->isEditing() ? 'Editar' : 'Nuevo') . ' equipo — ' . $this->equipment_type->name,
        ]);
    }

    private function advanceToStep(int $step): void
    {
        if ($step > $this->step && ! $this->form->isFlowComplete()) {
            $was_new   = ! $this->form->isEditing();
            $equipment = $this->persistProgress($step);

            if ($was_new) {
                $this->redirectRoute(
                    'admin.workshop.equipment.form.edit',
                    [$this->equipment_type, $equipment],
                    navigate: true
                );

                return;
            }
        }

        $this->step = $step;
    }

    private function persistProgress(int $step): Equipment
    {
        $business_id = $this->form->resolvedBusinessId();
        abort_unless($business_id, 403);

        $equipment = CreateOrUpdateEquipmentAction::run(
            $business_id,
            $this->form->equipment_id,
            $this->form->payload($step)
        );

        $this->form->equipment_id   = $equipment->id;
        $this->form->persisted_step = $step;

        return $equipment;
    }

    private function validateCurrentStep(): void
    {
        $this->form->validate($this->form->rulesForStep($this->step));
    }
}
