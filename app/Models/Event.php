<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'event_category_id',
        'name',
        'description',
        'date',
        'start_time',
        'end_time',
        'attendance',
    ];

    protected function casts(): array
    {
        return [
            'date'       => 'date',
            'attendance' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function attendee_types(): BelongsToMany
    {
        return $this->belongsToMany(AttendeeType::class, 'event_attendee_type')
            ->withPivot('attendance')
            ->withTimestamps();
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(EventTeam::class, 'event_event_team')
            ->withTimestamps();
    }

    public function scopeForAuthUser(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

        $business_ids = $user?->businessIds() ?? [];

        if ($business_ids === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn('events.business_id', $business_ids);
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user?->can('events.events.delete')) {
            return false;
        }

        if ($user->hasRole('superAdmin')) {
            return true;
        }

        return $user->belongsToBusiness($this->business_id);
    }
}
