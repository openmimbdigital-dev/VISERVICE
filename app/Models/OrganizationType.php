<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OrganizationType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'label',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (OrganizationType $organization_type) {
            if ($organization_type->isDirty('name') || blank($organization_type->label)) {
                $organization_type->label = static::normalizeLabel($organization_type->name);
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

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class, 'organization_type_id');
    }

    public function business_types(): HasMany
    {
        return $this->hasMany(BusinessType::class, 'organization_type_id');
    }

    public function team_positions(): HasMany
    {
        return $this->hasMany(TeamPosition::class, 'organization_type_id');
    }
}
