<?php

namespace App\Livewire\Admin\Workshop\Quotations;

use App\Models\Quotation;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\DateColumn;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableQuotations extends LivewireDatatable
{
    public bool $exportable = true;
    public ?int $perPage = 25;

    public function builder(): Builder
    {
        return Quotation::where('quotations.business_id', auth()->user()->business_id)
            ->leftJoin('clients', 'quotations.client_id', '=', 'clients.id')
            ->leftJoin('vehicles', 'quotations.vehicle_id', '=', 'vehicles.id')
            ->select('quotations.*', 'clients.name as client_name', 'vehicles.plate as vehicle_plate')
            ->latest('quotations.created_at');
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

            Column::name('vehicle_plate')
                ->label('Placa')
                ->searchable(),

            Column::callback(['total'], function ($total) {
                return '<span class="tabular-nums font-semibold">' . col_money($total) . '</span>';
            })->label('Total')->sortable(),

            Column::callback(['valid_until'], function ($date) {
                return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '<span class="text-slate-400">—</span>';
            })->label('Válida hasta'),

            Column::callback(['status'], function ($status) {
                $map = [
                    'borrador'  => ['label' => 'Borrador',  'class' => 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20'],
                    'enviada'   => ['label' => 'Enviada',   'class' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20'],
                    'aceptada'  => ['label' => 'Aceptada',  'class' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'],
                    'rechazada' => ['label' => 'Rechazada', 'class' => 'bg-red-50 text-red-700 ring-1 ring-red-600/20'],
                    'vencida'   => ['label' => 'Vencida',   'class' => 'bg-orange-50 text-orange-700 ring-1 ring-orange-600/20'],
                ];
                $s = $map[$status] ?? ['label' => $status, 'class' => 'bg-slate-100 text-slate-600'];
                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $s['class'] . '">' . $s['label'] . '</span>';
            })->label('Estado')->filterable([
                'borrador' => 'Borrador', 'enviada' => 'Enviada',
                'aceptada' => 'Aceptada', 'rechazada' => 'Rechazada', 'vencida' => 'Vencida',
            ]),

            DateColumn::name('quotations.created_at')
                ->label('Fecha')
                ->sortable(),

            Column::callback(['id'], function ($id) {
                return view('livewire.admin.workshop.quotations.actions', ['id' => $id]);
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
