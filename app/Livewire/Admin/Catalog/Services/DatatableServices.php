<?php

namespace App\Livewire\Admin\Catalog\Services;

use App\Models\ServiceCatalog;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableServices extends LivewireDatatable
{
    public bool $exportable = true;
    public ?int $perPage = 25;

    public function builder(): Builder
    {
        return ServiceCatalog::where('business_id', auth()->user()->business_id)
            ->orderBy('name');
    }

    public function getColumns(): Model|array
    {
        return [
            Column::name('name')
                ->label('Servicio')
                ->searchable()
                ->sortable(),

            Column::name('code')
                ->label('Código')
                ->searchable(),

            Column::name('category')
                ->label('Categoría')
                ->searchable()
                ->sortable(),

            Column::callback(['default_price'], function ($price) {
                return '<span class="tabular-nums font-semibold">' . col_money($price) . '</span>';
            })->label('Precio base')->sortable(),

            Column::callback(['duration_minutes'], function ($min) {
                if (!$min) return '<span class="text-slate-400">—</span>';
                $h = intdiv((int)$min, 60);
                $m = (int)$min % 60;
                return $h > 0 ? "{$h}h " . ($m > 0 ? "{$m}min" : '') : "{$m}min";
            })->label('Duración'),

            Column::callback(['is_active'], function ($active) {
                $label = $active ? 'Activo' : 'Inactivo';
                $class = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';
                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(['id'], function ($id) {
                return view('livewire.admin.catalog.services.actions', ['id' => $id]);
            })->label('Acciones')->unsortable(),
        ];
    }

    public function openEditEvent(int $id): void
    {
        $this->dispatch('open-service-edit', id: $id);
    }

    public function toggleActive(int $id): void
    {
        $s = ServiceCatalog::findOrFail($id);
        $s->update(['is_active' => !$s->is_active]);
        $this->dispatch('swal', ['title' => 'Estado actualizado', 'icon' => 'success']);
    }

    public function deleteRecord(int $id): void
    {
        ServiceCatalog::findOrFail($id)->delete();
        $this->dispatch('swal', ['title' => 'Servicio eliminado', 'icon' => 'warning']);
        $this->dispatch('service-deleted');
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
