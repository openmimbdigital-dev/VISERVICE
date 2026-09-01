<?php

namespace App\Models;

use App\Enums\BrandUsageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandUsage extends Model
{
    protected $table = 'brand_usage';

    protected $fillable = [
        'brand_id',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => BrandUsageType::class,
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}
