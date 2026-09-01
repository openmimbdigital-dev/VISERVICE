<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TeamPosition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'organization_type_id',
        'name',
        'label',
        'active',
        'general',
    ];

    protected function casts(): array
    {
        return [
            'active'  => 'boolean',
            'general' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (TeamPosition $team_position) {
            if ($team_position->isDirty('name') || blank($team_position->label)) {
                $team_position->label = static::normalizeLabel($team_position->name);
            }
        });
    }

    public static function normalizeLabel(string $name): string
    {
        $ascii = Str::ascii($name);
        $label = strtolower($ascii);
        $label = preg_replace('/[^a-z0-9\s_]/', '', $label) ?? '';
        $label = preg_replace('/\s+/', '_', trim($label)) ?? '';
        $label = preg_replace('/_+/', '_', $label) ?? '';

        return trim($label, '_');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function organization_type(): BelongsTo
    {
        return $this->belongsTo(OrganizationType::class, 'organization_type_id');
    }

    /** Usuarios asignados a este cargo (cada usuario tiene un solo cargo). */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'team_position_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        return $query->where("{$table}.active", true);
    }

    public function scopeVisibleToUser(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();
        $table = $query->getModel()->getTable();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

        $business_ids = $user->businessIds();

        if ($business_ids === []) {
            return $query->whereRaw('0 = 1');
        }

        $organization_type_ids = Business::query()
            ->whereIn('id', $business_ids)
            ->pluck('organization_type_id')
            ->unique()
            ->filter()
            ->values()
            ->all();

        return $query->where(function (Builder $q) use ($table, $business_ids, $organization_type_ids) {
            $q->where(function (Builder $gq) use ($table, $organization_type_ids) {
                $gq->where("{$table}.general", true)
                    ->where(function (Builder $tq) use ($table, $organization_type_ids) {
                        $tq->whereNull("{$table}.organization_type_id");

                        if ($organization_type_ids !== []) {
                            $tq->orWhereIn("{$table}.organization_type_id", $organization_type_ids);
                        }
                    });
            });

            $q->orWhereIn("{$table}.business_id", $business_ids);
        });
    }

    public function isEditableBy(?User $user = null): bool
    {
        $user ??= auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return $user->can('team_positions.edit');
        }

        if ($this->general) {
            return false;
        }

        return $this->business_id !== null
            && $user->belongsToBusiness($this->business_id)
            && $user->can('team_positions.edit');
    }

    public function isGeneralReadonly(?User $user = null): bool
    {
        return $this->general && ! $this->isEditableBy($user);
    }

    public function hasDependencies(): bool
    {
        return $this->users()->exists();
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $this->isEditableBy($user)
            && $user?->can('team_positions.delete')
            && ! $this->hasDependencies();
    }
}
