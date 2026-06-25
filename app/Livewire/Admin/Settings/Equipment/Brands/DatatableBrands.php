<?php

namespace App\Livewire\Admin\Settings\Equipment\Brands;

use App\Models\Brand;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class DatatableBrands extends LivewireDatatable
{
    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('brand-saved')]
    public function onBrandSaved(): void {}

    public function builder(): Builder
    {
        return Brand::query()
            ->visibleToUser()
            ->leftJoin('businesses', 'brands.business_id', '=', 'businesses.id')
            ->select('brands.*')
            ->orderByDesc('brands.created_at');
    }

    public function getColumns(): Model|array
    {
        return [
            Column::name('brands.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Column::callback(['brands.general'], function ($general) {
                if ($general) {
                    return '<span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>';
            })->label('General')->filterable([1 => 'Sí', 0 => 'No']),

            Column::callback(['businesses.name'], function ($business_name) {
                return $business_name
                    ? e($business_name)
                    : '<span class="text-slate-400">—</span>';
            })->label('Negocio'),

            Column::callback(['brands.active'], function ($active) {
                $label = $active ? 'Activo' : 'Inactivo';
                $class = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(['brands.id'], function ($id) {
                $brand = Brand::find($id);

                return view('livewire.admin.settings.equipment.brands.actions', [
                    'id'         => $id,
                    'can_edit'   => $brand?->isEditableBy() ?? false,
                    'can_delete' => $brand?->canDelete() ?? false,
                ]);
            })->label('Acciones')->unsortable(),
        ];
    }

    public function openEditEvent(int $id): void
    {
        $this->dispatch('open-brand-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        $brand = Brand::findOrFail($id);

        if (! $brand->canDelete()) {
            $message = $brand->hasDependencies()
                ? 'No se puede eliminar: tiene equipos asociados'
                : 'No tienes permiso para eliminar esta marca';

            $this->dispatch('swal', ['title' => $message, 'icon' => 'error']);

            return;
        }

        $brand->delete();
        $this->dispatch('swal', ['title' => 'Marca eliminada', 'icon' => 'warning']);
        $this->dispatch('brand-deleted');
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
