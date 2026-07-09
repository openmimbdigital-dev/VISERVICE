<?php

namespace App\Actions\Business;

use App\Models\BusinessBankAccount;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteBusinessBankAccountAction
{
    use AsAction;

    public function handle(int $bank_account_id): void
    {
        abort_unless(auth()->user()?->can('business_bank_accounts.delete'), 403);

        $account = BusinessBankAccount::query()->forAuthUser()->findOrFail($bank_account_id);

        abort_unless($account->canDelete(), 403);

        $account->delete();
    }
}
