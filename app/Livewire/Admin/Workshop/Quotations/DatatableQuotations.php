<?php

namespace App\Livewire\Admin\Workshop\Quotations;

use App\Actions\Workshop\DeleteQuotationAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Quotation;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\DateColumn;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class DatatableQuotations extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('quotation-saved')]
    public function onSaved(): void {}

    public function builder(): Builder
    {
        return Quotation::query()
            ->forAuthUser()
            ->leftJoin('clients', 'quotations.client_id', '=', 'clients.id')
            ->leftJoin('equipment', 'quotations.equipment_id', '=', 'equipment.id')
            ->select('quotations.*')
            ->orderByDesc('quotations.created_at');
    }

    public function getColumns(): Model|array
    {
        return [
            Column::name('quotations.reference')
                ->label('Referencia')
                ->searchable()
                ->sortable(),

            Column::raw('clients.name AS client_name')
                ->label('Cliente')
                ->searchable()
                ->sortable(),

            Column::raw('equipment.plate AS equipment_plate')
                ->label('Placa')
                ->searchable(),

            Column::callback(['quotations.total'], function ($total) {
                return '<span class="tabular-nums font-semibold">' . col_money($total) . '</span>';
            })->label('Total')->sortable(),

            Column::callback(['quotations.valid_until'], function ($date) {
                return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '<span class="text-slate-400">—</span>';
            })->label('Válida hasta'),

            Column::callback(['quotations.status'], function ($status) {
                return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">Borrador</span>';
            })->label('Estado'),

            DateColumn::name('quotations.created_at')
                ->label('Fecha')
                ->sortable(),

            Column::callback(['quotations.id', 'quotations.status'], function ($id, $status) {
                $can_delete = $status === 'borrador'
                    && auth()->user()->can('workshop.quotations.delete');

                return view('livewire.admin.workshop.quotations.actions', [
                    'id'         => $id,
                    'status'     => $status,
                    'can_delete' => $can_delete,
                ]);
            })->label('Acciones')->unsortable(),
        ];
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('workshop.quotations.delete'), 403);
        $this->askDeleteConfirmation($id, '¿Eliminar esta cotización?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteQuotationAction::run($this->delete_id);
            $this->alertDeleteSuccess('Cotización eliminada correctamente.');
            $this->dispatch('quotation-deleted');
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar la cotización.');
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
