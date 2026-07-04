<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BusinessType extends Model
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
        static::saving(function (BusinessType $business_type) {
            if ($business_type->isDirty('name') || blank($business_type->label)) {
                $business_type->label = static::normalizeLabel($business_type->name);
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
        return $this->hasMany(Business::class, 'business_type_id');
    }

    public function organization_types(): HasMany
    {
        return $this->hasMany(OrganizationType::class, 'business_type_id');
    }

    public function team_positions(): HasMany
    {
        return $this->hasMany(TeamPosition::class, 'business_type_id');
    }
}
