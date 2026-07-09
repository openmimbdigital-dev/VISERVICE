<?php

namespace App\Livewire\Admin\Businesses\BankAccounts;

use App\Enums\BusinessBankAccountType;
use App\Actions\Business\DeleteBusinessBankAccountAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\BusinessBankAccount;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class DatatableBusinessBankAccounts extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('business-bank-account-saved')]
    public function onSaved(): void {}

    public function builder(): Builder
    {
        $query = BusinessBankAccount::query()
            ->forAuthUser()
            ->select('business_bank_accounts.*')
            ->orderByDesc('business_bank_accounts.is_default')
            ->orderBy('business_bank_accounts.bank_name');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'business_bank_accounts.business_id', '=', 'businesses.id')
                ->addSelect('businesses.name as business_name');
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('business_bank_accounts.bank_name')
                ->label('Banco')
                ->searchable()
                ->sortable(),

            Column::callback(['business_bank_accounts.account_type'], function ($account_type) {
                try {
                    return e(BusinessBankAccountType::from((string) $account_type)->label());
                } catch (\ValueError) {
                    return e((string) $account_type);
                }
            })->label('Tipo'),

            Column::name('business_bank_accounts.account_number')
                ->label('Número')
                ->searchable(),

            Column::name('business_bank_accounts.account_holder')
                ->label('Titular')
                ->searchable(),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::raw('businesses.name AS business_name')
                ->label('Negocio')
                ->searchable()
                ->sortable();
        }

        return array_merge($columns, [
            Column::callback(['business_bank_accounts.is_default'], function ($is_default) {
                if ($is_default) {
                    return '<span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>';
                }

                return '<span class="text-slate-400">—</span>';
            })->label('Predeterminada'),

            Column::callback(['business_bank_accounts.active'], function ($active) {
                $label = $active ? 'Activa' : 'Inactiva';
                $class = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activa', 0 => 'Inactiva']),

            Column::callback(
                ['business_bank_accounts.id', 'business_bank_accounts.business_id'],
                function ($id, $business_id) {
                    $user = auth()->user();
                    $can_edit = $user->can('business_bank_accounts.edit')
                        && ($user->hasRole('superAdmin') || $user->belongsToBusiness($business_id));
                    $can_delete = $user->can('business_bank_accounts.delete')
                        && ($user->hasRole('superAdmin') || $user->belongsToBusiness($business_id));

                    return view('livewire.admin.businesses.bank-accounts.actions', [
                        'id'         => $id,
                        'can_edit'   => $can_edit,
                        'can_delete' => $can_delete,
                    ]);
                }
            )->label('Acciones')->unsortable(),
        ]);
    }

    public function openEditEvent(int $id): void
    {
        $this->dispatch('open-business-bank-account-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('business_bank_accounts.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Eliminar esta cuenta bancaria?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteBusinessBankAccountAction::run($this->delete_id);

            $this->alertDeleteSuccess('Cuenta eliminada correctamente.');
            $this->dispatch('business-bank-account-deleted');
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar la cuenta.');
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
