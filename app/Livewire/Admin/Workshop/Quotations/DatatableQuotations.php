<?php

namespace App\Livewire\Admin\Workshop\Quotations;

use App\Actions\Workshop\DeleteQuotationAction;
use App\Enums\QuotationStatus;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Quotation;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\DateColumn;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
        $items_count = DB::table('quotation_items')
            ->select('quotation_id', DB::raw('COUNT(*) as items_count'))
            ->groupBy('quotation_id');

        return Quotation::query()
            ->forAuthUser()
            ->leftJoin('clients', 'quotations.client_id', '=', 'clients.id')
            ->leftJoin('work_orders', function ($join) {
                $join->on('work_orders.quotation_id', '=', 'quotations.id')
                    ->whereNull('work_orders.deleted_at');
            })
            ->leftJoinSub(
                $items_count,
                'quotation_items_agg',
                fn ($join) => $join->on('quotations.id', '=', 'quotation_items_agg.quotation_id')
            )
            ->select('quotations.*')
            ->addSelect('work_orders.id as linked_work_order_id')
            ->addSelect('quotation_items_agg.items_count')
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

            Column::callback(['quotations.total'], function ($total) {
                return '<span class="tabular-nums font-semibold">' . col_money($total) . '</span>';
            })->label('Total')->sortable(),

            Column::callback(['quotations.step', 'quotations.final_step', 'quotation_items_agg.items_count'], function ($step, $final_step, $items_count) {
                $final    = max(1, (int) $final_step);
                $percent  = (int) min(100, round(((int) $step / $final) * 100));
                $complete = (int) $items_count > 0;
                $label    = $complete ? 'Completo' : 'Paso ' . (int) $step . '/' . $final;
                $class    = $complete
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-amber-50 text-amber-800 ring-1 ring-amber-600/20';

                return '<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">'
                    . $label
                    . ' · ' . $percent . '%</span>';
            })->label('Progreso')->unsortable(),

            Column::callback(['quotations.valid_until'], function ($date) {
                return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '<span class="text-slate-400">—</span>';
            })->label('Válida hasta'),

            Column::callback(['quotations.status'], function ($status) {
                $enum = QuotationStatus::tryFrom((string) $status) ?? QuotationStatus::Created;

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $enum->badgeClass() . '">' . e($enum->label()) . '</span>';
            })->label('Estado')->filterable(\App\Models\Status::optionsForModule('quotations')),

            DateColumn::name('quotations.created_at')
                ->label('Fecha')
                ->sortable(),

            Column::callback(['quotations.id', 'quotations.status', 'work_orders.id'], function ($id, $status, $work_order_id) {
                $is_locked = in_array($status, [
                    QuotationStatus::Accepted->value,
                    QuotationStatus::Rejected->value,
                ], true);

                return view('livewire.admin.workshop.quotations.actions', [
                    'id'            => $id,
                    'can_create_ot' => $status === QuotationStatus::Accepted->value && empty($work_order_id),
                    'has_work_order'=> ! empty($work_order_id),
                    'is_locked'     => $is_locked,
                    'lock_title'    => $status === QuotationStatus::Accepted->value
                        ? 'La cotización está aceptada'
                        : 'La cotización está rechazada',
                    'work_order_id' => $work_order_id,
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
