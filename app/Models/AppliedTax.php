<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AppliedTax extends Model
{
    protected $fillable = [
        'taxable_type',
        'taxable_id',
        'custom_tax_id',
        'custom_tax_name',
        'tax_percentage',
        'tax_amount',
    ];

    protected function casts(): array
    {
        return [
            'tax_percentage' => 'decimal:2',
            'tax_amount'     => 'decimal:2',
        ];
    }

    public function taxable(): MorphTo
    {
        return $this->morphTo();
    }

    public function customTax(): BelongsTo
    {
        return $this->belongsTo(CustomTax::class);
    }

    public function percentageLabel(): string
    {
        return rtrim(rtrim(number_format((float) $this->tax_percentage, 2, '.', ''), '0'), '.');
    }
}
