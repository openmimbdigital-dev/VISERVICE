<?php

namespace App\Livewire\Admin\Catalog\Products;

use App\Actions\Catalog\DeleteProductAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Product;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class DatatableProducts extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('product-deleted')]
    #[On('product-saved')]
    public function onProductChanged(): void {}

    public function builder(): Builder
    {
        $query = Product::query()
            ->forAuthUser()
            ->select('products.*')
            ->leftJoin('product_types', function ($join) {
                $join->on('products.product_type_id', '=', 'product_types.id')
                    ->whereNull('product_types.deleted_at');
            })
            ->leftJoin('product_categories', function ($join) {
                $join->on('products.product_category_id', '=', 'product_categories.id')
                    ->whereNull('product_categories.deleted_at');
            })
            ->addSelect('product_types.name as product_type_name')
            ->addSelect('product_categories.name as product_category_name')
            ->orderByDesc('products.created_at');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'products.business_id', '=', 'businesses.id');
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
            Column::name('products.code')
                ->label('Código')
                ->searchable()
                ->sortable(),

            Column::name('products.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Column::raw('product_types.name AS product_type_name')
                ->label('Tipo')
                ->sortable()
                ->searchable(),

            Column::raw('product_categories.name AS product_category_name')
                ->label('Categoría')
                ->sortable()
                ->searchable(),

            Column::callback(['products.sale_price'], function ($sale_price) {
                return '<span class="tabular-nums text-sm text-slate-700">$ ' . number_format((float) $sale_price, 2, ',', '.') . '</span>';
            })->label('Precio venta')->sortable(),

            Column::callback(['products.track_inventory'], function ($track_inventory) {
                if ($track_inventory) {
                    return '<span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-700 ring-1 ring-sky-600/20">Sí</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>';
            })->label('Inventario')->filterable([1 => 'Sí', 0 => 'No']),

            Column::callback(['products.status'], function ($status) {
                $label = $status ? 'Activo' : 'Inactivo';
                $class = $status
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(['products.id', 'products.business_id', 'products.status'], function ($id, $business_id, $status) {
                $user = auth()->user();
                $can_edit = $user->can('catalog.products.edit')
                    && ($user->hasRole('superAdmin') || $user->belongsToBusiness((int) $business_id));

                return view('livewire.admin.catalog.products.actions', [
                    'id'       => $id,
                    'can_edit' => $can_edit,
                    'status'   => $status,
                ]);
            })->label('Acciones')->unsortable(),
        ]);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('catalog.products.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Estás seguro de querer eliminar este producto?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteProductAction::run($this->delete_id);

            $this->alertDeleteSuccess('Producto eliminado correctamente.');
            $this->dispatch('product-deleted');
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
