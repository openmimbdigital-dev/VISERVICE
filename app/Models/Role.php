<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_role')
            ->withTimestamps();
    }
}
