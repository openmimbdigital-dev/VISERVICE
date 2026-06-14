<?php

namespace App\Livewire\Admin\Workshop\Vehicles;

use App\Models\Vehicle;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\DateColumn;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableVehicles extends LivewireDatatable
{
    public bool $exportable = true;
    public ?int $perPage = 25;

    public function builder(): Builder
    {
        return Vehicle::where('vehicles.business_id', auth()->user()->business_id)
            ->leftJoin('clients', 'vehicles.client_id', '=', 'clients.id')
            ->select('vehicles.*', 'clients.name as client_name')
            ->orderBy('vehicles.plate');
    }

    public function getColumns(): Model|array
    {
        return [
            Column::name('plate')
                ->label('Placa')
                ->searchable()
                ->sortable(),

            Column::callback(['brand', 'model', 'year'], function ($brand, $model, $year) {
                $parts = array_filter([$brand, $model, $year]);
                return $parts ? e(implode(' ', $parts)) : '<span class="text-slate-400">—</span>';
            })->label('Vehículo'),

            Column::name('client_name')
                ->label('Cliente')
                ->searchable()
                ->sortable(),

            Column::callback(['fuel_type'], function ($type) {
                $labels = ['gasolina' => 'Gasolina', 'diesel' => 'Diesel', 'gas' => 'Gas', 'electrico' => 'Eléctrico', 'hibrido' => 'Híbrido', 'otro' => 'Otro'];
                return e($labels[$type] ?? $type);
            })->label('Combustible'),

            Column::callback(['km_current'], function ($km) {
                return '<span class="tabular-nums">' . number_format($km) . ' km</span>';
            })->label('Km actual')->sortable(),

            Column::callback(['status'], function ($status) {
                $label = $status ? 'Activo' : 'Inactivo';
                $class = $status
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';
                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(['id'], function ($id) {
                return view('livewire.admin.workshop.vehicles.actions', ['id' => $id]);
            })->label('Acciones')->unsortable(),
        ];
    }

    public function openEditEvent(int $id): void
    {
        $this->dispatch('open-vehicle-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        $vehicle = Vehicle::findOrFail($id);
        if ($vehicle->workOrders()->exists()) {
            $this->dispatch('swal', ['title' => 'No se puede eliminar: tiene OTs asociadas', 'icon' => 'error']);
            return;
        }
        $vehicle->delete();
        $this->dispatch('swal', ['title' => 'Vehículo eliminado', 'icon' => 'warning']);
        $this->dispatch('vehicle-deleted');
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
