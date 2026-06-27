<?php

namespace App\Livewire\Admin\Workshop\Equipment;

use App\Models\Equipment;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableEquipment extends LivewireDatatable
{
    public bool $exportable = true;
    public ?int $perPage = 25;

    public function builder(): Builder
    {
        $query = Equipment::query()
            ->forAuthUser()
            ->leftJoin('clients', 'equipment.client_id', '=', 'clients.id')
            ->select('equipment.*', 'clients.name as client_name');

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
            Column::name('plate')
                ->label('Placa')
                ->searchable()
                ->sortable(),

            Column::callback(['brand', 'model', 'year'], function ($brand, $model, $year) {
                $parts = array_filter([$brand, $model, $year]);
                return $parts ? e(implode(' ', $parts)) : '<span class="text-slate-400">—</span>';
            })->label('Equipo'),

            Column::name('client_name')
                ->label('Cliente')
                ->searchable()
                ->sortable(),

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
                return view('livewire.admin.workshop.equipment.actions', ['id' => $id]);
            })->label('Acciones')->unsortable(),
        ]);
    }

    public function openEditEvent(int $id): void
    {
        $this->dispatch('open-equipment-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('workshop.equipment.delete'), 403);

        $equipment = $this->findAuthorized($id);

        if ($equipment->workOrders()->exists()) {
            $this->dispatch('swal', ['title' => 'No se puede eliminar: tiene OTs asociadas', 'icon' => 'error']);
            return;
        }

        $equipment->delete();
        $this->dispatch('swal', ['title' => 'Equipo eliminado', 'icon' => 'warning']);
        $this->dispatch('equipment-deleted');
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
