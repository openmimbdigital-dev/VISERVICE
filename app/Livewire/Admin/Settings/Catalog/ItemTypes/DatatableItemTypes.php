<?php

namespace App\Livewire\Admin\Settings\Catalog\ItemTypes;

use App\Actions\Settings\Catalog\DeleteItemTypeAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Concerns\ResolvesCatalogDatatableRowPermissions;
use App\Models\ItemType;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class DatatableItemTypes extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;
    use ResolvesCatalogDatatableRowPermissions;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('item-type-deleted')]
    public function onItemTypeDeleted(): void {}

    #[On('item-type-saved')]
    public function onItemTypeSaved(): void {}

    public function builder(): Builder
    {
        $items_count = DB::table('items')
            ->select('item_type_id', DB::raw('COUNT(*) as items_count'))
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

        $items_count->groupBy('item_type_id');

        $query = ItemType::query()
            ->visibleToUser()
            ->select('item_types.*')
            ->leftJoinSub(
                $items_count,
                'item_usage',
                fn ($join) => $join->on('item_types.id', '=', 'item_usage.item_type_id')
            )
            ->addSelect('item_usage.items_count')
            ->orderByDesc('item_types.created_at');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'item_types.business_id', '=', 'businesses.id');
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('item_types.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Column::callback(['item_types.general'], function ($general) {
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
            Column::callback(['item_types.active'], function ($active) {
                $label = $active ? 'Activo' : 'Inactivo';
                $class = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(
                ['item_types.id', 'item_types.general', 'item_types.business_id', 'item_usage.items_count'],
                function ($id, $general, $business_id, $items_count) {
                    $permissions = $this->catalogRowPermissions(
                        (bool) $general,
                        $business_id,
                        (int) $items_count,
                        'settings.item_types.edit',
                        'settings.item_types.delete',
                    );

                    return view('livewire.admin.settings.catalog.item-types.actions', [
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
        $this->dispatch('open-item-type-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('settings.item_types.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Estás seguro de querer eliminar este tipo de producto?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteItemTypeAction::run($this->delete_id);

            $this->alertDeleteSuccess('Tipo eliminado correctamente.');
            $this->dispatch('item-type-deleted');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el tipo.');
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
