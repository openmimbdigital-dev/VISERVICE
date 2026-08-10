<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventTeam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(EventTeamRole::class, 'event_team_role')
            ->withTimestamps();
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_event_team')
            ->withTimestamps();
    }

    public function members(): HasMany
    {
        return $this->hasMany(EventTeamMember::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class, 'event_team_members')
            ->withPivot(['business_id', 'event_team_role_id'])
            ->wherePivotNull('deleted_at')
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

        return $query->whereIn('event_teams.business_id', $business_ids);
    }

    public function hasDependencies(): bool
    {
        return $this->events()->exists();
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user?->can('events.teams.delete')) {
            return false;
        }

        if ($this->hasDependencies()) {
            return false;
        }

        if ($user->hasRole('superAdmin')) {
            return true;
        }

        return $user->belongsToBusiness($this->business_id);
    }
}
