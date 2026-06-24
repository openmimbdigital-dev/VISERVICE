<?php

namespace App\Livewire\Admin\Workshop\WorkOrders;

use App\Models\WorkOrder;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\DateColumn;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableWorkOrders extends LivewireDatatable
{
    public bool $exportable = true;
    public ?int $perPage = 25;

    public function builder(): Builder
    {
        return WorkOrder::where('work_orders.business_id', auth()->user()->business_id)
            ->leftJoin('clients', 'work_orders.client_id', '=', 'clients.id')
            ->leftJoin('equipment', 'work_orders.equipment_id', '=', 'equipment.id')
            ->select('work_orders.*', 'clients.name as client_name', 'equipment.plate as equipment_plate')
            ->latest('work_orders.created_at');
    }

    public function getColumns(): Model|array
    {
        return [
            Column::name('reference')
                ->label('Referencia')
                ->searchable()
                ->sortable(),

            Column::name('client_name')
                ->label('Cliente')
                ->searchable()
                ->sortable(),

            Column::name('equipment_plate')
                ->label('Placa')
                ->searchable(),

            Column::callback(['total'], function ($total) {
                return '<span class="tabular-nums font-semibold">' . col_money($total) . '</span>';
            })->label('Total')->sortable(),

            Column::callback(['estimated_delivery'], function ($date) {
                return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '<span class="text-slate-400">—</span>';
            })->label('Entrega est.'),

            Column::callback(['status'], function ($status) {
                $map = [
                    'abierta'    => ['label' => 'Abierta',    'class' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20'],
                    'en_proceso' => ['label' => 'En proceso', 'class' => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20'],
                    'finalizada' => ['label' => 'Finalizada', 'class' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'],
                    'cancelada'  => ['label' => 'Cancelada',  'class' => 'bg-red-50 text-red-700 ring-1 ring-red-600/20'],
                ];
                $s = $map[$status] ?? ['label' => $status, 'class' => 'bg-slate-100 text-slate-600'];
                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $s['class'] . '">' . $s['label'] . '</span>';
            })->label('Estado')->filterable([
                'abierta' => 'Abierta', 'en_proceso' => 'En proceso',
                'finalizada' => 'Finalizada', 'cancelada' => 'Cancelada',
            ]),

            DateColumn::name('work_orders.created_at')
                ->label('Fecha')
                ->sortable(),

            Column::callback(['id'], function ($id) {
                return view('livewire.admin.workshop.work-orders.actions', ['id' => $id]);
            })->label('Acciones')->unsortable(),
        ];
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
