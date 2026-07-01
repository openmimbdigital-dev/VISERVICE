<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuSection extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'icon_svg_path',
        'icon_color_class',
        'route_patterns',
        'behavior',
        'route_name',
        'role',
        'permission',
        'sort_order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'route_patterns' => 'array',
            'sort_order'     => 'integer',
            'active'         => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function activeItems(): HasMany
    {
        return $this->items()->where('active', true);
    }

    public function isActiveForRequest(): bool
    {
        $patterns = $this->route_patterns ?? [];

        return $patterns !== [] && request()->routeIs($patterns);
    }

    public function isVisibleTo(?User $user): bool
    {
        if (! $this->active || ! $user) {
            return false;
        }

        if ($this->role && ! $user->hasRole($this->role)) {
            return false;
        }

        if ($this->permission && ! $user->canViaBusinessType($this->permission)) {
            return false;
        }

        return true;
    }
}
