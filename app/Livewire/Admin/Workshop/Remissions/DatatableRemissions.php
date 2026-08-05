<?php

namespace App\Livewire\Admin\Workshop\Remissions;

use App\Actions\Workshop\DeleteRemissionAction;
use App\Enums\WorkOrderStatus;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Remission;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\DateColumn;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableRemissions extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    public function builder(): Builder
    {
        return Remission::query()
            ->forAuthUser()
            ->leftJoin('clients', 'remissions.client_id', '=', 'clients.id')
            ->leftJoin('work_orders', 'remissions.work_order_id', '=', 'work_orders.id')
            ->leftJoin('equipment', 'remissions.equipment_id', '=', 'equipment.id')
            ->select('remissions.*')
            ->orderByDesc('remissions.created_at');
    }

    public function getColumns(): Model|array
    {
        return [
            Column::name('remissions.reference')
                ->label('Referencia')
                ->searchable()
                ->sortable(),

            Column::raw('work_orders.reference AS work_order_reference')
                ->label('OT')
                ->searchable(),

            Column::raw('clients.name AS client_name')
                ->label('Cliente')
                ->searchable()
                ->sortable(),

            Column::raw('equipment.plate AS equipment_plate')
                ->label('Placa')
                ->searchable(),

            Column::callback(['remissions.type'], function ($type) {
                $map = [
                    'entrega'    => 'Entrega',
                    'devolucion' => 'Devolución',
                    'traslado'   => 'Traslado',
                ];

                return $map[$type] ?? $type;
            })->label('Tipo')->filterable([
                'entrega' => 'Entrega',
                'devolucion' => 'Devolución',
                'traslado' => 'Traslado',
            ]),

            Column::callback(['remissions.status'], function ($status) {
                $enum = WorkOrderStatus::tryFrom((string) $status);

                if (! $enum) {
                    return e((string) $status);
                }

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $enum->badgeClass() . '">' . e($enum->label()) . '</span>';
            })->label('Estado')->filterable(WorkOrderStatus::options()),

            Column::name('remissions.total_items')
                ->label('Ítems')
                ->sortable(),

            DateColumn::name('remissions.created_at')
                ->label('Fecha')
                ->sortable(),

            Column::callback(['remissions.id', 'remissions.status'], function ($id, $status) {
                return view('livewire.admin.workshop.remissions.actions', [
                    'id'     => $id,
                    'status' => $status,
                ]);
            })->label('Acciones')->unsortable(),
        ];
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('workshop.remissions.delete'), 403);
        $this->askDeleteConfirmation($id, '¿Eliminar esta remisión?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteRemissionAction::run($this->delete_id);
            $this->alertDeleteSuccess('Remisión eliminada correctamente.');
            $this->dispatch('remission-deleted');
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar la remisión.');
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
