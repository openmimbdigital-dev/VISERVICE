<?php

namespace App\Models;

use App\Models\Concerns\InteractsWithSharedCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendeeType extends Model
{
    use InteractsWithSharedCatalog;
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'minimum_range',
        'maximum_range',
        'general',
    ];

    protected function casts(): array
    {
        return [
            'minimum_range' => 'integer',
            'maximum_range' => 'integer',
            'general'       => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_attendee_type')
            ->withPivot('attendance')
            ->withTimestamps();
    }

    public function ageRangeLabel(): string
    {
        return $this->minimum_range.' – '.$this->maximum_range.' años';
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $this->isEditableBy($user)
            && $user?->can('settings.attendee_types.delete');
    }
}
