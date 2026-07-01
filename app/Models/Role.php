<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function businessTypes(): BelongsToMany
    {
        return $this->belongsToMany(BusinessType::class, 'business_type_role')
            ->withTimestamps();
    }
}
