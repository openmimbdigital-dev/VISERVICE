<?php

namespace App\Livewire\Admin\Workshop\AdvancePayments;

use App\Models\Status;
use App\Models\WorkOrderPayment;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\DateColumn;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableAdvancePayments extends LivewireDatatable
{
    public bool $exportable = true;

    public ?int $perPage = 25;

    public function builder(): Builder
    {
        return WorkOrderPayment::query()
            ->forAuthUser()
            ->leftJoin('work_orders', 'work_order_payments.work_order_id', '=', 'work_orders.id')
            ->leftJoin('clients', 'work_orders.client_id', '=', 'clients.id')
            ->leftJoin('users as creators', 'work_order_payments.created_by', '=', 'creators.id')
            ->select('work_order_payments.*')
            ->orderByDesc('work_order_payments.paid_at')
            ->orderByDesc('work_order_payments.id');
    }

    public function getColumns(): Model|array
    {
        $status_labels = Status::optionsForModule('work_order_payments');

        return [
            Column::raw('work_orders.reference AS work_order_reference')
                ->label('OT')
                ->searchable()
                ->sortable(),

            Column::raw('clients.name AS client_name')
                ->label('Cliente')
                ->searchable()
                ->sortable(),

            Column::callback(['work_order_payments.amount'], function ($amount) {
                return '<span class="tabular-nums font-semibold text-amber-700">' . col_money($amount) . '</span>';
            })->label('Monto')->sortable(),

            Column::callback(['work_order_payments.percentage'], function ($percentage) {
                if ($percentage === null || $percentage === '') {
                    return '<span class="text-slate-400">—</span>';
                }

                $formatted = rtrim(rtrim(number_format((float) $percentage, 2, '.', ''), '0'), '.');

                return '<span class="tabular-nums">' . e($formatted) . '%</span>';
            })->label('%')->sortable(),

            Column::callback(['work_order_payments.status'], function ($status) use ($status_labels) {
                $badge = match ((string) $status) {
                    'confirmed' => 'bg-emerald-100 text-emerald-800',
                    'voided' => 'bg-rose-100 text-rose-800',
                    default => 'bg-slate-100 text-slate-700',
                };
                $text = $status_labels[$status] ?? (string) $status;

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $badge . '">' . e($text) . '</span>';
            })->label('Estado')->filterable($status_labels),

            DateColumn::name('work_order_payments.paid_at')
                ->label('Fecha pago')
                ->sortable(),

            Column::raw("TRIM(CONCAT(COALESCE(creators.first_name, ''), ' ', COALESCE(creators.last_name, ''))) AS created_by_name")
                ->label('Registrado por')
                ->searchable()
                ->hideable(),

            Column::callback(['work_order_payments.notes'], function ($notes) {
                if (! $notes) {
                    return '<span class="text-slate-400">—</span>';
                }

                $short = \Illuminate\Support\Str::limit((string) $notes, 40);

                return '<span class="text-slate-600" title="' . e((string) $notes) . '">' . e($short) . '</span>';
            })->label('Notas')->hideable(),
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
