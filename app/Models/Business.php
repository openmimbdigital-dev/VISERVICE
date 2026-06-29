<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'email',
        'slug',
        'nit',
        'logo',
        'website',
        'facebook',
        'instagram',
        'twitter',
        'phone_number',
        'city_id',
        'country_id',
        'business_type_id',
        'business_id',
        'representative',
        'configurations',
        'configurations_value',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'representative' => 'array',
            'configurations' => 'array',
            'configurations_value' => 'array',
            'status' => 'boolean',
        ];
    }

    public function business_type(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class, 'business_type_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function parent_business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function child_businesses(): HasMany
    {
        return $this->hasMany(Business::class, 'business_id');
    }

    public function business_addresses(): HasMany
    {
        return $this->hasMany(BusinessAddress::class, 'business_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'business_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['trial', 'active'])
            ->latest();
    }

    public function latestSubscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function equipmentModels(): HasMany
    {
        return $this->hasMany(EquipmentModel::class);
    }

    public function equipmentTypes(): HasMany
    {
        return $this->hasMany(EquipmentType::class);
    }

    public function assignedEquipmentTypes(): BelongsToMany
    {
        return $this->belongsToMany(EquipmentType::class, 'equipment_type_business')
            ->withTimestamps();
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'attribute_business')
            ->withTimestamps();
    }
}
