<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BusinessCategory extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'label',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (BusinessCategory $category) {
            if ($category->isDirty('name') || blank($category->label)) {
                $category->label = static::normalizeLabel($category->name);
            }
        });
    }

    public static function normalizeLabel(string $name): string
    {
        $ascii = Str::ascii($name);
        $label = strtolower($ascii);
        $label = preg_replace('/[^a-z0-9\s_]/', '', $label) ?? '';
        $label = preg_replace('/\s+/', '_', trim($label)) ?? '';
        $label = preg_replace('/_+/', '_', $label) ?? '';

        return trim($label, '_');
    }
}
