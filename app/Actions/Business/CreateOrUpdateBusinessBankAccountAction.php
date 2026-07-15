<?php

namespace App\Actions\Business;

use App\Actions\LogUserHistoricalAction;
use App\Enums\BusinessBankAccountType;
use App\Models\BusinessBankAccount;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateBusinessBankAccountAction
{
    use AsAction;

    /**
     * @param  array{
     *   business_id: int,
     *   bank_id: int|null,
     *   bank_name: string,
     *   account_type: string,
     *   account_number: string,
     *   account_holder: string,
     *   document_type: string,
     *   document_number: string,
     *   is_default: bool,
     *   active: bool
     * }  $data
     */
    public function handle(?int $bank_account_id, array $data): BusinessBankAccount
    {
        abort_unless(
            auth()->user()->can($bank_account_id ? 'business_bank_accounts.edit' : 'business_bank_accounts.create'),
            403
        );

        $user        = auth()->user();
        $business_id = (int) $data['business_id'];

        if (! $user->hasRole('superAdmin')) {
            abort_unless($user->belongsToBusiness($business_id), 403);
        }

        return DB::transaction(function () use ($bank_account_id, $data, $business_id) {
            $attributes = [
                'business_id'      => $business_id,
                'bank_id'          => $data['bank_id'],
                'bank_name'        => $data['bank_name'],
                'account_type'     => BusinessBankAccountType::from($data['account_type']),
                'account_number'   => $data['account_number'],
                'account_holder'   => $data['account_holder'],
                'document_type'    => $data['document_type'],
                'document_number'  => $data['document_number'],
                'is_default'       => $data['is_default'],
                'active'           => $data['active'],
            ];

            if ($bank_account_id) {
                $account = BusinessBankAccount::query()->forAuthUser()->findOrFail($bank_account_id);
                abort_unless($account->isEditableBy(auth()->user(), 'business_bank_accounts.edit'), 403);
                abort_unless((int) $account->business_id === $business_id, 403);

                $account->update($attributes);
            } else {
                $account = BusinessBankAccount::create($attributes);
            }

            if ($data['is_default']) {
                BusinessBankAccount::query()
                    ->where('business_id', $business_id)
                    ->whereKeyNot($account->id)
                    ->update(['is_default' => false]);
            }

            $account = $account->fresh(['business', 'bank']);

            LogUserHistoricalAction::run(
                action: $bank_account_id ? 'updated' : 'created',
                module: 'business.bank_accounts',
                description: ($bank_account_id ? 'Actualizó' : 'Creó') . " la cuenta bancaria {$account->bank_name}",
                subject: $account,
                subject_label: "{$account->bank_name} · {$account->account_number}",
                properties: [
                    'bank_name'      => $account->bank_name,
                    'account_type'   => $account->account_type?->value ?? $account->account_type,
                    'account_number' => $account->account_number,
                    'is_default'     => $account->is_default,
                    'active'         => $account->active,
                ],
                business_id: $business_id,
            );

            return $account;
        });
    }
}
