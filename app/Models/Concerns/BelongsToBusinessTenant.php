<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToBusinessTenant
{
    public function scopeForAuthUser(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();
        $table  = $query->getModel()->getTable();

        // El superAdmin no es un comodín sobre los datos de los comercios: ve los
        // de su propio negocio, igual que cualquiera. Para mirar los de otro entra
        // como un usuario suyo (ver App\Support\Impersonation), y así lo hace con
        // sus permisos y su alcance en vez de con reglas especiales.
        $business_ids = $user->businessIds();

        if ($business_ids === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn("{$table}.business_id", $business_ids);
    }

    public function isEditableBy(?User $user = null, string $edit_permission = ''): bool
    {
        $user ??= auth()->user();

        if ($edit_permission !== '' && ! $user?->can($edit_permission)) {
            return false;
        }

        if ($user?->hasRole('superAdmin')) {
            return true;
        }

        return $this->business_id !== null
            && $user->belongsToBusiness($this->business_id);
    }
}
