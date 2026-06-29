<?php

namespace App\Livewire\Admin\Settings\Equipment\Types;

use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Concerns\JoinsEquipmentUsageCount;
use App\Livewire\Concerns\ResolvesCatalogDatatableRowPermissions;
use App\Models\EquipmentType;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class DatatableEquipmentTypes extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;
    use JoinsEquipmentUsageCount;
    use ResolvesCatalogDatatableRowPermissions;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('equipment-type-saved')]
    public function onEquipmentTypeSaved(): void {}

    public function builder(): Builder
    {
        $query = EquipmentType::query()
            ->visibleToUser()
            ->select('equipment_types.*');

        $this->joinEquipmentUsageCount($query, 'equipment_types', 'equipment_type_id');

        $query->addSelect('equipment_usage.equipment_count')
            ->orderByDesc('equipment_types.created_at');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'equipment_types.business_id', '=', 'businesses.id');
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('equipment_types.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Column::callback(['equipment_types.general'], function ($general) {
                if ($general) {
                    return '<span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>';
            })->label('General')->filterable([1 => 'Sí', 0 => 'No']),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::callback(['businesses.name'], function ($business_name) {
                return $business_name
                    ? e($business_name)
                    : '<span class="text-slate-400">—</span>';
            })->label('Negocio');
        }

        return array_merge($columns, [
            Column::callback(['equipment_types.active'], function ($active) {
                $label = $active ? 'Activo' : 'Inactivo';
                $class = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(
                ['equipment_types.id', 'equipment_types.general', 'equipment_types.business_id', 'equipment_usage.equipment_count'],
                function ($id, $general, $business_id, $equipment_count) {
                    $permissions = $this->catalogRowPermissions((bool) $general, $business_id, (int) $equipment_count);

                    return view('livewire.admin.settings.equipment.types.actions', [
                        'id'                  => $id,
                        'can_edit'            => $permissions['can_edit'],
                        'can_delete'          => $permissions['can_delete'],
                        'is_general_readonly' => $permissions['is_general_readonly'],
                    ]);
                }
            )->label('Acciones')->unsortable(),
        ]);
    }

    public function openEditEvent(int $id): void
    {
        $this->dispatch('open-equipment-type-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('settings.edit'), 403);

        $this->askDeleteConfirmation($id, '¿Estás seguro de querer eliminar este tipo de equipo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            abort_unless(auth()->user()->can('settings.edit'), 403);

            $equipment_type = EquipmentType::findOrFail($this->delete_id);

            if (! $equipment_type->canDelete()) {
                $message = $equipment_type->isGeneralReadonly()
                    ? 'No se puede eliminar: es un tipo general del sistema.'
                    : ($equipment_type->hasDependencies()
                        ? 'No se puede eliminar: tiene equipos asociados.'
                        : 'No tienes permiso para eliminar este tipo.');

                $this->alertDeleteWarning($message);

                return;
            }

            $equipment_type->delete();

            $this->alertDeleteSuccess('Tipo eliminado correctamente.');
            $this->dispatch('equipment-type-deleted');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el tipo.');
        }
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
