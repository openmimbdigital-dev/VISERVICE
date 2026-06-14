<?php

namespace App\Livewire\Admin\Catalog\SpareParts;

use App\Models\SparePartCatalog;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableSpareParts extends LivewireDatatable
{
    public bool $exportable = true;
    public ?int $perPage = 25;

    public function builder(): Builder
    {
        return SparePartCatalog::where('business_id', auth()->user()->business_id)
            ->orderBy('name');
    }

    public function getColumns(): Model|array
    {
        return [
            Column::name('name')
                ->label('Repuesto')
                ->searchable()
                ->sortable(),

            Column::name('code')
                ->label('Código')
                ->searchable(),

            Column::name('brand')
                ->label('Marca')
                ->searchable()
                ->sortable(),

            Column::name('category')
                ->label('Categoría')
                ->searchable()
                ->sortable(),

            Column::callback(['unit_price'], function ($price) {
                return '<span class="tabular-nums font-semibold">' . col_money($price) . '</span>';
            })->label('Precio unit.')->sortable(),

            Column::callback(['stock', 'min_stock'], function ($stock, $minStock) {
                $low = (int)$stock <= (int)$minStock;
                $class = $low
                    ? 'bg-red-50 text-red-700 ring-1 ring-red-600/20'
                    : 'bg-slate-100 text-slate-700 ring-1 ring-slate-500/20';
                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold tabular-nums ' . $class . '">'
                    . (int)$stock . ' / min ' . (int)$minStock . '</span>';
            })->label('Stock'),

            Column::name('unit')
                ->label('Unidad'),

            Column::callback(['id'], function ($id) {
                return view('livewire.admin.catalog.spare-parts.actions', ['id' => $id]);
            })->label('Acciones')->unsortable(),
        ];
    }

    public function openEditEvent(int $id): void
    {
        $this->dispatch('open-spare-part-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        SparePartCatalog::findOrFail($id)->delete();
        $this->dispatch('swal', ['title' => 'Repuesto eliminado', 'icon' => 'warning']);
        $this->dispatch('spare-part-deleted');
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
