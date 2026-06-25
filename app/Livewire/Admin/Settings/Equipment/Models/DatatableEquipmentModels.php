<?php

namespace App\Livewire\Admin\Settings\Equipment\Models;

use App\Models\EquipmentModel;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class DatatableEquipmentModels extends LivewireDatatable
{
    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('equipment-model-saved')]
    public function onEquipmentModelSaved(): void {}

    public function builder(): Builder
    {
        return EquipmentModel::query()
            ->visibleToUser()
            ->leftJoin('brands', 'equipment_models.brand_id', '=', 'brands.id')
            ->leftJoin('businesses', 'equipment_models.business_id', '=', 'businesses.id')
            ->select('equipment_models.*')
            ->orderByDesc('equipment_models.created_at');
    }

    public function getColumns(): Model|array
    {
        return [
            Column::name('equipment_models.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Column::callback(['brands.name'], function ($brand_name) {
                return $brand_name
                    ? e($brand_name)
                    : '<span class="text-slate-400">—</span>';
            })->label('Marca'),

            Column::callback(['equipment_models.general'], function ($general) {
                if ($general) {
                    return '<span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>';
            })->label('General')->filterable([1 => 'Sí', 0 => 'No']),

            Column::callback(['businesses.name'], function ($business_name) {
                return $business_name
                    ? e($business_name)
                    : '<span class="text-slate-400">—</span>';
            })->label('Negocio'),

            Column::callback(['equipment_models.active'], function ($active) {
                $label = $active ? 'Activo' : 'Inactivo';
                $class = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(['equipment_models.id'], function ($id) {
                $equipment_model = EquipmentModel::find($id);

                return view('livewire.admin.settings.equipment.models.actions', [
                    'id'         => $id,
                    'can_edit'   => $equipment_model?->isEditableBy() ?? false,
                    'can_delete' => $equipment_model?->canDelete() ?? false,
                ]);
            })->label('Acciones')->unsortable(),
        ];
    }

    public function openEditEvent(int $id): void
    {
        $this->dispatch('open-equipment-model-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        $equipment_model = EquipmentModel::findOrFail($id);

        if (! $equipment_model->canDelete()) {
            $message = $equipment_model->hasDependencies()
                ? 'No se puede eliminar: tiene equipos asociados'
                : 'No tienes permiso para eliminar este modelo';

            $this->dispatch('swal', ['title' => $message, 'icon' => 'error']);

            return;
        }

        $equipment_model->delete();
        $this->dispatch('swal', ['title' => 'Modelo eliminado', 'icon' => 'warning']);
        $this->dispatch('equipment-model-deleted');
    }

    public function render()
    {
        $this->dispatch('refreshDynamic');

        if ($this->persistPerPage) {
            session()->put([$this->sessionStorageKey() . '_perpage' => $this->perPage]);
        }

        return view('datatables::datatable');
    }
}
