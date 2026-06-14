<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'phone_code',
        'currency',
        'currency_symbol',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'country_id');
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class, 'country_id');
    }

    public function business_addresses(): HasMany
    {
        return $this->hasMany(BusinessAddress::class, 'country_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'country_id');
    }
}
