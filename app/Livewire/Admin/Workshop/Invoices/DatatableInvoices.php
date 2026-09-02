<?php

namespace App\Livewire\Admin\Workshop\Invoices;

use App\Models\WorkOrderInvoice;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\DateColumn;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableInvoices extends LivewireDatatable
{
    public bool $exportable = true;

    public ?int $perPage = 25;

    public function builder(): Builder
    {
        return WorkOrderInvoice::query()
            ->forAuthUser()
            ->select('work_order_invoices.*')
            ->orderByDesc('work_order_invoices.created_at');
    }

    public function getColumns(): Model|array
    {
        return [
            Column::name('work_order_invoices.reference')
                ->label('Referencia')
                ->searchable()
                ->sortable(),

            Column::name('work_order_invoices.work_order_id')
                ->label('ID OT')
                ->sortable(),

            Column::callback(['work_order_invoices.subtotal'], fn ($subtotal) => col_money($subtotal))
                ->label('Subtotal')
                ->sortable(),

            Column::callback(['work_order_invoices.tax_amount'], fn ($tax_amount) => col_money($tax_amount))
                ->label('Impuesto')
                ->sortable(),

            Column::callback(['work_order_invoices.total'], fn ($total) => col_money($total))
                ->label('Total')
                ->sortable(),

            Column::callback(['work_order_invoices.status'], function ($status) {
                $invoice = new WorkOrderInvoice(['status' => $status]);

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $invoice->status_badge_class . '">' . e($invoice->status_label) . '</span>';
            })->label('Estado')->filterable([
                'pendiente' => 'Pendiente',
                'pagada'    => 'Pagada',
                'vencida'   => 'Vencida',
                'anulada'   => 'Anulada',
            ]),

            DateColumn::name('work_order_invoices.due_date')
                ->label('Vencimiento')
                ->sortable(),

            DateColumn::name('work_order_invoices.created_at')
                ->label('Fecha')
                ->sortable(),

            Column::callback(['work_order_invoices.id'], function ($id) {
                return view('livewire.admin.workshop.invoices.actions', [
                    'id' => $id,
                ]);
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
