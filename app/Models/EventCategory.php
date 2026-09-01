<?php

namespace App\Models;

use App\Enums\EventCategoryType;
use App\Models\Concerns\InteractsWithSharedCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventCategory extends Model
{
    use InteractsWithSharedCatalog;
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'type',
        'general',
    ];

    protected function casts(): array
    {
        return [
            'type'    => EventCategoryType::class,
            'general' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $this->isEditableBy($user)
            && $user?->can('settings.event_categories.delete');
    }
}
