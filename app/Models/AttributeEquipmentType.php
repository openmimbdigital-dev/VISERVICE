<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeEquipmentType extends Model
{
    use SoftDeletes;

    protected $table = 'attribute_equipment_types';

    protected $fillable = [
        'business_id',
        'model_id',
        'model_type',
        'attribute_id',
        'general'
    ];

    protected function casts(): array
    {
        return [
            'business_id'  => 'integer',
            'model_id'     => 'integer',
            'attribute_id' => 'integer',
            'general'      => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function scopeByBusiness($query, int $business_id)
    {
        return $query->where('business_id', $business_id);
    }

    public function scopeByEquipmentType($query, int $equipment_type_id)
    {
        return $query->where('model_type', EquipmentType::class)
            ->where('model_id', $equipment_type_id);
    }

    /** @deprecated Usar scopeByEquipmentType */
    public function scopeByProductType($query, int $product_type_id)
    {
        return $this->scopeByEquipmentType($query, $product_type_id);
    }

    public function scopeByAttribute($query, int $attribute_id)
    {
        return $query->where('attribute_id', $attribute_id);
    }
}
