<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'participant_role_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'profile_photo',
        'status',
        'document_type',
        'document_number',
        'city_id',
        'country_id',
        'team_position_id',
        'name_team_position',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'document_number' => 'integer',
            'document_type' => DocumentType::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Participant $participant) {
            if (! $participant->isDirty('team_position_id')) {
                return;
            }

            if ($participant->team_position_id === null) {
                $participant->name_team_position = null;

                return;
            }

            $team_position = TeamPosition::query()
                ->whereKey($participant->team_position_id)
                ->first(['name']);

            $participant->name_team_position = $team_position?->name;
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function participant_role(): BelongsTo
    {
        return $this->belongsTo(ParticipantRole::class, 'participant_role_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function team_position(): BelongsTo
    {
        return $this->belongsTo(TeamPosition::class, 'team_position_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
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

        return $query->whereIn($query->getModel()->getTable().'.business_id', $business_ids);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
