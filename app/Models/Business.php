<?php

namespace App\Models;

use App\Support\BusinessModuleAccess;
use App\Support\BusinessLogoStorage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
        'tagline',
        'tax_regime',
        'logo',
        'website',
        'facebook',
        'instagram',
        'twitter',
        'phone_number',
        'city_id',
        'country_id',
        'business_type_id',
        'organization_type_id',
        'business_category_id',
        'business_id',
        'representative',
        'configurations',
        'configurations_value',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'representative'       => 'array',
            'configurations'       => 'array',
            'configurations_value' => 'array',
            'status'               => 'boolean',
        ];
    }

    public function business_type(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class, 'business_type_id');
    }

    public function organization_type(): BelongsTo
    {
        return $this->belongsTo(OrganizationType::class, 'organization_type_id');
    }

    public function business_category(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_business')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function user_historicals(): HasMany
    {
        return $this->hasMany(UserHistorical::class)->orderByDesc('created_at');
    }

    public function equipment_historicals(): HasMany
    {
        return $this->hasMany(EquipmentHistorical::class)->orderByDesc('created_at');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payment_methods(): HasMany
    {
        return $this->hasMany(BusinessPaymentMethod::class);
    }

    public function bank_accounts(): HasMany
    {
        return $this->hasMany(BusinessBankAccount::class);
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

    public function team_positions(): HasMany
    {
        return $this->hasMany(TeamPosition::class);
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

    public function generalConfigs(): MorphMany
    {
        return $this->morphMany(GeneralConfig::class, 'configurable');
    }

    public function remissions(): HasMany
    {
        return $this->hasMany(Remission::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'attribute_business')
            ->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'business_role')->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'business_permission')->withTimestamps();
    }

    public function menuSections(): BelongsToMany
    {
        return $this->belongsToMany(MenuSection::class, 'business_menu_section')->withTimestamps();
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'business_menu_item')->withTimestamps();
    }

    public function moduleOwner(): Business
    {
        return BusinessModuleAccess::moduleOwnerBusiness($this);
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn () => BusinessLogoStorage::url($this->logo));
    }

    protected function logoInitials(): Attribute
    {
        return Attribute::get(fn () => strtoupper(mb_substr($this->name, 0, 2)));
    }

    public function scopeForAuthUser($query)
    {
        $user = auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

        $business_ids = $user->businessIds();

        if ($business_ids === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn($query->getModel()->getTable() . '.id', $business_ids);
    }
}
