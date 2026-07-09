<?php

namespace App\Livewire\Admin\Settings\Catalog\ItemCategories;

use App\Actions\Settings\Catalog\DeleteItemCategoryAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Concerns\ResolvesCatalogDatatableRowPermissions;
use App\Models\ItemCategory;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class DatatableItemCategories extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;
    use ResolvesCatalogDatatableRowPermissions;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('item-category-deleted')]
    public function onItemCategoryDeleted(): void {}

    #[On('item-category-saved')]
    public function onItemCategorySaved(): void {}

    public function builder(): Builder
    {
        $items_count = DB::table('items')
            ->select('item_category_id', DB::raw('COUNT(*) as items_count'))
            ->whereNull('deleted_at');

        $user = auth()->user();

        if ($user && ! $user->hasRole('superAdmin')) {
            $business_ids = $user->businessIds();

            if ($business_ids === []) {
                $items_count->whereRaw('0 = 1');
            } else {
                $items_count->whereIn('business_id', $business_ids);
            }
        }

        $items_count->groupBy('item_category_id');

        $query = ItemCategory::query()
            ->visibleToUser()
            ->select('item_categories.*')
            ->leftJoinSub(
                $items_count,
                'item_usage',
                fn ($join) => $join->on('item_categories.id', '=', 'item_usage.item_category_id')
            )
            ->addSelect('item_usage.items_count')
            ->orderByDesc('item_categories.created_at');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'item_categories.business_id', '=', 'businesses.id');
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('item_categories.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Column::callback(['item_categories.inventory'], function ($inventory) {
                $label = $inventory ? 'Sí' : 'No';

                return '<span class="text-sm text-slate-700">' . $label . '</span>';
            })->label('Inventario')->filterable([1 => 'Sí', 0 => 'No']),

            Column::callback(['item_categories.general'], function ($general) {
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
            Column::callback(['item_categories.active'], function ($active) {
                $label = $active ? 'Activo' : 'Inactivo';
                $class = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(
                ['item_categories.id', 'item_categories.general', 'item_categories.business_id', 'item_usage.items_count'],
                function ($id, $general, $business_id, $items_count) {
                    $permissions = $this->catalogRowPermissions(
                        (bool) $general,
                        $business_id,
                        (int) $items_count,
                        'settings.item_categories.edit',
                        'settings.item_categories.delete',
                    );

                    return view('livewire.admin.settings.catalog.item-categories.actions', [
                        'id'                  => $id,
                        'can_edit'            => $permissions['can_edit'],
                        'can_delete'          => $permissions['can_delete'],
                        'is_general_readonly' => $permissions['is_general_readonly'],
                    ]);
                }
            )->label('Acciones')->unsortable(),
        ]);
    }

    public function openEditEvent(int $id): void
    {
        $this->dispatch('open-item-category-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('settings.item_categories.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Estás seguro de querer eliminar esta categoría?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteItemCategoryAction::run($this->delete_id);

            $this->alertDeleteSuccess('Categoría eliminada correctamente.');
            $this->dispatch('item-category-deleted');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar la categoría.');
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
