<?php

namespace App\Models;

use App\Support\BusinessModuleAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_menu_item')->withTimestamps();
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

        if ($this->permission) {
            $permissions = preg_split('/\s*\|\s*/', $this->permission) ?: [];
            $allowed = collect($permissions)->contains(
                fn (string $permission) => $permission !== '' && $user->can($permission)
            );

            if (! $allowed) {
                return false;
            }
        }

        if (! BusinessModuleAccess::menuItemEnabledForUser($user, $this)) {
            return false;
        }

        return true;
    }
}
