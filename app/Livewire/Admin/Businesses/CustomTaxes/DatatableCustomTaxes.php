<?php

namespace App\Livewire\Admin\Businesses\CustomTaxes;

use App\Actions\Business\DeleteCustomTaxAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Concerns\ResolvesCatalogDatatableRowPermissions;
use App\Models\CustomTax;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class DatatableCustomTaxes extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;
    use ResolvesCatalogDatatableRowPermissions;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('custom-tax-saved')]
    public function onSaved(): void {}

    public function builder(): Builder
    {
        $query = CustomTax::query()
            ->forAuthUser()
            ->select('custom_taxes.*')
            ->orderBy('custom_taxes.name');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'custom_taxes.business_id', '=', 'businesses.id')
                ->addSelect('businesses.name as business_name');
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('custom_taxes.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Column::callback(['custom_taxes.percentage'], function ($percentage) {
                return e(number_format((float) $percentage, 2, ',', '.') . ' %');
            })->label('Porcentaje')->sortable(),

            Column::name('custom_taxes.description')
                ->label('Descripción')
                ->searchable(),

            Column::callback(['custom_taxes.general'], function ($general) {
                if ($general) {
                    return '<span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>';
            })->label('General')->filterable([1 => 'Sí', 0 => 'No']),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::raw('businesses.name AS business_name')
                ->label('Negocio')
                ->searchable()
                ->sortable();
        }

        return array_merge($columns, [
            Column::callback(['custom_taxes.active'], function ($active) {
                $label = $active ? 'Activo' : 'Inactivo';
                $class = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(
                ['custom_taxes.id', 'custom_taxes.general', 'custom_taxes.business_id'],
                function ($id, $general, $business_id) {
                    $permissions = $this->catalogRowPermissions(
                        (bool) $general,
                        $business_id,
                        0,
                        'custom_taxes.edit',
                        'custom_taxes.delete',
                    );

                    return view('livewire.admin.businesses.custom-taxes.actions', [
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
        $this->dispatch('open-custom-tax-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('custom_taxes.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Eliminar este impuesto?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteCustomTaxAction::run($this->delete_id);

            $this->alertDeleteSuccess('Impuesto eliminado correctamente.');
            $this->dispatch('custom-tax-deleted');
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar el impuesto.');
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
