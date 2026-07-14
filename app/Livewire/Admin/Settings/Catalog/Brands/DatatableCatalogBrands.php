<?php

namespace App\Livewire\Admin\Settings\Catalog\Brands;

use App\Actions\Settings\Catalog\DeleteCatalogBrandAction;
use App\Enums\BrandUsageType;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Concerns\ResolvesCatalogDatatableRowPermissions;
use App\Models\Brand;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class DatatableCatalogBrands extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;
    use ResolvesCatalogDatatableRowPermissions;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('catalog-brand-deleted')]
    public function onCatalogBrandDeleted(): void {}

    public function builder(): Builder
    {
        $products_count = DB::table('products')
            ->select('brand_id', DB::raw('COUNT(*) as products_count'))
            ->whereNull('deleted_at');

        $user = auth()->user();

        if ($user && ! $user->hasRole('superAdmin')) {
            $business_ids = $user->businessIds();

            if ($business_ids === []) {
                $products_count->whereRaw('0 = 1');
            } else {
                $products_count->whereIn('business_id', $business_ids);
            }
        }

        $products_count->groupBy('brand_id');

        $categories_count = DB::table('brand_product_category')
            ->select('brand_id', DB::raw('COUNT(*) as categories_count'))
            ->groupBy('brand_id');

        $equipment_usage = DB::table('brand_usage')
            ->select('brand_id')
            ->where('type', BrandUsageType::Equipment->value)
            ->groupBy('brand_id');

        $query = Brand::query()
            ->visibleToUser()
            ->forProductsCatalog()
            ->select('brands.*')
            ->leftJoinSub(
                $products_count,
                'product_usage',
                fn ($join) => $join->on('brands.id', '=', 'product_usage.brand_id')
            )
            ->leftJoinSub(
                $categories_count,
                'category_usage',
                fn ($join) => $join->on('brands.id', '=', 'category_usage.brand_id')
            )
            ->leftJoinSub(
                $equipment_usage,
                'equipment_usage_flag',
                fn ($join) => $join->on('brands.id', '=', 'equipment_usage_flag.brand_id')
            )
            ->addSelect(
                'product_usage.products_count',
                'category_usage.categories_count',
                'equipment_usage_flag.brand_id'
            )
            ->orderByDesc('brands.created_at');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'brands.business_id', '=', 'businesses.id');
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('brands.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Column::callback(['category_usage.categories_count'], function ($categories_count) {
                $count = (int) $categories_count;

                return '<span class="text-sm text-slate-700">' . $count . '</span>';
            })->label('Categorías'),

            Column::callback(['brands.general'], function ($general) {
                if ($general) {
                    return '<span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>';
            })->label('General')->filterable([1 => 'Sí', 0 => 'No']),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::callback(['businesses.name'], function ($business_name) {
                return $business_name
                    ? e($business_name)
                    : '<span class="text-slate-400">—</span>';
            })->label('Negocio');
        }

        return array_merge($columns, [
            Column::callback(['brands.active'], function ($active) {
                $label = $active ? 'Activo' : 'Inactivo';
                $class = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(
                ['brands.id', 'brands.general', 'brands.business_id', 'product_usage.products_count', 'equipment_usage_flag.brand_id'],
                function ($id, $general, $business_id, $products_count, $equipment_usage_brand_id) {
                    $has_equipment_usage = $equipment_usage_brand_id !== null;

                    $permissions = $this->catalogRowPermissions(
                        (bool) $general,
                        $business_id,
                        (int) $products_count,
                        'settings.brands.edit',
                        'settings.brands.delete',
                    );

                    $can_delete = $permissions['can_delete'] && ! $has_equipment_usage;

                    return view('livewire.admin.settings.catalog.brands.actions', [
                        'id'                  => $id,
                        'can_edit'            => $permissions['can_edit'],
                        'can_delete'          => $can_delete,
                        'is_general_readonly' => $permissions['is_general_readonly'],
                        'has_equipment_usage' => $has_equipment_usage,
                        'has_products'        => (int) $products_count > 0,
                    ]);
                }
            )->label('Acciones')->unsortable(),
        ]);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('settings.brands.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Estás seguro de querer eliminar esta marca?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteCatalogBrandAction::run($this->delete_id);

            $this->alertDeleteSuccess('Marca eliminada correctamente.');
            $this->dispatch('catalog-brand-deleted');
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar la marca.');
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
