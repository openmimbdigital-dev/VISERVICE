<?php

namespace App\Livewire\Admin\Workshop\Clients;

use App\Models\Client;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\DateColumn;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableClients extends LivewireDatatable
{
    public bool $exportable = true;
    public ?int $perPage = 25;

    public function builder(): Builder
    {
        $query = Client::query()->forAuthUser();

        if (auth()->user()->hasRole('superAdmin')) {
            return $query
                ->leftJoin('businesses', 'clients.business_id', '=', 'businesses.id')
                ->select('clients.*')
                ->orderBy('businesses.name')
                ->orderBy('clients.name');
        }

        return $query->orderBy('clients.name');
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
            Column::name('name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Column::callback(['document_type', 'document_number'], function ($type, $number) {
                return '<span class="font-mono text-xs">' . e($type) . ': ' . e($number ?: '—') . '</span>';
            })->label('Documento'),

            Column::name('phone')
                ->label('Teléfono')
                ->searchable(),

            Column::name('email')
                ->label('Email')
                ->searchable(),

            Column::callback(['status'], function ($status) {
                $label = $status ? 'Activo' : 'Inactivo';
                $class = $status
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';
                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            DateColumn::name('created_at')
                ->label('Registro')
                ->sortable(),

            Column::callback(['id'], function ($id) {
                return view('livewire.admin.workshop.clients.actions', ['id' => $id]);
            })->label('Acciones')->unsortable(),
        ]);
    }


    public function toggleStatus(int $id): void
    {
        $client = Client::findOrFail($id);
        $client->update(['status' => !$client->status]);
        $this->dispatch('swal', ['title' => 'Estado actualizado', 'icon' => 'success']);
    }

    public function deleteRecord(int $id): void
    {
        $client = Client::findOrFail($id);
        if ($client->workOrders()->exists() || $client->quotations()->exists()) {
            $this->dispatch('swal', ['title' => 'No se puede eliminar: tiene OTs o cotizaciones asociadas', 'icon' => 'error']);
            return;
        }
        $client->delete();
        $this->dispatch('swal', ['title' => 'Cliente eliminado', 'icon' => 'warning']);
        $this->dispatch('client-deleted');
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
