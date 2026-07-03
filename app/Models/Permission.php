<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_permission')
            ->withTimestamps();
    }
}
