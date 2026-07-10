<?php

namespace App\Livewire\Admin\Workshop\QuotationServiceTypes;

use App\Actions\Workshop\DeleteQuotationServiceTypeAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Concerns\ResolvesCatalogDatatableRowPermissions;
use App\Models\QuotationServiceType;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class DatatableQuotationServiceTypes extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;
    use ResolvesCatalogDatatableRowPermissions;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('quotation-service-type-saved')]
    public function onSaved(): void {}

    public function builder(): Builder
    {
        $usage = DB::table('quotations')
            ->select('quotation_service_type_id', DB::raw('COUNT(*) as usage_count'))
            ->whereNull('deleted_at')
            ->whereNotNull('quotation_service_type_id')
            ->groupBy('quotation_service_type_id');

        $query = QuotationServiceType::query()
            ->visibleToUser()
            ->select('quotation_service_types.*')
            ->leftJoinSub($usage, 'quotation_usage', fn ($join) => $join
                ->on('quotation_service_types.id', '=', 'quotation_usage.quotation_service_type_id'))
            ->addSelect('quotation_usage.usage_count')
            ->orderByDesc('quotation_service_types.created_at');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'quotation_service_types.business_id', '=', 'businesses.id');
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('quotation_service_types.name')->label('Nombre')->searchable()->sortable(),
            Column::callback(['quotation_service_types.general'], function ($general) {
                return $general
                    ? '<span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>'
                    : '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>';
            })->label('General')->filterable([1 => 'Sí', 0 => 'No']),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::raw('businesses.name AS business_name')->label('Negocio')->searchable();
        }

        return array_merge($columns, [
            Column::callback(['quotation_service_types.active'], function ($active) {
                $label = $active ? 'Activo' : 'Inactivo';
                $class = $active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(
                ['quotation_service_types.id', 'quotation_service_types.general', 'quotation_service_types.business_id', 'quotation_usage.usage_count'],
                function ($id, $general, $business_id, $usage_count) {
                    $permissions = $this->catalogRowPermissions(
                        (bool) $general,
                        $business_id,
                        (int) $usage_count,
                        'workshop.quotation_service_types.edit',
                        'workshop.quotation_service_types.delete',
                    );

                    return view('livewire.admin.workshop.quotation-service-types.actions', [
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
        $this->dispatch('open-quotation-service-type-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('workshop.quotation_service_types.delete'), 403);
        $this->askDeleteConfirmation($id, '¿Eliminar este tipo de servicio?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteQuotationServiceTypeAction::run($this->delete_id);
            $this->alertDeleteSuccess('Tipo eliminado correctamente.');
            $this->dispatch('quotation-service-type-deleted');
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar el tipo.');
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
