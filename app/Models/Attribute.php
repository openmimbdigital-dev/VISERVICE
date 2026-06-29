<?php

namespace App\Models;

use App\Enums\AttributeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'business_id',
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

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
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

    public function scopeByBusiness($query, int $business_id)
    {
        return $query->where('business_id', $business_id);
    }

    public function isBeingUsed(): bool
    {
        return $this->attributeProductTypes()->exists();
    }
}
