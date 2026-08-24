<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusinessTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomTax extends Model
{
    use BelongsToBusinessTenant;
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'percentage',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'active'     => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $this->isEditableBy($user, 'custom_taxes.delete');
    }
}
