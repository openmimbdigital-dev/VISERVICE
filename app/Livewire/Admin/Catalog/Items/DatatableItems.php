<?php

namespace App\Livewire\Admin\Catalog\Items;

use App\Actions\Catalog\DeleteItemAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Item;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class DatatableItems extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('item-deleted')]
    #[On('item-saved')]
    public function onItemChanged(): void {}

    public function builder(): Builder
    {
        $query = Item::query()
            ->forAuthUser()
            ->select('items.*')
            ->leftJoin('item_types', function ($join) {
                $join->on('items.item_type_id', '=', 'item_types.id')
                    ->whereNull('item_types.deleted_at');
            })
            ->leftJoin('item_categories', function ($join) {
                $join->on('items.item_category_id', '=', 'item_categories.id')
                    ->whereNull('item_categories.deleted_at');
            })
            ->addSelect('item_types.name as item_type_name')
            ->addSelect('item_categories.name as item_category_name')
            ->orderByDesc('items.created_at');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'items.business_id', '=', 'businesses.id');
        }

        return $query;
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
            Column::name('items.code')
                ->label('Código')
                ->searchable()
                ->sortable(),

            Column::name('items.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Column::raw('item_types.name AS item_type_name')
                ->label('Tipo')
                ->sortable()
                ->searchable(),

            Column::raw('item_categories.name AS item_category_name')
                ->label('Categoría')
                ->sortable()
                ->searchable(),

            Column::callback(['items.sale_price'], function ($sale_price) {
                return '<span class="tabular-nums text-sm text-slate-700">$ ' . number_format((float) $sale_price, 2, ',', '.') . '</span>';
            })->label('Precio venta')->sortable(),

            Column::callback(['items.track_inventory'], function ($track_inventory) {
                if ($track_inventory) {
                    return '<span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-700 ring-1 ring-sky-600/20">Sí</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>';
            })->label('Inventario')->filterable([1 => 'Sí', 0 => 'No']),

            Column::callback(['items.status'], function ($status) {
                $label = $status ? 'Activo' : 'Inactivo';
                $class = $status
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(['items.id', 'items.business_id', 'items.status'], function ($id, $business_id, $status) {
                $user = auth()->user();
                $can_edit = $user->can('catalog.items.edit')
                    && ($user->hasRole('superAdmin') || $user->belongsToBusiness((int) $business_id));

                return view('livewire.admin.catalog.items.actions', [
                    'id'       => $id,
                    'can_edit' => $can_edit,
                    'status'   => $status,
                ]);
            })->label('Acciones')->unsortable(),
        ]);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('catalog.items.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Estás seguro de querer eliminar este producto?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteItemAction::run($this->delete_id);

            $this->alertDeleteSuccess('Producto eliminado correctamente.');
            $this->dispatch('item-deleted');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el producto.');
        }
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
