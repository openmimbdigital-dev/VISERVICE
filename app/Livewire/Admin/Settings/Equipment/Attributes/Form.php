<?php

namespace App\Livewire\Admin\Settings\Equipment\Attributes;

use App\Actions\Settings\Equipment\CreateOrUpdateAttributeAction;
use App\Livewire\Admin\Settings\Equipment\EquipmentSettingsConfig;
use App\Livewire\Forms\Admin\Settings\Equipment\AttributeForm;
use App\Models\Attribute;
use App\Models\Business;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    use LivewireAlert;

    public AttributeForm $form;

    public function mount(?Attribute $attribute = null): void
    {
        if ($attribute) {
            abort_unless(auth()->user()->can('settings.attributes.edit'), 403);
            abort_unless($attribute->isAccessibleBy(), 403);
            abort_unless($attribute->isEditableBy(), 403);

            $this->form->setAttribute($attribute);
        } else {
            abort_unless(auth()->user()->can('settings.attributes.create'), 403);
            $this->form->reset();
        }
    }

    public function updated($property): void
    {
        if ($property === 'form.general' && $this->form->general) {
            $this->form->business_ids = [];
            $this->form->equipment_types = [];
        }

        if ($property === 'form.business_ids') {
            $visible_ids = $this->form->getEquipmentTypes()->pluck('id')->all();
            $this->form->equipment_types = array_values(
                array_intersect($this->form->equipment_types, $visible_ids)
            );
        }

        if ($property === 'form.type') {
            $this->form->options     = [];
            $this->form->min_value   = null;
            $this->form->max_value   = null;
            $this->form->default_color = '#6366f1';
        }
    }

    public function save()
    {
        try {
            $was_editing = $this->form->isEditing();

            CreateOrUpdateAttributeAction::run(
                $this->form->attribute_id,
                $this->form->validated()
            );

            $this->alert('success', $was_editing ? 'Atributo actualizado correctamente.' : 'Atributo creado correctamente.', [
                'position' => 'top-end',
                'timer'    => 3000,
                'toast'    => true,
            ]);

            $this->redirectRoute('admin.settings.equipment.attributes.index', navigate: true);
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

    public function addOption(): void
    {
        $this->form->addOption();
    }

    public function removeOption(int $index): void
    {
        $this->form->removeOption($index);
    }

    public function render()
    {
        $is_super_admin = auth()->user()->hasRole('superAdmin');
        $config         = EquipmentSettingsConfig::sectionOrFail('attributes');

        return view('livewire.admin.settings.equipment.attributes.form', [
            'config'          => $config,
            'is_super_admin'  => $is_super_admin,
            'businesses'      => $is_super_admin
                ? Business::where('status', true)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'equipment_types' => $this->form->getEquipmentTypes(),
        ])->layoutData([
            'title' => $this->form->isEditing() ? 'Configuración — Editar atributo' : 'Configuración — Nuevo atributo',
        ]);
    }
}
