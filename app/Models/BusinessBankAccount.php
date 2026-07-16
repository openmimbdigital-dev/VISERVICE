<?php

namespace App\Models;

use App\Enums\BusinessBankAccountType;
use App\Models\Concerns\BelongsToBusinessTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessBankAccount extends Model
{
    use BelongsToBusinessTenant;
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'bank_id',
        'bank_name',
        'account_type',
        'account_number',
        'account_holder',
        'document_type',
        'document_number',
        'is_default',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'account_type' => BusinessBankAccountType::class,
            'is_default'   => 'boolean',
            'active'       => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function accountTypeLabel(): string
    {
        return $this->account_type?->label() ?? '—';
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $this->isEditableBy($user, 'business_bank_accounts.delete');
    }
}
