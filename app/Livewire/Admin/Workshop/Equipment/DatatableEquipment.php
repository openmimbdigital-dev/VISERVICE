<?php

namespace App\Livewire\Admin\Workshop\Equipment;

use App\Actions\Workshop\Equipment\DeleteEquipmentAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Equipment;
use App\Models\EquipmentType;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class DatatableEquipment extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    public ?int $equipment_type_id = null;

    #[On('equipment-saved')]
    public function onEquipmentSaved(): void {}

    #[On('equipment-deleted')]
    public function onEquipmentDeleted(): void {}

    public function builder(): Builder
    {
        $user = auth()->user();

        $work_orders_count = DB::table('work_orders')
            ->select('equipment_id', DB::raw('COUNT(*) as work_orders_count'))
            ->whereNull('deleted_at');

        $quotations_count = DB::table('quotations')
            ->select('equipment_id', DB::raw('COUNT(*) as quotations_count'))
            ->whereNull('deleted_at');

        if ($user && ! $user->hasRole('superAdmin')) {
            $business_ids = $user->businessIds();

            if ($business_ids === []) {
                $work_orders_count->whereRaw('0 = 1');
                $quotations_count->whereRaw('0 = 1');
            } else {
                $work_orders_count->whereIn('business_id', $business_ids);
                $quotations_count->whereIn('business_id', $business_ids);
            }
        }

        $work_orders_count->groupBy('equipment_id');
        $quotations_count->groupBy('equipment_id');

        $query = Equipment::query()
            ->forAuthUser()
            ->leftJoin('clients', 'equipment.client_id', '=', 'clients.id')
            ->leftJoinSub(
                $work_orders_count,
                'equipment_work_orders',
                fn ($join) => $join->on('equipment.id', '=', 'equipment_work_orders.equipment_id')
            )
            ->leftJoinSub(
                $quotations_count,
                'equipment_quotations',
                fn ($join) => $join->on('equipment.id', '=', 'equipment_quotations.equipment_id')
            )
            ->select('equipment.*', 'clients.name as client_name')
            ->addSelect('equipment_work_orders.work_orders_count')
            ->addSelect('equipment_quotations.quotations_count');

        if ($this->equipment_type_id) {
            $query->where('equipment.equipment_type_id', $this->equipment_type_id);
        }

        if (auth()->user()->hasRole('superAdmin')) {
            return $query
                ->leftJoin('businesses', 'equipment.business_id', '=', 'businesses.id')
                ->addSelect('businesses.name as business_name')
                ->orderBy('businesses.name')
                ->orderBy('equipment.plate');
        }

        return $query->orderBy('equipment.plate');
    }

    public function getColumns(): Model|array
    {
        $columns = [];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::raw('businesses.name AS business_name')
                ->label('Comercio')
                ->sortable()
                ->searchable();
        }

        return array_merge($columns, [
            Column::name('equipment.id')
                ->label('ID')
                ->sortable()
                ->searchable(),

            Column::name('plate')
                ->label('Placa')
                ->searchable()
                ->sortable(),

            Column::callback(['equipment.brand_name', 'equipment.model_name', 'equipment.year'], function ($brand_name, $model_name, $year) {
                $parts = array_filter([$brand_name, $model_name, $year]);

                return $parts ? e(implode(' ', $parts)) : '<span class="text-slate-400">—</span>';
            })->label('Equipo'),

            Column::name('client_name')
                ->label('Cliente')
                ->searchable()
                ->sortable(),

            Column::callback(['status'], function ($status) {
                $label = $status ? 'Activo' : 'Inactivo';
                $class = $status
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(
                ['equipment.id', 'equipment_work_orders.work_orders_count', 'equipment_quotations.quotations_count'],
                function ($id, $work_orders_count, $quotations_count) {
                    $work_orders_count = (int) $work_orders_count;
                    $quotations_count  = (int) $quotations_count;
                    $has_dependencies  = $work_orders_count > 0 || $quotations_count > 0;
                    $can_delete        = auth()->user()->can('workshop.equipment.delete') && ! $has_dependencies;

                    $delete_block_reason = match (true) {
                        $work_orders_count > 0 => 'Tiene órdenes de trabajo asociadas.',
                        $quotations_count > 0  => 'Tiene cotizaciones asociadas.',
                        default                  => null,
                    };

                    return view('livewire.admin.workshop.equipment.actions', [
                        'id'                  => $id,
                        'equipment_type_id'   => $this->equipment_type_id,
                        'can_delete'          => $can_delete,
                        'delete_block_reason' => $delete_block_reason,
                    ]);
                }
            )->label('Acciones')->unsortable(),
        ]);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('workshop.equipment.delete'), 403);

        $equipment = $this->findAuthorized($id);

        if (! $equipment->canDelete()) {
            $this->dispatch('swal', [
                'title' => $equipment->dependencyBlockReason() ?? 'No se puede eliminar el equipo.',
                'icon'  => 'error',
            ]);

            return;
        }

        $this->askDeleteConfirmation($id, '¿Estás seguro de querer eliminar este equipo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            abort_unless(auth()->user()->can('workshop.equipment.delete'), 403);

            $equipment = $this->findAuthorized($this->delete_id);

            if (! $equipment->canDelete()) {
                $this->alertDeleteWarning(
                    $equipment->dependencyBlockReason() ?? 'No se puede eliminar el equipo.'
                );

                return;
            }

            $equipment_type = EquipmentType::query()->findOrFail($this->equipment_type_id);

            DeleteEquipmentAction::run($equipment, $equipment_type);

            $this->alertDeleteSuccess('Equipo eliminado correctamente.');
            $this->dispatch('equipment-deleted');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el equipo.');
        }
    }

    private function findAuthorized(int $id): Equipment
    {
        return Equipment::query()->forAuthUser()->where('equipment.id', $id)->firstOrFail();
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
