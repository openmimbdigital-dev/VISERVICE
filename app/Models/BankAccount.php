<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_id',
        'logo',
        'account_type',
        'account_number',
        'account_holder',
        'document_type',
        'document_number',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function getAccountTypeLabelAttribute(): string
    {
        return match ($this->account_type) {
            'corriente' => 'Corriente',
            'ahorros'   => 'Ahorros',
            default     => $this->account_type,
        };
    }

    public function getBankNameAttribute(): string
    {
        return $this->bank?->name ?? '—';
    }
}
