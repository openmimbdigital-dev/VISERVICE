<?php

namespace App\Models;

use App\Models\Concerns\InteractsWithSharedCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationServiceType extends Model
{
    use InteractsWithSharedCatalog;
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'name', 'label', 'active', 'general',
    ];

    protected function casts(): array
    {
        return [
            'active'  => 'boolean',
            'general' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function hasDependencies(): bool
    {
        return $this->quotations()->exists();
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $this->isEditableBy($user)
            && $user?->can('workshop.quotation_service_types.delete')
            && ! $this->hasDependencies();
    }
}
