<?php

namespace App\Livewire\Forms\Admin\Businesses;

use App\Enums\BusinessBankAccountType;
use App\Models\Bank;
use App\Models\Business;
use App\Models\BusinessBankAccount;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class BusinessBankAccountForm extends Form
{
    public ?int $bank_account_id = null;

    public ?int $business_id = null;

    public ?int $bank_id = null;

    public string $bank_name = '';

    public string $account_type = 'corriente';

    public string $account_number = '';

    public string $account_holder = '';

    public string $document_type = 'NIT';

    public string $document_number = '';

    public bool $is_default = false;

    public bool $active = true;

    public function setBankAccount(BusinessBankAccount $account): void
    {
        $this->bank_account_id   = $account->id;
        $this->business_id       = $account->business_id;
        $this->bank_id           = $account->bank_id;
        $this->bank_name         = $account->bank_name;
        $this->account_type      = $account->account_type->value;
        $this->account_number    = $account->account_number;
        $this->account_holder    = $account->account_holder;
        $this->document_type     = $account->document_type;
        $this->document_number   = $account->document_number;
        $this->is_default        = $account->is_default;
        $this->active            = $account->active;
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->bank_account_id  = null;
        $this->business_id      = null;
        $this->bank_id          = null;
        $this->bank_name        = '';
        $this->account_type     = 'corriente';
        $this->account_number   = '';
        $this->account_holder   = '';
        $this->document_type    = 'NIT';
        $this->document_number  = '';
        $this->is_default       = false;
        $this->active           = true;
    }

    public function isEditing(): bool
    {
        return (bool) $this->bank_account_id;
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function resolvedBusinessId(): int
    {
        if ($this->isSuperAdmin()) {
            return (int) $this->business_id;
        }

        return (int) auth()->user()->businessIds()[0] ?? 0;
    }

    public function getBusinesses(): Collection
    {
        if (! $this->isSuperAdmin()) {
            return collect();
        }

        return Business::query()
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getBanks(): Collection
    {
        return Bank::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function updatedBankId($value): void
    {
        if (! $value) {
            return;
        }

        $bank = Bank::query()->find($value);

        if ($bank) {
            $this->bank_name = $bank->name;
        }
    }

    public function rules(): array
    {
        $account_types = array_column(BusinessBankAccountType::cases(), 'value');

        $rules = [
            'bank_id' => ['nullable', 'integer', 'exists:banks,id'],
            'bank_name' => ['required', 'string', 'max:120'],
            'account_type' => ['required', 'string', Rule::in($account_types)],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder' => ['required', 'string', 'max:150'],
            'document_type' => ['required', 'string', 'max:20'],
            'document_number' => ['required', 'string', 'max:30'],
            'is_default' => ['boolean'],
            'active' => ['boolean'],
        ];

        if ($this->isSuperAdmin()) {
            $rules['business_id'] = ['required', 'integer', 'exists:businesses,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'business_id.required'     => 'Debe seleccionar un negocio.',
            'bank_name.required'       => 'El banco es obligatorio.',
            'account_type.required'    => 'El tipo de cuenta es obligatorio.',
            'account_type.in'          => 'El tipo de cuenta no es válido.',
            'account_number.required'  => 'El número de cuenta es obligatorio.',
            'account_holder.required'  => 'El titular es obligatorio.',
            'document_number.required' => 'El NIT es obligatorio.',
        ];
    }

    public function validated(): array
    {
        $this->validate();

        return [
            'business_id'       => $this->resolvedBusinessId(),
            'bank_id'           => $this->bank_id ? (int) $this->bank_id : null,
            'bank_name'         => trim($this->bank_name),
            'account_type'      => $this->account_type,
            'account_number'    => trim($this->account_number),
            'account_holder'    => trim($this->account_holder),
            'document_type'     => trim($this->document_type),
            'document_number'   => trim($this->document_number),
            'is_default'        => $this->is_default,
            'active'            => $this->active,
        ];
    }
}
