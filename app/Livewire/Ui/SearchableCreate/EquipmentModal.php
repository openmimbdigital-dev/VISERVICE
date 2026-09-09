<?php

namespace App\Livewire\Ui\SearchableCreate;

use App\Actions\Workshop\Equipment\CreateOrUpdateEquipmentAction;
use App\Livewire\Forms\Admin\Workshop\EquipmentForm;
use App\Models\Equipment;
use App\Models\EquipmentType;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Alta rápida de un equipo desde otra pantalla, sin salir del flujo.
 *
 * Pide solo lo que el equipo necesita para quedar utilizable —tipo, marca,
 * modelo, año, nombre y placa— y lo deja marcado como completo para que aparezca
 * de inmediato en el selector que lo pidió. Los atributos dinámicos del tipo de
 * equipo se llenan después desde el módulo de equipos.
 */
class EquipmentModal extends Component
{
    public EquipmentForm $form;

    #[Locked]
    public int $client_id;

    #[Locked]
    public ?string $client_name = null;

    public function mount(int $clientId, ?int $businessId = null, ?string $clientName = null): void
    {
        abort_unless(auth()->user()?->can('workshop.equipment.create'), 403);

        $this->client_id = $clientId;
        $this->client_name = $clientName;

        $this->form->client_id = $clientId;
        $this->form->business_id = $businessId ?? auth()->user()->business_id;
        $this->form->status = true;
    }

    /** Cambiar el tipo invalida marca y modelo: sus listas dependen de él. */
    public function updatedFormEquipmentTypeId(): void
    {
        $this->form->brand_id = null;
        $this->form->model_id = null;
    }

    public function updatedFormBrandId(): void
    {
        $this->form->model_id = null;
    }

    public function close(): void
    {
        $this->dispatch('searchable-create-closed', modelClass: Equipment::class);
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('workshop.equipment.create'), 403);

        // Solo los pasos que hacen falta para que el equipo quede completo; el
        // paso de atributos dinámicos se deja para el módulo de equipos.
        // Las reglas del objeto Form vienen sin prefijo y aquí se validan contra
        // el componente, así que hay que anteponerles «form.».
        $rules = array_merge(
            $this->form->rulesForStep(EquipmentForm::STEP_GENERAL),
            $this->form->rulesForStep(EquipmentForm::STEP_IDENTIFICATION),
        );

        $this->validate(
            $this->prefixKeys($rules),
            $this->prefixKeys($this->form->messages()),
        );

        $equipment = CreateOrUpdateEquipmentAction::run(
            (int) $this->form->resolvedBusinessId(),
            null,
            [
                'client_id'         => $this->client_id,
                'brand_id'          => (int) $this->form->brand_id,
                'model_id'          => (int) $this->form->model_id,
                'equipment_type_id' => (int) $this->form->equipment_type_id,
                'name'              => trim($this->form->name),
                'plate'             => strtoupper(trim($this->form->plate)),
                'year'              => (int) $this->form->year,
                'status'            => true,
                'notes'             => trim($this->form->notes) ?: null,
                'attribute_values'  => [],
                'step'              => EquipmentForm::TOTAL_STEPS,
                'final_step'        => EquipmentForm::TOTAL_STEPS,
            ],
        );

        $this->dispatch('swal', [
            'title' => 'Equipo creado',
            'text'  => $equipment->plate,
            'icon'  => 'success',
        ]);

        $this->dispatch('searchable-created', id: $equipment->id, modelClass: Equipment::class);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function prefixKeys(array $values): array
    {
        $prefixed = [];

        foreach ($values as $key => $value) {
            $prefixed['form.'.$key] = $value;
        }

        return $prefixed;
    }

    public function render()
    {
        return view('livewire.ui.searchable-create.equipment-modal', [
            'equipment_types' => EquipmentType::query()
                ->visibleToUser()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'brands' => $this->form->getBrands(),
            'models' => $this->form->getModels(),
        ]);
    }
}
