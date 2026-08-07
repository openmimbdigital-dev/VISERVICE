<?php

namespace App\Livewire\Admin\Workshop\AdvancePayments;

use App\Models\WorkOrder;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\DateColumn;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DatatableAdvancePayments extends LivewireDatatable
{
    public bool $exportable = true;

    public ?int $perPage = 25;

    public function builder(): Builder
    {
        $paid_subquery = DB::table('work_order_payments')
            ->select('work_order_id', DB::raw('SUM(amount) as paid_sum'))
            ->whereNull('deleted_at')
            ->where('status', 'confirmed')
            ->groupBy('work_order_id');

        return WorkOrder::query()
            ->forAuthUser()
            ->where('work_orders.advance_amount', '>', 0)
            ->leftJoin('clients', 'work_orders.client_id', '=', 'clients.id')
            ->leftJoinSub($paid_subquery, 'advance_paid', 'advance_paid.work_order_id', '=', 'work_orders.id')
            ->select('work_orders.*')
            ->orderByDesc('work_orders.created_at');
    }

    public function getColumns(): Model|array
    {
        return [
            Column::name('work_orders.reference')
                ->label('OT')
                ->searchable()
                ->sortable(),

            Column::raw('clients.name AS client_name')
                ->label('Cliente')
                ->searchable()
                ->sortable(),

            Column::callback(['work_orders.advance_amount'], function ($amount) {
                return '<span class="tabular-nums font-semibold text-amber-700">' . col_money($amount) . '</span>';
            })->label('Acordado')->sortable(),

            Column::callback(['work_orders.advance_amount', 'advance_paid.paid_sum'], function ($agreed, $paid) {
                $remaining = max(0, round((float) $agreed - (float) ($paid ?? 0), 2));

                return '<span class="tabular-nums font-semibold text-slate-800">' . col_money($remaining) . '</span>';
            })->label('Pendiente'),

            Column::callback(['work_orders.advance_percentage'], function ($percentage) {
                $formatted = rtrim(rtrim(number_format((float) $percentage, 2, '.', ''), '0'), '.');

                return '<span class="tabular-nums">' . e($formatted) . '%</span>';
            })->label('%')->sortable(),

            DateColumn::name('work_orders.created_at')
                ->label('Creada')
                ->sortable(),

            Column::callback(['work_orders.id'], function ($id) {
                return view('livewire.admin.workshop.advance-payments.actions', [
                    'id' => $id,
                ])->render();
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
