<?php

namespace App\Livewire\Admin\Settings\Catalog\Units;

use App\Actions\Settings\Catalog\DeleteUnitAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Concerns\ResolvesCatalogDatatableRowPermissions;
use App\Models\Unit;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class DatatableUnits extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;
    use ResolvesCatalogDatatableRowPermissions;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('unit-deleted')]
    public function onUnitDeleted(): void {}

    #[On('unit-saved')]
    public function onUnitSaved(): void {}

    public function builder(): Builder
    {
        $products_count = DB::table('products')
            ->select('unit_id', DB::raw('COUNT(*) as products_count'))
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

        $products_count->groupBy('unit_id');

        $query = Unit::query()
            ->visibleToUser()
            ->select('units.*')
            ->leftJoinSub(
                $products_count,
                'product_usage',
                fn ($join) => $join->on('units.id', '=', 'product_usage.unit_id')
            )
            ->addSelect('product_usage.products_count')
            ->orderByDesc('units.created_at');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'units.business_id', '=', 'businesses.id');
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('units.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Column::name('units.symbol')
                ->label('Símbolo')
                ->searchable()
                ->sortable(),

            Column::callback(['units.general'], function ($general) {
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
            Column::callback(['units.active'], function ($active) {
                $label = $active ? 'Activo' : 'Inactivo';
                $class = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(
                ['units.id', 'units.general', 'units.business_id', 'product_usage.products_count'],
                function ($id, $general, $business_id, $products_count) {
                    $permissions = $this->catalogRowPermissions(
                        (bool) $general,
                        $business_id,
                        (int) $products_count,
                        'settings.units.edit',
                        'settings.units.delete',
                    );

                    return view('livewire.admin.settings.catalog.units.actions', [
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
        $this->dispatch('open-unit-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('settings.units.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Estás seguro de querer eliminar esta unidad?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteUnitAction::run($this->delete_id);

            $this->alertDeleteSuccess('Unidad eliminada correctamente.');
            $this->dispatch('unit-deleted');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar la unidad.');
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
