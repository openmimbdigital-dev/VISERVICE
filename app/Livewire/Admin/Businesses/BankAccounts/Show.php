<?php

namespace App\Livewire\Admin\Businesses\BankAccounts;

use App\Actions\Business\DeleteBusinessBankAccountAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\BusinessBankAccount;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cuenta bancaria')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public BusinessBankAccount $bank_account;

    public function mount(BusinessBankAccount $bankAccount): void
    {
        abort_unless(auth()->user()?->can('business_bank_accounts.view'), 403);

        abort_unless(
            BusinessBankAccount::query()->forAuthUser()->whereKey($bankAccount->id)->exists(),
            404
        );

        $this->bank_account = $bankAccount->load(['business', 'bank']);
    }

    public function deleteRecord(): void
    {
        abort_unless(auth()->user()?->can('business_bank_accounts.delete'), 403);

        $this->askDeleteConfirmation($this->bank_account->id, '¿Eliminar esta cuenta bancaria?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteBusinessBankAccountAction::run($this->delete_id);

            $this->alertDeleteSuccess('Cuenta eliminada correctamente.');

            $this->redirectRoute('admin.business-bank-accounts.index', navigate: true);
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar la cuenta.');
        }
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.admin.businesses.bank-accounts.show', [
            'can_edit'   => $user->can('business_bank_accounts.edit')
                && $this->bank_account->isEditableBy($user, 'business_bank_accounts.edit'),
            'can_delete' => $user->can('business_bank_accounts.delete')
                && $this->bank_account->canDelete($user),
        ]);
    }
}
