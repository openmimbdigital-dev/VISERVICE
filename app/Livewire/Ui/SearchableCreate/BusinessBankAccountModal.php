<?php

namespace App\Livewire\Ui\SearchableCreate;

use App\Actions\Business\CreateOrUpdateBusinessBankAccountAction;
use App\Enums\BusinessBankAccountType;
use App\Livewire\Forms\Admin\Businesses\BusinessBankAccountForm;
use App\Models\Bank;
use App\Models\BusinessBankAccount;
use Livewire\Component;

class BusinessBankAccountModal extends Component
{
    public BusinessBankAccountForm $form;

    public function mount(string $search = ''): void
    {
        abort_unless(auth()->user()?->can('business_bank_accounts.create'), 403);

        $this->form->active = true;

        if (! $this->form->isSuperAdmin()) {
            $this->form->business_id = auth()->user()->businessIds()[0] ?? null;
        }

        $term = trim($search);

        if ($term !== '') {
            $bank = Bank::query()
                ->where('is_active', true)
                ->where('name', 'like', $term.'%')
                ->orderBy('name')
                ->first();

            if ($bank) {
                $this->form->bank_id = $bank->id;
                $this->form->bank_name = $bank->name;
            }
        }
    }

    public function updatedFormBankId(mixed $value): void
    {
        $this->form->updatedBankId($value);
    }

    public function close(): void
    {
        $this->dispatch('searchable-create-closed', modelClass: BusinessBankAccount::class);
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('business_bank_accounts.create'), 403);

        $this->validate([
            'form.bank_id' => ['required', 'integer', 'exists:banks,id'],
        ], [
            'form.bank_id.required' => 'Selecciona un banco.',
        ]);

        $this->form->updatedBankId($this->form->bank_id);

        $account = CreateOrUpdateBusinessBankAccountAction::run(null, $this->form->validated());

        $this->dispatch('swal', [
            'title' => 'Cuenta bancaria creada',
            'icon'  => 'success',
        ]);

        $this->dispatch('searchable-created', id: $account->id, modelClass: BusinessBankAccount::class);
    }

    public function render()
    {
        return view('livewire.ui.searchable-create.business-bank-account-modal', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'businesses'     => $this->form->getBusinesses(),
            'banks'          => $this->form->getBanks(),
            'account_types'  => BusinessBankAccountType::options(),
        ]);
    }
}
