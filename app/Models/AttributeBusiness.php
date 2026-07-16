<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeBusiness extends Model
{
    protected $table = 'attribute_business';

    protected $fillable = [
        'attribute_id',
        'business_id',
    ];

    protected function casts(): array
    {
        return [
            'attribute_id' => 'integer',
            'business_id'  => 'integer',
        ];
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
