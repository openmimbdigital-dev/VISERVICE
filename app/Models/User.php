<?php

namespace App\Models;

use App\Support\BusinessAccess;
use App\Support\CurrentBusiness;
use Illuminate\Database\Eloquent\Casts\Attribute as EloquentAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'        => 'hashed',
            'status'          => 'boolean',
            'document_number' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->isDirty('team_position_id')) {
                if ($user->team_position_id === null) {
                    $user->name_team_position = null;

                    return;
                }

                $team_position = TeamPosition::query()
                    ->whereKey($user->team_position_id)
                    ->first(['name']);

                $user->name_team_position = $team_position?->name;
            }
        });
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

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'user_business')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function user_historicals(): HasMany
    {
        return $this->hasMany(UserHistorical::class)->orderByDesc('created_at');
    }

    public function equipment_historicals(): HasMany
    {
        return $this->hasMany(EquipmentHistorical::class)->orderByDesc('created_at');
    }

    public function event_attendances(): MorphMany
    {
        return $this->morphMany(EventAttendance::class, 'attendable');
    }

    public function attended_events(): MorphToMany
    {
        return $this->morphToMany(Event::class, 'attendable', 'event_attendances')
            ->withPivot(['date_event', 'attendance_hour', 'attendance'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function primaryBusiness(): ?Business
    {
        if ($this->relationLoaded('businesses')) {
            return $this->businesses->first(fn (Business $business) => (bool) $business->pivot->is_primary)
                ?? $this->businesses->first();
        }

        return $this->businesses()
            ->orderByDesc('user_business.is_primary')
            ->orderBy('user_business.id')
            ->first();
    }

    /** @return list<int> */
    public function businessIds(): array
    {
        if ($this->relationLoaded('businesses')) {
            return $this->businesses->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return $this->businesses()->pluck('businesses.id')->map(fn ($id) => (int) $id)->all();
    }

    public function belongsToBusiness(?int $business_id): bool
    {
        if ($business_id === null) {
            return false;
        }

        if ($this->relationLoaded('businesses')) {
            return $this->businesses->contains('id', (int) $business_id);
        }

        return $this->businesses()->where('businesses.id', $business_id)->exists();
    }

    public function canViaOrganizationType(string $permission): bool
    {
        return $this->can($permission);
    }

    /** @return \Illuminate\Support\Collection<int, Role> */
    public function assignableRoles(): \Illuminate\Support\Collection
    {
        return BusinessAccess::assignableRolesForUser($this);
    }

    public function syncBusinesses(array $businesses): void
    {
        $this->businesses()->sync($businesses);
    }

    public function attachBusiness(int $business_id, bool $is_primary = false): void
    {
        if ($is_primary) {
            DB::table('user_business')
                ->where('user_id', $this->id)
                ->update(['is_primary' => false]);
        }

        $this->businesses()->syncWithoutDetaching([
            $business_id => ['is_primary' => $is_primary],
        ]);
    }

    protected function businessId(): EloquentAttribute
    {
        return EloquentAttribute::get(fn () => CurrentBusiness::id() ?? $this->primaryBusiness()?->id);
    }

    protected function business(): EloquentAttribute
    {
        return EloquentAttribute::get(fn () => CurrentBusiness::get() ?? $this->primaryBusiness());
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
