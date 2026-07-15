<?php

namespace App\Actions\Business;

use App\Actions\LogUserHistoricalAction;
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

        LogUserHistoricalAction::run(
            action: 'deleted',
            module: 'business.bank_accounts',
            description: "Eliminó la cuenta bancaria {$account->bank_name}",
            subject: $account,
            subject_label: "{$account->bank_name} · {$account->account_number}",
            properties: [
                'bank_name'      => $account->bank_name,
                'account_number' => $account->account_number,
            ],
            business_id: (int) $account->business_id,
        );

        $account->delete();
    }
}
