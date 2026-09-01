<?php

namespace App\Livewire\Admin\Settings\Equipment\Types;

use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Concerns\JoinsEquipmentUsageCount;
use App\Models\EquipmentType;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class DatatableEquipmentTypes extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;
    use JoinsEquipmentUsageCount;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('equipment-type-saved')]
    public function onEquipmentTypeSaved(): void {}

    public function builder(): Builder
    {
        $business_counts = DB::table('equipment_type_business')
            ->select('equipment_type_id', DB::raw('COUNT(*) as assigned_businesses_count'))
            ->groupBy('equipment_type_id');

        $query = EquipmentType::query()
            ->select('equipment_types.*');

        if (! auth()->user()->hasRole('superAdmin')) {
            $query->visibleToUser();
        }

        $this->joinEquipmentUsageCount($query, 'equipment_types', 'equipment_type_id');

        $query->leftJoinSub(
            $business_counts,
            'business_assignments',
            fn ($join) => $join->on('equipment_types.id', '=', 'business_assignments.equipment_type_id')
        );

        $query->addSelect('equipment_usage.equipment_count')
            ->addSelect('business_assignments.assigned_businesses_count')
            ->orderByDesc('equipment_types.created_at');

        return $query;
    }

    public function getColumns(): Model|array
    {
        return [
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

            Column::callback(['business_assignments.assigned_businesses_count'], function ($count) {
                $count = (int) ($count ?? 0);

                if ($count === 0) {
                    return '<span class="text-slate-500">Todos</span>';
                }

                return '<span class="text-slate-700">' . $count . ' negocio(s)</span>';
            })->label('Negocios'),

            Column::callback(['equipment_types.active'], function ($active) {
                $label = $active ? 'Activo' : 'Inactivo';
                $class = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(
                ['equipment_types.id', 'equipment_usage.equipment_count'],
                function ($id, $equipment_count) {
                    $equipment_count = (int) $equipment_count;
                    $can_edit        = auth()->user()->can('settings.equipment_types.edit');
                    $can_delete      = auth()->user()->can('settings.equipment_types.delete') && $equipment_count === 0;

                    return view('livewire.admin.settings.equipment.types.actions', [
                        'id'                  => $id,
                        'can_edit'            => $can_edit,
                        'can_delete'          => $can_delete,
                        'is_general_readonly' => false,
                    ]);
                }
            )->label('Acciones')->unsortable(),
        ];
    }

    public function openEditEvent(int $id): void
    {
        $this->dispatch('open-equipment-type-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('settings.equipment_types.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Estás seguro de querer eliminar este tipo de equipo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            abort_unless(auth()->user()->can('settings.equipment_types.delete'), 403);

            $equipment_type = EquipmentType::query()
                ->when(
                    ! auth()->user()->hasRole('superAdmin'),
                    fn ($query) => $query->visibleToUser()
                )
                ->findOrFail($this->delete_id);

            if (! $equipment_type->canDelete()) {
                $message = $equipment_type->hasDependencies()
                    ? 'No se puede eliminar: tiene equipos asociados.'
                    : 'No tienes permiso para eliminar este tipo.';

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
