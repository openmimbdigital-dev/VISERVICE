<?php

namespace App\Livewire\Admin\Businesses\PaymentMethods;

use App\Actions\Business\DeleteBusinessPaymentMethodAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Concerns\ResolvesCatalogDatatableRowPermissions;
use App\Models\BusinessPaymentMethod;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class DatatableBusinessPaymentMethods extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;
    use ResolvesCatalogDatatableRowPermissions;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('business-payment-method-saved')]
    public function onSaved(): void {}

    public function builder(): Builder
    {
        $query = BusinessPaymentMethod::query()
            ->visibleToUser()
            ->select('business_payment_methods.*')
            ->orderBy('business_payment_methods.sort_order')
            ->orderBy('business_payment_methods.name');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'business_payment_methods.business_id', '=', 'businesses.id')
                ->addSelect('businesses.name as business_name');
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('business_payment_methods.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Column::callback(['business_payment_methods.general'], function ($general) {
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
            Column::callback(['business_payment_methods.is_default'], function ($is_default) {
                if ($is_default) {
                    return '<span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>';
                }

                return '<span class="text-slate-400">—</span>';
            })->label('Predeterminado'),

            Column::callback(['business_payment_methods.sort_order'], function ($sort_order) {
                return (int) $sort_order;
            })->label('Orden')->sortable(),

            Column::callback(['business_payment_methods.active'], function ($active) {
                $label = $active ? 'Activo' : 'Inactivo';
                $class = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(
                ['business_payment_methods.id', 'business_payment_methods.general', 'business_payment_methods.business_id'],
                function ($id, $general, $business_id) {
                    $permissions = $this->catalogRowPermissions(
                        (bool) $general,
                        $business_id,
                        0,
                        'business_payment_methods.edit',
                        'business_payment_methods.delete',
                    );

                    return view('livewire.admin.businesses.payment-methods.actions', [
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
        $this->dispatch('open-business-payment-method-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('business_payment_methods.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Eliminar este método de pago?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteBusinessPaymentMethodAction::run($this->delete_id);

            $this->alertDeleteSuccess('Método eliminado correctamente.');
            $this->dispatch('business-payment-method-deleted');
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar el método.');
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
