<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait InteractsWithSharedCatalog
{
    public function scopeVisibleToUser(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();
        $table = $query->getModel()->getTable();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

        $business_ids = $user->businessIds();

        return $query->where(function (Builder $q) use ($user, $table, $business_ids) {
            $q->where("{$table}.general", true);

            if ($business_ids !== []) {
                $q->orWhereIn("{$table}.business_id", $business_ids);
            }
        });
    }

    public function isEditableBy(?User $user = null): bool
    {
        $user ??= auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return true;
        }

        return ! $this->general
            && $this->business_id !== null
            && $user->belongsToBusiness($this->business_id);
    }

    public function isGeneralReadonly(?User $user = null): bool
    {
        return $this->general && ! $this->isEditableBy($user);
    }
}
