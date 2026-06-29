<?php

namespace App\Livewire\Admin\Settings\Equipment\Attributes;

use App\Actions\Settings\Equipment\DeleteAttributeAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Attribute;
use App\Models\AttributeEquipmentType;
use App\Models\EquipmentType;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Atributo')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public Attribute $attribute;

    /** @var Collection<int, EquipmentType> */
    public Collection $equipment_types;

    public function mount(Attribute $attribute): void
    {
        abort_unless(auth()->user()->can('settings.attributes.view'), 403);

        $visible = Attribute::query()
            ->forAuthUser()
            ->whereKey($attribute->id)
            ->exists();

        abort_unless($visible, 404);

        $this->attribute = $attribute->load('businesses');

        $type_ids = AttributeEquipmentType::query()
            ->where('attribute_id', $attribute->id)
            ->where('model_type', EquipmentType::class)
            ->pluck('model_id')
            ->unique();

        $this->equipment_types = $type_ids->isEmpty()
            ? collect()
            : EquipmentType::query()
                ->whereIn('id', $type_ids)
                ->orderBy('name')
                ->get(['id', 'name']);
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()->can('settings.attributes.delete'), 403);
        abort_unless($this->attribute->isEditableBy(), 403);

        $this->askDeleteConfirmation($this->attribute->id, '¿Estás seguro de querer eliminar este atributo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteAttributeAction::run($this->delete_id);

            $this->alertDeleteSuccess('Atributo eliminado correctamente.');

            $this->redirectRoute('admin.settings.equipment.attributes.index', navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el atributo.');
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.equipment.attributes.show', [
            'is_super_admin'      => auth()->user()->hasRole('superAdmin'),
            'can_edit'            => auth()->user()->can('settings.attributes.edit') && $this->attribute->isEditableBy(),
            'can_delete'          => auth()->user()->can('settings.attributes.delete') && $this->attribute->isEditableBy(),
            'is_general_readonly' => $this->attribute->isGeneralReadonly(),
        ]);
    }
}
