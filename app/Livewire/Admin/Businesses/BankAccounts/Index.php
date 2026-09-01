<?php

namespace App\Livewire\Admin\Businesses\BankAccounts;

use App\Actions\Business\CreateOrUpdateBusinessBankAccountAction;
use App\Livewire\Forms\Admin\Businesses\BusinessBankAccountForm;
use App\Models\BusinessBankAccount;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Datos bancarios')]
class Index extends Component
{
    public BusinessBankAccountForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('business_bank_accounts.view'), 403);

        $edit_id = request()->integer('edit');

        if ($edit_id > 0) {
            $this->openEdit($edit_id);
        }
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('business_bank_accounts.create'), 403);

        $this->form->reset();
        $this->form->active = true;

        if (! $this->form->isSuperAdmin()) {
            $this->form->business_id = auth()->user()->businessIds()[0] ?? null;
        }

        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('open-business-bank-account-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('business_bank_accounts.edit'), 403);

        $account = BusinessBankAccount::query()->forAuthUser()->findOrFail($id);
        abort_unless($account->isEditableBy(auth()->user(), 'business_bank_accounts.edit'), 403);

        $this->form->setBankAccount($account);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('business-bank-account-deleted')]
    public function onRecordDeleted(): void {}

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->form->reset();
        $this->form->active = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can($this->form->isEditing() ? 'business_bank_accounts.edit' : 'business_bank_accounts.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        CreateOrUpdateBusinessBankAccountAction::run(
            $this->form->bank_account_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Cuenta actualizada' : 'Cuenta creada',
            'icon'  => 'success',
        ]);

        $this->dispatch('business-bank-account-saved');
    }

    public function render()
    {
        $query = BusinessBankAccount::query()->forAuthUser();

        return view('livewire.admin.businesses.bank-accounts.index', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'businesses'     => $this->form->getBusinesses(),
            'banks'          => $this->form->getBanks(),
            'account_types'  => \App\Enums\BusinessBankAccountType::options(),
            'stats'          => [
                'total'   => (clone $query)->count(),
                'active'  => (clone $query)->where('active', true)->count(),
                'default' => (clone $query)->where('is_default', true)->count(),
            ],
        ]);
    }
}
