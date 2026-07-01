<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_section_id',
        'name',
        'route_name',
        'active_route_pattern',
        'icon_svg_path',
        'icon_color_class',
        'permission',
        'role',
        'badge_key',
        'sort_order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'menu_section_id' => 'integer',
            'sort_order'      => 'integer',
            'active'          => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(MenuSection::class, 'menu_section_id');
    }

    public function activePattern(): string
    {
        return $this->active_route_pattern ?? $this->route_name;
    }

    public function isActiveForRequest(): bool
    {
        return request()->routeIs($this->activePattern());
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
