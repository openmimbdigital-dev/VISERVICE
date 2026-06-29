<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function attributeProductTypes(): MorphMany
    {
        return $this->morphMany(AttributeProductType::class, 'model');
    }
}
