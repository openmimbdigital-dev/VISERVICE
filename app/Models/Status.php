<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $fillable = [
        'name',
        'label',
        'active',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'type'   => 'array',
        ];
    }

    public function scopeForModule(Builder $query, string $module): Builder
    {
        return $query
            ->where('active', true)
            ->whereJsonContains('type', $module);
    }

    /** @return array<string, string> */
    public static function optionsForModule(string $module): array
    {
        return static::query()
            ->forModule($module)
            ->orderBy('id')
            ->pluck('label', 'name')
            ->all();
    }
}
