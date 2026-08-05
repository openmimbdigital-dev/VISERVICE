<?php

namespace App\Livewire\Admin\Workshop\WorkOrders;

use App\Actions\Workshop\DeleteWorkOrderAction;
use App\Enums\WorkOrderStatus;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\WorkOrder;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\DateColumn;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableWorkOrders extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    public function builder(): Builder
    {
        return WorkOrder::query()
            ->forAuthUser()
            ->leftJoin('clients', 'work_orders.client_id', '=', 'clients.id')
            ->leftJoin('equipment', 'work_orders.equipment_id', '=', 'equipment.id')
            ->select('work_orders.*')
            ->orderByDesc('work_orders.created_at');
    }

    public function getColumns(): Model|array
    {
        return [
            Column::name('work_orders.reference')
                ->label('Referencia')
                ->searchable()
                ->sortable(),

            Column::raw('clients.name AS client_name')
                ->label('Cliente')
                ->searchable()
                ->sortable(),

            Column::raw('equipment.plate AS equipment_plate')
                ->label('Placa')
                ->searchable(),

            Column::callback(['work_orders.total'], function ($total) {
                return '<span class="tabular-nums font-semibold">' . col_money($total) . '</span>';
            })->label('Total')->sortable(),

            Column::callback(['work_orders.estimated_delivery'], function ($date) {
                return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '<span class="text-slate-400">—</span>';
            })->label('Entrega est.'),

            Column::callback(['work_orders.status'], function ($status) {
                $enum = WorkOrderStatus::tryFrom((string) $status);

                if (! $enum) {
                    return e((string) $status);
                }

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $enum->badgeClass() . '">' . e($enum->label()) . '</span>';
            })->label('Estado')->filterable(WorkOrderStatus::options()),

            DateColumn::name('work_orders.created_at')
                ->label('Fecha')
                ->sortable(),

            Column::callback(['work_orders.id', 'work_orders.status'], function ($id, $status) {
                return view('livewire.admin.workshop.work-orders.actions', [
                    'id'     => $id,
                    'status' => $status,
                ]);
            })->label('Acciones')->unsortable(),
        ];
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('workshop.work-orders.delete'), 403);
        $this->askDeleteConfirmation($id, '¿Eliminar esta orden de trabajo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteWorkOrderAction::run($this->delete_id);
            $this->alertDeleteSuccess('Orden de trabajo eliminada correctamente.');
            $this->dispatch('work-order-deleted');
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar la OT.');
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
