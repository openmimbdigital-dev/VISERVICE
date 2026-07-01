<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public function businessTypes(): BelongsToMany
    {
        return $this->belongsToMany(BusinessType::class, 'business_type_permission')
            ->withTimestamps();
    }
}
