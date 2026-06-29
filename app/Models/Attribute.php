<?php

namespace App\Models;

use App\Enums\AttributeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'required',
        'max_value',
        'min_value',
        'default',
        'nullable_creation',
        'general',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'type'              => AttributeType::class,
            'required'          => 'boolean',
            'default'           => 'boolean',
            'nullable_creation' => 'boolean',
            'general'           => 'boolean',
            'options'           => 'array',
            'max_value'         => 'integer',
            'min_value'         => 'integer',
        ];
    }

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'attribute_business')
            ->withTimestamps();
    }

    public function attributeBusinesses(): HasMany
    {
        return $this->hasMany(AttributeBusiness::class);
    }

    public function attributeProductTypes(): HasMany
    {
        return $this->hasMany(AttributeProductType::class);
    }

    public function scopeByType($query, AttributeType|string $type)
    {
        $value = $type instanceof AttributeType ? $type->value : $type;

        return $query->where('type', $value);
    }

    public function scopeForAuthUser($query)
    {
        $user = auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

        $business_id = $user?->business_id;

        if (! $business_id) {
            return $query->where('general', true);
        }

        return $query->where(function ($q) use ($business_id) {
            $q->where('general', true)
                ->orWhereHas('businesses', fn ($bq) => $bq->where('businesses.id', $business_id));
        });
    }

    public function isAccessibleBy(?\App\Models\User $user = null): bool
    {
        $user ??= auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return true;
        }

        if ($this->general) {
            return true;
        }

        return $this->businesses()
            ->where('businesses.id', $user?->business_id)
            ->exists();
    }

    public function isEditableBy(?\App\Models\User $user = null): bool
    {
        $user ??= auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return true;
        }

        if ($this->general) {
            return false;
        }

        return $this->businesses()
            ->where('businesses.id', $user?->business_id)
            ->exists();
    }

    public function isGeneralReadonly(?\App\Models\User $user = null): bool
    {
        $user ??= auth()->user();

        return $this->general && ! $user?->hasRole('superAdmin');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            AttributeType::TEXT     => 'Texto',
            AttributeType::NUMBER     => 'Número',
            AttributeType::TEXTAREA   => 'Área de texto',
            AttributeType::SELECT     => 'Lista desplegable',
            AttributeType::RADIO      => 'Botones de radio',
            AttributeType::CHECKBOX   => 'Casillas de verificación',
            AttributeType::COLOR      => 'Color',
        };
    }

    public function isBeingUsed(): bool
    {
        return $this->attributeProductTypes()->exists();
    }
}
